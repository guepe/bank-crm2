<?php

namespace App\Repository;

use App\Entity\BankAccessLink;
use App\Entity\BankRelationship;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class BankAccessLinkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BankAccessLink::class);
    }

    public function findActiveByToken(string $token): ?BankAccessLink
    {
        $link = $this->findOneBy(['token' => $token]);

        if (!$link instanceof BankAccessLink || !$link->isActive()) {
            return null;
        }

        return $link;
    }

    /** @return list<BankAccessLink> */
    public function findActiveForRelationship(BankRelationship $bankRelationship): array
    {
        return array_values(array_filter(
            $this->findBy(['bankRelationship' => $bankRelationship], ['id' => 'DESC']),
            static fn (BankAccessLink $link): bool => $link->isActive()
        ));
    }
}
