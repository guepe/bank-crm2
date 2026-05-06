<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserSecurityToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserSecurityToken>
 */
class UserSecurityTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserSecurityToken::class);
    }

    public function findActiveByToken(string $token, string $purpose): ?UserSecurityToken
    {
        $securityToken = $this->findOneBy([
            'token' => $token,
            'purpose' => $purpose,
        ]);

        return $securityToken instanceof UserSecurityToken && $securityToken->isActive() ? $securityToken : null;
    }

    /**
     * @return list<UserSecurityToken>
     */
    public function findActiveForUser(User $user, string $purpose): array
    {
        return $this->createQueryBuilder('token')
            ->andWhere('token.user = :user')
            ->andWhere('token.purpose = :purpose')
            ->andWhere('token.usedAt IS NULL')
            ->andWhere('token.expiresAt > :now')
            ->setParameter('user', $user)
            ->setParameter('purpose', $purpose)
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getResult();
    }
}
