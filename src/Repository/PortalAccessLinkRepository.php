<?php

namespace App\Repository;

use App\Entity\Contact;
use App\Entity\PortalAccessLink;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PortalAccessLinkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PortalAccessLink::class);
    }

    public function findActiveByToken(string $token): ?PortalAccessLink
    {
        $link = $this->findOneBy(['token' => $token]);

        if (!$link instanceof PortalAccessLink || !$link->isActive()) {
            return null;
        }

        return $link;
    }

    /** @return list<PortalAccessLink> */
    public function findActiveForContact(Contact $contact): array
    {
        return array_values(array_filter(
            $this->findBy(['contact' => $contact], ['id' => 'DESC']),
            static fn (PortalAccessLink $link): bool => $link->isActive()
        ));
    }

    /** @return list<PortalAccessLink> */
    public function findByContact(Contact $contact): array
    {
        return $this->findBy(['contact' => $contact], ['id' => 'DESC']);
    }
}
