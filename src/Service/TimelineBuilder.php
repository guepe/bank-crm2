<?php

namespace App\Service;

use App\Entity\Account;
use App\Entity\BankAccessLink;
use App\Entity\Contact;
use App\Entity\PortalAccessLink;
use App\Repository\PortalAccessLinkRepository;

class TimelineBuilder
{
    public function __construct(private readonly PortalAccessLinkRepository $portalAccessLinkRepository)
    {
    }

    /**
     * @return list<array{date:\DateTimeInterface,label:string,context:string}>
     */
    public function buildForContact(Contact $contact): array
    {
        $events = [[
            'date' => $contact->getCreatedAt(),
            'label' => 'Contact cree',
            'context' => (string) $contact,
        ]];

        foreach ($contact->getAccounts() as $account) {
            $events[] = [
                'date' => $account->getCreatedAt(),
                'label' => 'Compte lie',
                'context' => $account->getName(),
            ];

            if ($account->getStartingDate() !== null) {
                $events[] = [
                    'date' => $account->getStartingDate(),
                    'label' => 'Debut de relation',
                    'context' => sprintf('Compte %s', $account->getName()),
                ];
            }
        }

        foreach ($contact->getDocuments() as $document) {
            $events[] = [
                'date' => $document->getCreatedAt(),
                'label' => 'Document ajoute',
                'context' => (string) $document,
            ];
        }

        foreach ($contact->getBankRelationships() as $relationship) {
            $events[] = [
                'date' => $relationship->getCreatedAt(),
                'label' => 'Relation bancaire creee',
                'context' => $relationship->getBankName(),
            ];

            foreach ($relationship->getAccessLinks() as $accessLink) {
                $events = [...$events, ...$this->buildBankAccessEvents($accessLink)];
            }
        }

        foreach ($this->portalAccessLinkRepository->findByContact($contact) as $portalAccessLink) {
            $events = [...$events, ...$this->buildPortalAccessEvents($portalAccessLink)];
        }

        return $this->sortEvents($events);
    }

    /**
     * @return list<array{date:\DateTimeInterface,label:string,context:string}>
     */
    public function buildForAccount(Account $account): array
    {
        $events = [[
            'date' => $account->getCreatedAt(),
            'label' => 'Compte cree',
            'context' => $account->getName(),
        ]];

        if ($account->getStartingDate() !== null) {
            $events[] = [
                'date' => $account->getStartingDate(),
                'label' => 'Debut de relation',
                'context' => $account->getName(),
            ];
        }

        foreach ($account->getContacts() as $contact) {
            $events[] = [
                'date' => $contact->getCreatedAt(),
                'label' => 'Contact lie',
                'context' => (string) $contact,
            ];
        }

        foreach ($account->getDocuments() as $document) {
            $events[] = [
                'date' => $document->getCreatedAt(),
                'label' => 'Document ajoute',
                'context' => (string) $document,
            ];
        }

        return $this->sortEvents($events);
    }

    /**
     * @param list<array{date:\DateTimeInterface,label:string,context:string}> $events
     * @return list<array{date:\DateTimeInterface,label:string,context:string}>
     */
    private function sortEvents(array $events): array
    {
        usort(
            $events,
            static fn (array $left, array $right): int => $right['date'] <=> $left['date']
        );

        return $events;
    }

    /**
     * @return list<array{date:\DateTimeInterface,label:string,context:string}>
     */
    private function buildPortalAccessEvents(PortalAccessLink $portalAccessLink): array
    {
        $events = [[
            'date' => $portalAccessLink->getCreatedAt(),
            'label' => 'Acces portail prepare',
            'context' => $portalAccessLink->getUser()->getUsername(),
        ]];

        if ($portalAccessLink->getSentAt() !== null) {
            $events[] = [
                'date' => $portalAccessLink->getSentAt(),
                'label' => 'Acces portail envoye',
                'context' => $portalAccessLink->getContact()->getEmail() ?: (string) $portalAccessLink->getContact(),
            ];
        }

        if ($portalAccessLink->getUsedAt() !== null) {
            $events[] = [
                'date' => $portalAccessLink->getUsedAt(),
                'label' => 'Acces portail active',
                'context' => $portalAccessLink->getUser()->getUsername(),
            ];
        }

        return $events;
    }

    /**
     * @return list<array{date:\DateTimeInterface,label:string,context:string}>
     */
    private function buildBankAccessEvents(BankAccessLink $bankAccessLink): array
    {
        $events = [[
            'date' => $bankAccessLink->getCreatedAt(),
            'label' => 'Lien bancaire prepare',
            'context' => $bankAccessLink->getBankRelationship()->getBankName(),
        ]];

        if ($bankAccessLink->getSentAt() !== null) {
            $events[] = [
                'date' => $bankAccessLink->getSentAt(),
                'label' => 'Dossier envoye a la banque',
                'context' => $bankAccessLink->getBankRelationship()->getBankName(),
            ];
        }

        if ($bankAccessLink->getRespondedAt() !== null) {
            $events[] = [
                'date' => $bankAccessLink->getRespondedAt(),
                'label' => 'Retour banque recu',
                'context' => $bankAccessLink->getBankRelationship()->getBankName(),
            ];
        }

        return $events;
    }
}
