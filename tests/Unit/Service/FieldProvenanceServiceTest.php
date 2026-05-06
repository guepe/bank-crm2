<?php

namespace App\Tests\Unit\Service;

use App\Entity\FieldEdit;
use App\Entity\OnboardingSession;
use App\Entity\User;
use App\Repository\FieldEditRepository;
use App\Service\FieldProvenanceService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class FieldProvenanceServiceTest extends TestCase
{
    private FieldProvenanceService $service;
    private EntityManagerInterface $entityManager;
    private FieldEditRepository $repository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createStub(EntityManagerInterface::class);
        $this->repository = $this->createStub(FieldEditRepository::class);

        $this->service = new FieldProvenanceService($this->entityManager, $this->repository);
    }

    public function testTrackFieldUpdate(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');

        $session = new OnboardingSession($user);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $repository = $this->createMock(FieldEditRepository::class);
        $service = new FieldProvenanceService($entityManager, $repository);

        $repository
            ->expects($this->once())
            ->method('findBySessionAndFieldPath')
            ->with($session, 'client.prenom')
            ->willReturn([]);
        $entityManager->expects($this->once())->method('persist')->with($this->isInstanceOf(FieldEdit::class));

        $provenance = $service->trackFieldUpdate(
            $session,
            'client.prenom',
            'Jean',
            FieldEdit::SOURCE_DECLARED,
            $user
        );

        $this->assertSame('Jean', $provenance['current']);
        $this->assertSame(FieldEdit::SOURCE_DECLARED, $provenance['source']);
        $this->assertCount(1, $provenance['history']);
        $this->assertSame('test@example.com', $provenance['history'][0]['author']);
    }

    public function testMigrateToProvenanceFormat(): void
    {
        $oldData = [
            'client' => [
                'prenom' => 'Jean',
                'nom' => 'Dupont',
            ],
            'account' => [
                'name' => 'Mon compte',
            ],
        ];

        $result = $this->service->migrateToProvenanceFormat($oldData, FieldEdit::SOURCE_DECLARED);

        // Check structure
        $this->assertIsArray($result);
        $this->assertArrayHasKey('client', $result);
        $this->assertArrayHasKey('prenom', $result['client']);

        $prenom = $result['client']['prenom'];
        $this->assertArrayHasKey('current', $prenom);
        $this->assertArrayHasKey('source', $prenom);
        $this->assertArrayHasKey('history', $prenom);
        $this->assertEquals('Jean', $prenom['current']);
        $this->assertEquals(FieldEdit::SOURCE_DECLARED, $prenom['source']);
        $this->assertCount(1, $prenom['history']);
    }

    public function testExtractCurrentValues(): void
    {
        $provenanceData = [
            'client' => [
                'prenom' => [
                    'current' => 'Jean',
                    'source' => FieldEdit::SOURCE_DECLARED,
                    'history' => [],
                ],
                'nom' => [
                    'current' => 'Dupont',
                    'source' => FieldEdit::SOURCE_DECLARED,
                    'history' => [],
                ],
            ],
        ];

        $result = $this->service->extractCurrentValues($provenanceData);

        $this->assertIsArray($result);
        $this->assertEquals('Jean', $result['client']['prenom']);
        $this->assertEquals('Dupont', $result['client']['nom']);
    }

    public function testExtractCurrentValuesKeepsNullCurrentValue(): void
    {
        $provenanceData = [
            'client' => [
                'email' => [
                    'current' => null,
                    'source' => FieldEdit::SOURCE_DECLARED,
                    'history' => [],
                ],
            ],
        ];

        $result = $this->service->extractCurrentValues($provenanceData);

        $this->assertArrayHasKey('email', $result['client']);
        $this->assertNull($result['client']['email']);
    }

    public function testGetSourcingReport(): void
    {
        $user = new User();
        $session = new OnboardingSession($user);

        // Mock repository to return counts
        $this->repository
            ->method('countBySessionAndSource')
            ->willReturnMap([
                [$session, FieldEdit::SOURCE_DECLARED, 5],
                [$session, FieldEdit::SOURCE_DETECTED, 2],
                [$session, FieldEdit::SOURCE_UPDATED, 1],
                [$session, FieldEdit::SOURCE_CORRECTED, 0],
            ]);

        $report = $this->service->getSourcingReport($session);

        $this->assertEquals(5, $report['declared']);
        $this->assertEquals(2, $report['detected']);
        $this->assertEquals(1, $report['updated']);
        $this->assertEquals(0, $report['corrected']);
    }
}
