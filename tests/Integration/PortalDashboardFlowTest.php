<?php

namespace App\Tests\Integration;

use App\Entity\Contact;
use App\Entity\FieldEdit;
use App\Entity\OnboardingSession;
use App\Entity\User;
use App\Repository\FieldEditRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PortalDashboardFlowTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->resetDatabase();
    }

    public function testClientDashboardShowsSixTabsAndCompletionScore(): void
    {
        $user = $this->createClientUser('dashboard-client', 'dashboard@example.test');
        $this->createSession($user, [
            'client' => [
                'prenom' => 'Nina',
                'nom' => 'Vermeulen',
                'age' => 42,
                'statut' => 'mariee',
                'pro' => 'architecte',
            ],
            'projets' => [
                'vision' => 'Financer les etudes et preparer une transition professionnelle.',
                'retraite_age' => 62,
                'objectifs' => ['Etudes enfants', 'Residence secondaire'],
            ],
            'risque' => [
                'profil' => 'equilibre',
                'transmission' => 'Anticiper la donation aux enfants.',
            ],
            'etapes' => [
                'etape_cle' => 'Changer de statut professionnel',
                'etapes' => ['2027 - transition professionnelle'],
            ],
            'patrimoine' => [
                'immo' => [['type' => 'maison', 'valeur' => 420000]],
                'tresorerie' => 35000,
                'financier' => [['type' => 'assurance vie', 'montant' => 80000]],
            ],
        ]);

        $this->client->loginUser($user);
        $this->client->request('GET', '/portal');

        self::assertResponseIsSuccessful();
        $this->assertResponseContains('Dashboard Planilife');
        $this->assertResponseContains('Score de completude');
        $this->assertResponseContains('Rapport utile possible');
        $this->assertResponseContains('Profil de vie');
        $this->assertResponseContains('Projets');
        $this->assertResponseContains('Risque et valeurs');
        $this->assertResponseContains('Timing');
        $this->assertResponseContains('Patrimoine');
        $this->assertResponseContains('Revenus et flux');
    }

    public function testClientCanUpdateDashboardFieldWithProvenance(): void
    {
        $user = $this->createClientUser('inline-client', 'inline@example.test');
        $session = $this->createSession($user, [
            'projets' => [
                'vision' => 'Vision initiale',
            ],
        ]);

        $this->client->loginUser($user);
        $crawler = $this->client->request('GET', '/portal');
        self::assertResponseIsSuccessful();
        $token = $crawler->filter('form[action="/portal/dashboard/champ"] input[name="_token"]')->first()->attr('value');

        $this->client->request('POST', '/portal/dashboard/champ', [
            '_token' => $token,
            'field_path' => 'projets.vision',
            'value' => 'Nouvelle vision consolidee',
        ]);

        self::assertResponseRedirects('/portal#projets');

        $this->entityManager->clear();
        /** @var OnboardingSession $storedSession */
        $storedSession = $this->entityManager->getRepository(OnboardingSession::class)->find($session->getId());
        self::assertSame('Nouvelle vision consolidee', $storedSession->getExtractedData()['projets']['vision']);

        /** @var FieldEditRepository $fieldEditRepository */
        $fieldEditRepository = $this->entityManager->getRepository(FieldEdit::class);
        $edits = $fieldEditRepository->findBySessionAndFieldPath($storedSession, 'projets.vision');
        self::assertCount(1, $edits);
        self::assertSame('Vision initiale', $edits[0]->getOldValue());
        self::assertSame(FieldEdit::SOURCE_UPDATED, $edits[0]->getSource());
        self::assertSame(FieldEdit::ROLE_CLIENT, $edits[0]->getRole());
    }

    public function testClientCanAddTimelineEventAndKeepRequiredStepsInSync(): void
    {
        $user = $this->createClientUser('timeline-client', 'timeline@example.test');
        $session = $this->createSession($user, [
            'etapes' => [
                'etape_cle' => 'Premier rendez-vous patrimonial',
                'etapes' => ['Premier rendez-vous patrimonial'],
            ],
        ]);

        $this->client->loginUser($user);
        $crawler = $this->client->request('GET', '/portal');
        self::assertResponseIsSuccessful();
        $token = $crawler->filter('form[action="/portal/dashboard/timeline"] input[name="_token"]')->first()->attr('value');

        $this->client->request('POST', '/portal/dashboard/timeline', [
            '_token' => $token,
            'timeline_action' => 'add',
            'title' => 'Vente appartement locatif',
            'category' => 'patrimoine',
            'horizon' => '2028',
            'date' => '2028-06-01',
            'notes' => 'A arbitrer selon fiscalite.',
        ]);

        self::assertResponseRedirects('/portal#timing');

        $this->entityManager->clear();
        /** @var OnboardingSession $storedSession */
        $storedSession = $this->entityManager->getRepository(OnboardingSession::class)->find($session->getId());
        $data = $storedSession->getExtractedData();

        self::assertSame('Vente appartement locatif', $data['etapes']['timeline'][1]['title']);
        self::assertContains('Vente appartement locatif - 2028', $data['etapes']['etapes']);
        $eventId = $data['etapes']['timeline'][1]['id'];

        $crawler = $this->client->request('GET', '/portal');
        self::assertResponseIsSuccessful();
        $token = $crawler->filter('form[action="/portal/dashboard/timeline"] input[name="_token"]')->first()->attr('value');

        $this->client->request('POST', '/portal/dashboard/timeline', [
            '_token' => $token,
            'timeline_action' => 'update',
            'event_id' => $eventId,
            'title' => 'Vente appartement locatif confirmee',
            'category' => 'patrimoine',
            'horizon' => '2029',
            'date' => '2029-03-15',
            'notes' => 'Decision confirmee avec le conseiller.',
        ]);

        self::assertResponseRedirects('/portal#timing');

        $this->entityManager->clear();
        /** @var OnboardingSession $updatedSession */
        $updatedSession = $this->entityManager->getRepository(OnboardingSession::class)->find($session->getId());
        $updatedData = $updatedSession->getExtractedData();
        $updatedEvent = $this->findTimelineEvent($updatedData['etapes']['timeline'], $eventId);

        self::assertSame('Vente appartement locatif confirmee', $updatedEvent['title']);
        self::assertContains('Vente appartement locatif confirmee - 2029', $updatedData['etapes']['etapes']);

        /** @var FieldEditRepository $fieldEditRepository */
        $fieldEditRepository = $this->entityManager->getRepository(FieldEdit::class);
        self::assertCount(2, $fieldEditRepository->findBySessionAndFieldPath($updatedSession, 'etapes.timeline'));
        self::assertCount(2, $fieldEditRepository->findBySessionAndFieldPath($updatedSession, 'etapes.etapes'));
    }

    private function createClientUser(string $username, string $email): User
    {
        $contact = (new Contact())
            ->setFirstname($username)
            ->setLastname('Client')
            ->setEmail($email);

        $user = (new User())
            ->setUsername($username)
            ->setEmail($email)
            ->setRoles(['ROLE_CLIENT'])
            ->setPassword('not-used-in-webtest')
            ->setContact($contact);
        $user->markEmailVerified();
        $user->acceptConsent('planilife-beta-2026-05');

        $this->entityManager->persist($contact);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    private function createSession(User $user, array $data): OnboardingSession
    {
        $session = new OnboardingSession($user);
        $session->setContact($user->getContact());
        $session->setExtractedData($data);

        $this->entityManager->persist($session);
        $this->entityManager->flush();

        return $session;
    }

    private function findTimelineEvent(array $events, string $eventId): array
    {
        foreach ($events as $event) {
            if (($event['id'] ?? null) === $eventId) {
                return $event;
            }
        }

        self::fail(sprintf('Timeline event "%s" was not found.', $eventId));

        return [];
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
