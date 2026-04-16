<?php

namespace App\Service;

use App\Entity\BankAccessLink;
use App\Entity\BankProduct;
use App\Entity\BankRelationship;
use App\Repository\BankAccessLinkRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BankRelationshipManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly BankAccessLinkRepository $bankAccessLinkRepository,
        private readonly BrevoMailer $mailer,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function sendBankRequest(BankRelationship $bankRelationship): BankAccessLink
    {
        $email = $bankRelationship->getBankContactEmail();
        if ($email === null || $email === '') {
            throw new \InvalidArgumentException('La relation bancaire doit avoir un e-mail interlocuteur pour envoyer le dossier.');
        }

        foreach ($this->bankAccessLinkRepository->findActiveForRelationship($bankRelationship) as $existingLink) {
            $existingLink->revoke();
        }

        $accessLink = (new BankAccessLink($bankRelationship))
            ->setToken(bin2hex(random_bytes(32)))
            ->setSummarySnapshot($this->buildSummarySnapshot($bankRelationship))
            ->markSent();

        $this->entityManager->persist($accessLink);
        $this->entityManager->flush();

        $accessUrl = $this->urlGenerator->generate('app_bank_access', [
            'token' => $accessLink->getToken(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $this->mailer->sendTemplatedEmail(
            $email,
            'Dossier client a completer',
            'emails/bank_access.html.twig',
            [
                'relationship' => $bankRelationship,
                'contact' => $bankRelationship->getContact(),
                'summary' => $accessLink->getSummarySnapshot(),
                'access_url' => $accessUrl,
                'expires_at' => $accessLink->getExpiresAt(),
            ],
            new Address($email, $bankRelationship->getBankContactName() ?? $bankRelationship->getBankName()),
        );

        return $accessLink;
    }

    public function buildSummarySnapshot(BankRelationship $bankRelationship): array
    {
        $contact = $bankRelationship->getContact();

        return [
            'contact' => [
                'full_name' => (string) $contact,
                'email' => $contact->getEmail(),
                'phone' => $contact->getPhone(),
                'gsm' => $contact->getGsm(),
            ],
            'bank' => [
                'name' => $bankRelationship->getBankName(),
                'contact_name' => $bankRelationship->getBankContactName(),
                'contact_email' => $bankRelationship->getBankContactEmail(),
            ],
            'accounts' => array_map(
                static fn ($account): array => [
                    'name' => $account->getName(),
                    'type' => $account->getType(),
                    'city' => $account->getCity(),
                ],
                $contact->getAccounts()->toArray()
            ),
            'documents' => array_map(
                static fn ($document): array => ['name' => (string) $document],
                $contact->getDocuments()->toArray()
            ),
        ];
    }

    /**
     * @param array{account:\App\Entity\Account,number:?string,type:?string,amount:mixed,notes:?string} $data
     */
    public function addSubmittedProduct(BankAccessLink $accessLink, array $data): BankProduct
    {
        $relationship = $accessLink->getBankRelationship();
        $product = (new BankProduct())
            ->setNumber($data['number'] ?? null)
            ->setType($data['type'] ?? null)
            ->setAmount($data['amount'] !== null && $data['amount'] !== '' ? (string) $data['amount'] : null)
            ->setCompany($relationship->getBankName())
            ->setNotes(trim(implode("\n", array_filter([
                $data['notes'] ?? null,
                $relationship->getBankContactName() ? 'Interlocuteur bancaire: '.$relationship->getBankContactName() : null,
            ]))));

        $data['account']->addProduct($product);
        $this->entityManager->persist($product);
        $accessLink->markResponded();
        $this->entityManager->flush();

        return $product;
    }

    /** @return list<BankProduct> */
    public function getSubmittedProducts(BankRelationship $bankRelationship): array
    {
        $products = [];
        foreach ($bankRelationship->getContact()->getAccounts() as $account) {
            foreach ($account->getBankProducts() as $product) {
                if ($product->getCompany() === $bankRelationship->getBankName()) {
                    $products[] = $product;
                }
            }
        }

        return $products;
    }
}
