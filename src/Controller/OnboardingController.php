<?php

namespace App\Controller;

use App\Entity\OnboardingSession;
use App\Repository\OnboardingSessionRepository;
use App\Service\AiChatServiceInterface;
use App\Service\DocumentStorage;
use App\Service\OnboardingService;
use App\Service\OnboardingDocumentAnalyzer;
use App\Entity\Document;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[Route('/onboarding')]
class OnboardingController extends AbstractController
{
    public function __construct(
        private readonly OnboardingSessionRepository $sessionRepository,
        private readonly OnboardingService $onboardingService,
        private readonly AiChatServiceInterface $aiChat,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('', name: 'app_onboarding_index', methods: ['GET'])]
    public function index(): Response
    {
        $user = $this->getUser();
        $sessions = $user !== null
            ? $this->sessionRepository->findBy(['user' => $user], ['createdAt' => 'DESC'])
            : [];

        return $this->render('onboarding/index.html.twig', [
            'sessions' => $sessions,
            'page_title' => 'Mes Onboardings',
        ]);
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/new', name: 'app_onboarding_new', methods: ['GET', 'POST'])]
    public function new(): Response
    {
        $user = $this->getUser();

        if ($inProgressSession = $this->sessionRepository->findInProgressByUser($user)) {
            return $this->redirectToRoute('app_onboarding_chat', ['id' => $inProgressSession->getId()]);
        }

        $session = new OnboardingSession($user);
        $this->entityManager->persist($session);
        $this->entityManager->flush();

        return $this->redirectToRoute('app_onboarding_chat', ['id' => $session->getId()]);
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/{id}/chat', name: 'app_onboarding_chat', methods: ['GET'])]
    public function chat(OnboardingSession $session): Response
    {
        $this->denyAccessUnlessGranted('view', $session);

        $session->setCompleteness($this->onboardingService->calculateCompleteness($session));

        return $this->render('onboarding/chat.html.twig', [
            'session' => $session,
            'displayMessages' => $this->buildDisplayMessages($session),
            'phaseSequence' => $this->onboardingService->getPhaseSequence(),
            'completeness' => $session->getCompleteness(),
            'missingFields' => $this->onboardingService->getMissingFieldLabels($session),
            'entitySummaries' => $this->onboardingService->buildEntitySummaries($session),
            'page_title' => 'Onboarding Client',
        ]);
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/{id}/message', name: 'app_onboarding_message', methods: ['POST'])]
    public function sendMessage(OnboardingSession $session, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('view', $session);

        $message = $this->extractMessageFromRequest($request);
        if ($message === null) {
            return new JsonResponse(['error' => 'Message empty'], Response::HTTP_BAD_REQUEST);
        }

        $session->addMessage('user', $message);
        $this->entityManager->persist($session);
        $this->entityManager->flush();

        try {
            $payload = $this->aiChat->chat(
                $this->normalizeMessages($session),
                $this->aiChat->buildSystemPrompt($session, $session->getPhase())
            );

            $session->addMessage('assistant', (string) ($payload['message'] ?? ''));
            $state = $this->onboardingService->processLlmResponse($session, $payload);

            return new JsonResponse([
                'message' => $payload['message'] ?? '',
                'phase' => $state['phase'],
                'completeness' => $state['completeness'],
                'missingFields' => $state['missingFields'],
                'extractedData' => $state['extractedData'],
                'contactSummary' => $state['contactSummary'],
                'accountSummary' => $state['accountSummary'],
            ]);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'error' => 'AI service error: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/{id}/message/stream', name: 'app_onboarding_message_stream', methods: ['POST'])]
    public function streamMessage(OnboardingSession $session, Request $request): StreamedResponse
    {
        $this->denyAccessUnlessGranted('view', $session);

        $message = $this->extractMessageFromRequest($request);
        if ($message === null) {
            return $this->createSseErrorResponse('Message empty', Response::HTTP_BAD_REQUEST);
        }

        $session->addMessage('user', $message);
        $this->entityManager->persist($session);
        $this->entityManager->flush();

        $messages = $this->normalizeMessages($session);
        $systemPrompt = $this->aiChat->buildSystemPrompt($session, $session->getPhase());

        $response = new StreamedResponse(function () use ($session, $messages, $systemPrompt): void {
            try {
                foreach ($this->aiChat->streamChat($messages, $systemPrompt) as $event) {
                    if (!is_array($event) || !isset($event['event'])) {
                        continue;
                    }

                    if ($event['event'] === 'token') {
                        $this->sendSseEvent('typing', ['status' => 'typing']);
                        continue;
                    }

                    if ($event['event'] === 'final') {
                        $payload = is_array($event['data'] ?? null) ? $event['data'] : [];
                        $assistantMessage = (string) ($payload['message'] ?? '');
                        $session->addMessage('assistant', $assistantMessage);
                        $state = $this->onboardingService->processLlmResponse($session, $payload);

                        $this->sendSseEvent('final', [
                            'message' => $assistantMessage,
                            'phase' => $state['phase'],
                            'completeness' => $state['completeness'],
                            'missingFields' => $state['missingFields'],
                            'extractedData' => $state['extractedData'],
                            'contactSummary' => $state['contactSummary'],
                            'accountSummary' => $state['accountSummary'],
                        ]);

                        return;
                    }

                    if ($event['event'] === 'error') {
                        throw new \RuntimeException((string) ($event['data'] ?? 'Unknown streaming error'));
                    }
                }

            } catch (\Throwable $e) {
                $this->sendSseEvent('error', ['message' => $e->getMessage()]);
            }
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/{id}/document', name: 'app_onboarding_document_upload', methods: ['POST'])]
    public function uploadDocument(
        OnboardingSession $session,
        Request $request,
        DocumentStorage $storage,
        OnboardingDocumentAnalyzer $documentAnalyzer,
    ): JsonResponse {
        $this->denyAccessUnlessGranted('view', $session);

        $uploadedFile = $request->files->get('document');
        if (!$uploadedFile instanceof UploadedFile) {
            return new JsonResponse(['error' => 'Aucun document transmis.'], Response::HTTP_BAD_REQUEST);
        }

        $allowedMimeTypes = [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'text/plain',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];

        if (!in_array((string) $uploadedFile->getClientMimeType(), $allowedMimeTypes, true)) {
            return new JsonResponse([
                'error' => 'Formats autorisés dans le chat: PDF, JPG, PNG, TXT, DOCX.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $document = new Document();
        $document->setName(pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME) ?: $uploadedFile->getClientOriginalName());

        $stored = $storage->store($uploadedFile);
        $document
            ->setPath($stored['path'])
            ->setMimeType($stored['mime_type'])
            ->setSize($stored['size']);

        if ($session->getAccount() !== null) {
            $document->addAccount($session->getAccount());
        }

        if ($session->getContact() !== null) {
            $document->addContact($session->getContact());
        }

        $this->entityManager->persist($document);
        $session->addMessage('user', sprintf('[Document envoyé: %s]', $uploadedFile->getClientOriginalName()));
        $this->entityManager->persist($session);
        $this->entityManager->flush();

        $payload = $documentAnalyzer->analyzeDocument($session, $document);
        $assistantMessage = (string) ($payload['message'] ?? 'Document reçu.');
        $session->addMessage('assistant', $assistantMessage);

        $state = $this->onboardingService->processLlmResponse($session, $payload);

        if ($session->getAccount() !== null) {
            $document->addAccount($session->getAccount());
        }

        if ($session->getContact() !== null) {
            $document->addContact($session->getContact());
        }

        $this->entityManager->persist($document);
        $this->entityManager->flush();

        return new JsonResponse([
            'message' => $assistantMessage,
            'phase' => $state['phase'],
            'completeness' => $state['completeness'],
            'missingFields' => $state['missingFields'],
            'extractedData' => $state['extractedData'],
            'contactSummary' => $state['contactSummary'],
            'accountSummary' => $state['accountSummary'],
            'documentName' => $document->getName() ?: $uploadedFile->getClientOriginalName(),
        ]);
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/{id}/review', name: 'app_onboarding_review', methods: ['GET'])]
    public function review(OnboardingSession $session): Response
    {
        $this->denyAccessUnlessGranted('view', $session);

        return $this->render('onboarding/review.html.twig', [
            'session' => $session,
            'displayMessages' => $this->buildDisplayMessages($session),
            'contact' => $session->getContact(),
            'account' => $session->getAccount(),
            'page_title' => 'Valider mon profil',
        ]);
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/{id}/save', name: 'app_onboarding_save', methods: ['POST'])]
    public function save(OnboardingSession $session): JsonResponse
    {
        $this->denyAccessUnlessGranted('view', $session);

        try {
            $this->onboardingService->saveSessionData($session);

            return new JsonResponse([
                'success' => true,
                'message' => 'Données sauvegardées avec succès',
                'extractedData' => $session->getExtractedData(),
            ]);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Erreur lors de la sauvegarde: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/{id}/complete', name: 'app_onboarding_complete', methods: ['POST'])]
    public function complete(OnboardingSession $session): Response
    {
        $this->denyAccessUnlessGranted('view', $session);

        $this->onboardingService->completeSession($session);
        $this->addFlash('success', 'Profil client créé avec succès!');

        if ($account = $session->getAccount()) {
            return $this->redirectToRoute('app_account_show', ['id' => $account->getId()]);
        }

        return $this->redirectToRoute('app_onboarding_index');
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/{id}', name: 'app_onboarding_show', methods: ['GET'])]
    public function show(OnboardingSession $session): Response
    {
        $this->denyAccessUnlessGranted('view', $session);

        return $this->render('onboarding/show.html.twig', [
            'session' => $session,
            'displayMessages' => $this->buildDisplayMessages($session),
            'page_title' => 'Onboarding: '.($session->getContact()?->getFirstname() ?? 'Nouveau'),
        ]);
    }

    /**
     * @return list<array{role: string, content: string}>
     */
    private function normalizeMessages(OnboardingSession $session): array
    {
        return array_map(
            static fn(array $message): array => [
                'role' => (string) ($message['role'] ?? 'user'),
                'content' => (string) ($message['content'] ?? ''),
            ],
            $session->getMessages()
        );
    }

    /**
     * @return list<array{role: string, content: string, timestamp: string}>
     */
    private function buildDisplayMessages(OnboardingSession $session): array
    {
        return array_map(function (array $message): array {
            $role = (string) ($message['role'] ?? 'user');
            $content = (string) ($message['content'] ?? '');

            return [
                'role' => $role,
                'content' => $this->normalizeDisplayContent($role, $content),
                'timestamp' => (string) ($message['timestamp'] ?? ''),
            ];
        }, $session->getMessages());
    }

    private function normalizeDisplayContent(string $role, string $content): string
    {
        $trimmed = trim($content);
        if ($role !== 'assistant' || $trimmed === '' || $trimmed[0] !== '{') {
            return $content;
        }

        try {
            $decoded = json_decode($trimmed, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            return $content;
        }

        if (!is_array($decoded)) {
            return $content;
        }

        $message = $decoded['message'] ?? null;

        return is_string($message) && trim($message) !== '' ? $message : $content;
    }

    private function extractMessageFromRequest(Request $request): ?string
    {
        $data = json_decode($request->getContent(), true);
        $message = trim((string) ($data['message'] ?? ''));

        return $message !== '' ? $message : null;
    }

    private function sendSseEvent(string $event, array $payload): void
    {
        echo 'event: '.$event."\n";
        echo 'data: '.json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n\n";

        if (function_exists('ob_flush')) {
            @ob_flush();
        }
        flush();
    }

    private function createSseErrorResponse(string $message, int $status): StreamedResponse
    {
        $response = new StreamedResponse(function () use ($message): void {
            $this->sendSseEvent('error', ['message' => $message]);
        }, $status);

        $response->headers->set('Content-Type', 'text/event-stream');

        return $response;
    }
}
