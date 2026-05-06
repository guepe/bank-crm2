<?php

namespace App\Repository;

use App\Entity\FieldEdit;
use App\Entity\OnboardingSession;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FieldEdit>
 */
class FieldEditRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FieldEdit::class);
    }

    /**
     * Get all edits for a session, ordered by creation date (DESC).
     */
    public function findBySessionOrderedByDate(OnboardingSession $session): array
    {
        return $this->createQueryBuilder('fe')
            ->where('fe.session = :session')
            ->setParameter('session', $session)
            ->orderBy('fe.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get edit history for a specific field path.
     */
    public function findBySessionAndFieldPath(OnboardingSession $session, string $fieldPath): array
    {
        return $this->createQueryBuilder('fe')
            ->where('fe.session = :session')
            ->andWhere('fe.fieldPath = :fieldPath')
            ->setParameter('session', $session)
            ->setParameter('fieldPath', $fieldPath)
            ->orderBy('fe.createdAt', 'ASC')
            ->addOrderBy('fe.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get edits by source type (declared, detected, updated, corrected).
     */
    public function countBySessionAndSource(OnboardingSession $session, string $source): int
    {
        return (int) $this->createQueryBuilder('fe')
            ->select('COUNT(fe.id)')
            ->where('fe.session = :session')
            ->andWhere('fe.source = :source')
            ->setParameter('session', $session)
            ->setParameter('source', $source)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get recent edits for reporting.
     */
    public function findRecentBySession(OnboardingSession $session, int $limit = 50): array
    {
        return $this->createQueryBuilder('fe')
            ->where('fe.session = :session')
            ->setParameter('session', $session)
            ->orderBy('fe.createdAt', 'DESC')
            ->addOrderBy('fe.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
