<?php

namespace App\Tests\Integration;

use App\Entity\OnboardingSession;
use App\Entity\User;
use App\Service\ChatGptService;
use App\Service\OnboardingService;
use App\Service\OnboardingServiceRequiredFields;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PlanilifeChatTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private OnboardingService $onboarding;
    private OnboardingServiceRequiredFields $requiredFields;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->onboarding = self::getContainer()->get(OnboardingService::class);
        $this->requiredFields = self::getContainer()->get(OnboardingServiceRequiredFields::class);
    }

    public function testPhaseConstantsAreAccessible(): void
    {
        self::assertSame('discovery', OnboardingSession::PHASE_DISCOVERY);
        self::assertSame('qualification', OnboardingSession::PHASE_QUALIFICATION);
        self::assertSame('risk_analysis', OnboardingSession::PHASE_RISK_ANALYSIS);
        self::assertSame('etapes', OnboardingSession::PHASE_ETAPES);
        self::assertSame('patrimoine', OnboardingSession::PHASE_PATRIMOINE);
    }

    public function testOnboardingSessionCanHavePhaseProperty(): void
    {
        $session = new OnboardingSession($this->makeUser());

        self::assertSame(OnboardingSession::PHASE_DISCOVERY, $session->getPhase());

        $session->setPhase(OnboardingSession::PHASE_QUALIFICATION);

        self::assertSame(OnboardingSession::PHASE_QUALIFICATION, $session->getPhase());
    }

    public function testOnboardingServiceCalculatesCompletenessCorrectly(): void
    {
        $session = new OnboardingSession($this->makeUser());
        $session->setPhase(OnboardingSession::PHASE_DISCOVERY);

        $completeness = $this->onboarding->calculateCompleteness($session);

        self::assertSame(0.0, $completeness);

        $session->setExtractedData([
            'client' => [
                'prenom' => 'Jean',
                'age' => 45,
                'statut' => 'marié',
                'pro' => 'Ingénieur',
            ],
        ]);

        self::assertSame(100.0, $this->onboarding->calculateCompleteness($session));
    }

    public function testChatGptServiceHasStreamingCapability(): void
    {
        $client = new MockHttpClient([
            new MockResponse("data: {\"choices\":[{\"delta\":{\"content\":\"{\\\"message\\\":\\\"Bonjour\\\"\"}}]}\n\n".
                "data: {\"choices\":[{\"delta\":{\"content\":\",\\\"extractedFields\\\":{},\\\"phaseComplete\\\":false,\\\"nextPhase\\\":null}\"}}]}\n\n".
                "data: [DONE]\n\n"),
        ]);

        $service = $this->makeChatGptService($client);
        $session = new OnboardingSession($this->makeUser());
        $events = iterator_to_array($service->streamChat([], $service->buildSystemPrompt($session, OnboardingSession::PHASE_DISCOVERY)));

        self::assertNotEmpty($events);
        self::assertSame('final', $events[array_key_last($events)]['event']);
    }

    public function testSystemPromptIsConstructedWithPhaseInstructions(): void
    {
        $service = $this->makeChatGptService(new MockHttpClient([]));
        $session = new OnboardingSession($this->makeUser());
        $session->setPhase(OnboardingSession::PHASE_QUALIFICATION);

        $systemPrompt = $service->buildSystemPrompt($session, OnboardingSession::PHASE_QUALIFICATION);

        self::assertStringContainsString('PLANILIFE', $systemPrompt);
        self::assertStringContainsString('qualification', strtolower($systemPrompt));
    }

    public function testJsonResponseParsingExtractsValidStructure(): void
    {
        $service = $this->makeChatGptService(new MockHttpClient([]));
        $method = new \ReflectionMethod(ChatGptService::class, 'parseStructuredResponse');

        $parsed = $method->invoke($service, <<<'JSON'
{
  "message": "Bonjour, comment puis-je vous aider?",
  "extractedFields": {
    "client.prenom": "Jean",
    "client.age": 45
  },
  "phaseComplete": false,
  "nextPhase": null
}
JSON);

        self::assertIsArray($parsed);
        self::assertSame('Jean', $parsed['extractedFields']['client.prenom']);
        self::assertFalse($parsed['phaseComplete']);
    }

    public function testPhaseAdvancementWorks(): void
    {
        $session = new OnboardingSession($this->makeUser());

        $nextPhase = $this->onboarding->advancePhase($session);

        self::assertSame(OnboardingSession::PHASE_QUALIFICATION, $nextPhase);
        self::assertSame(OnboardingSession::PHASE_QUALIFICATION, $session->getPhase());
    }

    public function testGetMissingFieldsDetectsIncompletePhase(): void
    {
        $session = new OnboardingSession($this->makeUser());
        $session->setPhase(OnboardingSession::PHASE_DISCOVERY);
        $session->setExtractedData([]);

        $missing = $this->onboarding->getMissingFields($session);

        self::assertIsArray($missing);
        self::assertContains('client.prenom', $missing);
    }

    public function testFieldLabelsAreReadable(): void
    {
        $labels = $this->requiredFields->toDisplayLabels([
            'patrimoine.immo',
            'patrimoine.tresorerie',
            'patrimoine.financier',
        ]);

        self::assertSame([
            'Biens immobiliers',
            'Trésorerie disponible',
            'Épargne et placements',
        ], $labels);
    }

    private function makeUser(): User
    {
        $user = new User();
        $user->setUsername('test-user');
        $user->setEmail('test@example.com');
        $user->setPassword('hashed');

        return $user;
    }

    private function makeChatGptService(HttpClientInterface $httpClient): ChatGptService
    {
        return new ChatGptService(
            $httpClient,
            $this->requiredFields,
            'sk-test-placeholder',
            0.7,
            1000,
            30,
        );
    }

    public static function tearDownAfterClass(): void
    {
        self::ensureKernelShutdown();
    }
}
