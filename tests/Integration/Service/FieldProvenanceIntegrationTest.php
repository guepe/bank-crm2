<?php

namespace App\Tests\Integration\Service;

use App\Entity\FieldEdit;
use App\Entity\OnboardingSession;
use App\Entity\User;
use App\Repository\FieldEditRepository;
use App\Service\OnboardingService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Integration test for field provenance tracking (US030).
 * Tests the complete flow: OnboardingSession → FieldProvenanceService → DB.
 */
class FieldProvenanceIntegrationTest extends KernelTestCase
{
    private FieldEditRepository $repository;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->repository = self::getContainer()->get(FieldEditRepository::class);
        $this->resetDatabase();
    }

    public function testTrackingFieldUpdatesInOnboardingSession(): void
    {
        $provenanceService = self::getContainer()->get('App\Service\FieldProvenanceService');

        // Create test user
        $user = new User();
        $user->setUsername('test-provenance');
        $user->setEmail('test-provenance@example.com');
        $user->setPassword(password_hash('test', PASSWORD_BCRYPT));
        $user->setRoles(['ROLE_USER']);
        $this->entityManager->persist($user);

        // Create test session
        $session = new OnboardingSession($user);
        $session->setStatus(OnboardingSession::STATUS_IN_PROGRESS);
        $session->setPhase(OnboardingSession::PHASE_DISCOVERY);
        $this->entityManager->persist($session);
        $this->entityManager->flush();

        // Track a field update
        $provenanceService->trackFieldUpdate(
            $session,
            'client.prenom',
            'Jean',
            FieldEdit::SOURCE_DECLARED,
            $user
        );
        $this->entityManager->flush();

        // Verify FieldEdit was persisted
        $edits = $this->repository->findBySessionAndFieldPath($session, 'client.prenom');
        $this->assertCount(1, $edits);
        $this->assertEquals('Jean', $edits[0]->getNewValue());
        $this->assertEquals(FieldEdit::SOURCE_DECLARED, $edits[0]->getSource());
        $this->assertEquals($user, $edits[0]->getAuthor());

        // Track a second update (simulating correction)
        $provenanceService->trackFieldUpdate(
            $session,
            'client.prenom',
            'Jean-Pierre',
            FieldEdit::SOURCE_UPDATED,
            $user
        );
        $this->entityManager->flush();

        // Verify complete history
        $edits = $this->repository->findBySessionAndFieldPath($session, 'client.prenom');
        $this->assertCount(2, $edits);
        $this->assertEquals('Jean', $edits[0]->getNewValue());
        $this->assertEquals('Jean-Pierre', $edits[1]->getNewValue());
        $this->assertEquals(FieldEdit::SOURCE_UPDATED, $edits[1]->getSource());

        // Cleanup
        $this->entityManager->remove($session);
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }

    public function testSourcingReportAggregation(): void
    {
        $provenanceService = self::getContainer()->get('App\Service\FieldProvenanceService');

        // Create test user and session
        $user = new User();
        $user->setUsername('test-sourcing');
        $user->setEmail('test-sourcing@example.com');
        $user->setPassword(password_hash('test', PASSWORD_BCRYPT));
        $user->setRoles(['ROLE_USER']);
        $this->entityManager->persist($user);

        $session = new OnboardingSession($user);
        $session->setStatus(OnboardingSession::STATUS_IN_PROGRESS);
        $session->setPhase(OnboardingSession::PHASE_DISCOVERY);
        $this->entityManager->persist($session);
        $this->entityManager->flush();

        // Create edits with different sources
        $provenanceService->trackFieldUpdate(
            $session, 'client.prenom', 'Jean', FieldEdit::SOURCE_DECLARED, $user
        );
        $provenanceService->trackFieldUpdate(
            $session, 'client.nom', 'Dupont', FieldEdit::SOURCE_DECLARED, $user
        );
        $provenanceService->trackFieldUpdate(
            $session, 'client.email', 'jean@example.com', FieldEdit::SOURCE_DETECTED, $user
        );
        $provenanceService->trackFieldUpdate(
            $session, 'client.prenom', 'Jean-Pierre', FieldEdit::SOURCE_UPDATED, $user
        );
        $this->entityManager->flush();

        // Get report
        $report = $provenanceService->getSourcingReport($session);

        $this->assertEquals(2, $report['declared']);
        $this->assertEquals(1, $report['detected']);
        $this->assertEquals(1, $report['updated']);
        $this->assertEquals(0, $report['corrected']);

        // Cleanup
        $this->entityManager->remove($session);
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }

    public function testOnboardingFieldUpdateKeepsCurrentValueAndHistory(): void
    {
        /** @var OnboardingService $onboardingService */
        $onboardingService = self::getContainer()->get(OnboardingService::class);

        $user = new User();
        $user->setUsername('test-collaborative-dossier');
        $user->setEmail('test-collaborative-dossier@example.com');
        $user->setPassword(password_hash('test', PASSWORD_BCRYPT));
        $user->setRoles(['ROLE_CLIENT']);
        $this->entityManager->persist($user);

        $session = new OnboardingSession($user);
        $this->entityManager->persist($session);
        $this->entityManager->flush();

        $firstProvenance = $onboardingService->updateSessionField(
            $session,
            'client.prenom',
            'Jean',
            FieldEdit::SOURCE_DECLARED,
            $user,
        );
        $correctedProvenance = $onboardingService->updateSessionField(
            $session,
            'client.prenom',
            'Jean-Pierre',
            FieldEdit::SOURCE_CORRECTED,
            $user,
            'Correction apres verification',
            FieldEdit::ROLE_PRESCRIBER,
        );

        $this->assertEquals('Jean', $firstProvenance['current']);
        $this->assertEquals('Jean-Pierre', $correctedProvenance['current']);
        $this->assertCount(2, $correctedProvenance['history']);
        $this->assertEquals('Jean', $correctedProvenance['history'][1]['oldValue']);

        $this->entityManager->clear();
        /** @var OnboardingSession $storedSession */
        $storedSession = $this->entityManager->getRepository(OnboardingSession::class)->find($session->getId());
        $this->assertEquals('Jean-Pierre', $storedSession->getExtractedData()['client']['prenom']);

        $edits = $this->repository->findBySessionAndFieldPath($storedSession, 'client.prenom');
        $this->assertCount(2, $edits);
        $this->assertEquals(FieldEdit::SOURCE_DECLARED, $edits[0]->getSource());
        $this->assertEquals(FieldEdit::SOURCE_CORRECTED, $edits[1]->getSource());
        $this->assertEquals(FieldEdit::ROLE_PRESCRIBER, $edits[1]->getRole());
        $this->assertEquals('Correction apres verification', $edits[1]->getReason());
    }

    public function testMigrationFormatConversion(): void
    {
        $provenanceService = self::getContainer()->get('App\Service\FieldProvenanceService');

        // Old format data
        $oldData = [
            'client' => [
                'prenom' => 'Jean',
                'nom' => 'Dupont',
                'email' => null,
            ],
            'account' => [
                'name' => 'Compte principal',
            ],
        ];

        // Convert to provenance format
        $provenanceData = $provenanceService->migrateToProvenanceFormat($oldData, FieldEdit::SOURCE_DECLARED);

        // Verify structure
        $this->assertArrayHasKey('client', $provenanceData);
        $this->assertArrayHasKey('prenom', $provenanceData['client']);

        $prenom = $provenanceData['client']['prenom'];
        $this->assertEquals('Jean', $prenom['current']);
        $this->assertEquals(FieldEdit::SOURCE_DECLARED, $prenom['source']);
        $this->assertIsArray($prenom['history']);
        $this->assertCount(1, $prenom['history']);

        // Extract current values back
        $extracted = $provenanceService->extractCurrentValues($provenanceData);
        $this->assertEquals('Jean', $extracted['client']['prenom']);
        $this->assertEquals('Dupont', $extracted['client']['nom']);
        $this->assertNull($extracted['client']['email']);
        $this->assertEquals('Compte principal', $extracted['account']['name']);
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
}
