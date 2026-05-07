<?php

namespace App\Repository;

use App\Entity\OnboardingSession;
use App\Entity\PrescriberInvitation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PrescriberInvitation>
 */
class PrescriberInvitationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PrescriberInvitation::class);
    }

    public function findActiveByToken(string $token): ?PrescriberInvitation
    {
        return $this->createQueryBuilder('p')
            ->where('p.token = :token')
            ->andWhere('p.revokedAt IS NULL')
            ->andWhere('p.expiresAt > :now')
            ->setParameter('token', $token)
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getOneOrNullResult();
    }

    /** @return list<PrescriberInvitation> */
    public function findBySession(OnboardingSession $session): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.session = :session')
            ->setParameter('session', $session)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /** @return list<PrescriberInvitation> */
    public function findActiveBySession(OnboardingSession $session): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.session = :session')
            ->andWhere('p.revokedAt IS NULL')
            ->andWhere('p.expiresAt > :now')
            ->setParameter('session', $session)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
