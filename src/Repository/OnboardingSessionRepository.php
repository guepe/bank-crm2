<?php

namespace App\Repository;

use App\Entity\OnboardingSession;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OnboardingSession>
 */
class OnboardingSessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OnboardingSession::class);
    }

    public function findLatestByUser(User $user): ?OnboardingSession
    {
        return $this->createQueryBuilder('os')
            ->where('os.user = :user')
            ->setParameter('user', $user)
            ->orderBy('os.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return list<OnboardingSession>
     */
    public function findRecentSessions(?User $user = null): array
    {
        $queryBuilder = $this->createQueryBuilder('os')
            ->orderBy('os.updatedAt', 'DESC')
            ->addOrderBy('os.createdAt', 'DESC');

        if ($user instanceof User) {
            $queryBuilder
                ->where('os.user = :user')
                ->setParameter('user', $user);
        }

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }

    public function findInProgressByUser(User $user): ?OnboardingSession
    {
        return $this->createQueryBuilder('os')
            ->where('os.user = :user')
            ->andWhere('os.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', OnboardingSession::STATUS_IN_PROGRESS)
            ->orderBy('os.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findCompletedByUser(User $user): array
    {
        return $this->createQueryBuilder('os')
            ->where('os.user = :user')
            ->andWhere('os.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', OnboardingSession::STATUS_COMPLETED)
            ->orderBy('os.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
