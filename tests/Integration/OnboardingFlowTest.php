<?php

namespace App\Tests\Integration;

use App\Entity\Document;
use App\Entity\OnboardingSession;
use App\Entity\User;
use App\Service\OnboardingService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class OnboardingFlowTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private OnboardingService $onboardingService;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->onboardingService = static::getContainer()->get(OnboardingService::class);

        $this->resetDatabase();
    }

    public function testClientCanStartResumeAndDocumentAnOnboardingSession(): void
    {
        $clientUser = $this->createUser('client-onboarding', ['ROLE_CLIENT'], 'client.onboarding@example.test');
        $this->client->loginUser($clientUser);

        $this->client->request('GET', '/onboarding/new');
        self::assertResponseRedirects();

        $location = (string) $this->client->getResponse()->headers->get('Location');
        self::assertMatchesRegularExpression('#/onboarding/(\d+)/chat$#', $location);
        preg_match('#/onboarding/(\d+)/chat$#', $location, $matches);
        $sessionId = (int) ($matches[1] ?? 0);
        self::assertGreaterThan(0, $sessionId);

        $this->client->followRedirect();
        self::assertResponseIsSuccessful();
        $this->assertResponseContains('Chat Planilife');

        $this->client->request('GET', '/onboarding/new');
        self::assertResponseRedirects(sprintf('/onboarding/%d/chat', $sessionId));

        $this->client->request(
            'POST',
            sprintf('/onboarding/%d/message', $sessionId),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['message' => 'Je m appelle Lea et je veux avancer dossier par dossier.'], JSON_THROW_ON_ERROR)
        );

        self::assertResponseIsSuccessful();
        self::assertStringStartsWith('application/json', (string) $this->client->getResponse()->headers->get('content-type'));
        $payload = json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('discovery', $payload['phase']);
        self::assertSame('Je m appelle Lea et je veux avancer dossier par dossier.', $payload['extractedData']['client']['reponse_libre']);

        $this->client->loginUser($clientUser);

        $documentPath = tempnam(sys_get_temp_dir(), 'onboarding-doc-');
        self::assertIsString($documentPath);
        file_put_contents($documentPath, "Releve client\nBanque Exemple\nSolde 1250 EUR");

        $this->client->request(
            'POST',
            sprintf('/onboarding/%d/document', $sessionId),
            [],
            [
                'document' => new UploadedFile(
                    $documentPath,
                    'justificatif.txt',
                    'text/plain',
                    null,
                    true
                ),
            ]
        );

        self::assertResponseIsSuccessful();
        $documentPayload = json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('justificatif', $documentPayload['documentName']);
        self::assertStringContainsString('document', strtolower($documentPayload['message']));

        $this->client->loginUser($clientUser);
        $this->client->request('GET', sprintf('/onboarding/%d/chat', $sessionId));
        self::assertResponseIsSuccessful();
        $this->assertResponseContains('Je m appelle Lea et je veux avancer dossier par dossier.');
        $this->assertResponseContains('[Document envoyé: justificatif.txt]');

        $this->entityManager->clear();
        /** @var OnboardingSession $session */
        $session = $this->entityManager->getRepository(OnboardingSession::class)->find($sessionId);

        self::assertSame(OnboardingSession::STATUS_IN_PROGRESS, $session->getStatus());
        self::assertCount(4, $session->getMessages());
        self::assertSame(1, $this->entityManager->getRepository(Document::class)->count([]));

        @unlink($documentPath);
    }

    public function testAdvisorCanReviewAndConvertAClientOnboardingIntoCrmData(): void
    {
        $clientUser = $this->createUser('client-review', ['ROLE_CLIENT'], 'client.review@example.test');
        $advisor = $this->createUser('advisor-review', ['ROLE_USER'], 'advisor.review@example.test');

        $session = (new OnboardingSession($clientUser))
            ->setExtractedData([
                'client' => [
                    'prenom' => 'Lea',
                    'nom' => 'Durand',
                    'email' => 'lea.durand@example.test',
                    'phone' => '0102030405',
                    'profession' => 'Architecte',
                    'statut' => 'marie',
                    'adresse' => 'Rue Haute 10',
                    'ville' => 'Bruxelles',
                    'code_postal' => '1000',
                    'pays' => 'BE',
                ],
                'account' => [
                    'name' => 'Dossier Lea Durand',
                    'type' => 'Core',
                    'company_statut' => 'Personne physique',
                ],
            ]);
        $session->addMessage('user', 'Voici les informations de base de Lea Durand.');

        $this->entityManager->persist($session);
        $this->entityManager->flush();
        $this->onboardingService->saveSessionData($session);

        $this->client->loginUser($advisor);

        $this->client->request('GET', '/onboarding');
        self::assertResponseIsSuccessful();
        $this->assertResponseContains('Dossiers a suivre');
        $this->assertResponseContains('client.review@example.test');

        $this->client->request('GET', sprintf('/onboarding/%d/review', $session->getId()));
        self::assertResponseIsSuccessful();
        $this->assertResponseContains('Lea');
        $this->assertResponseContains('Dossier Lea Durand');
        $this->assertResponseContains('Finaliser et créer le profil');

        $this->client->submitForm('Finaliser et créer le profil');
        self::assertResponseRedirects();
        $this->client->followRedirect();
        self::assertResponseIsSuccessful();
        $this->assertResponseContains('Dossier Lea Durand');

        $this->entityManager->clear();
        /** @var OnboardingSession $completedSession */
        $completedSession = $this->entityManager->getRepository(OnboardingSession::class)->find($session->getId());

        self::assertSame(OnboardingSession::STATUS_COMPLETED, $completedSession->getStatus());
        self::assertNotNull($completedSession->getContact());
        self::assertNotNull($completedSession->getAccount());
        self::assertSame('Lea', $completedSession->getContact()->getFirstname());
        self::assertSame('Dossier Lea Durand', $completedSession->getAccount()->getName());
        self::assertCount(1, $completedSession->getAccount()->getContacts());
    }

    private function createUser(string $username, array $roles, string $email): User
    {
        $user = (new User())
            ->setUsername($username)
            ->setEmail($email)
            ->setRoles($roles)
            ->setPassword('not-used-in-webtest');

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    private function resetDatabase(): void
    {
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($this->entityManager);

        if ($metadata === []) {
            return;
        }

        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    private function assertResponseContains(string $text): void
    {
        self::assertStringContainsString($text, (string) $this->client->getResponse()->getContent());
    }
}
