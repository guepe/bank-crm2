<?php

namespace App\Service;

use App\Entity\Contact;
use App\Entity\PortalAccessLink;
use App\Entity\User;
use App\Repository\PortalAccessLinkRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

class PortalAccessManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly PortalAccessLinkRepository $portalAccessLinkRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly MailerInterface $mailer,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function sendAccessEmail(Contact $contact): PortalAccessLink
    {
        $email = $contact->getEmail();
        if ($email === null || $email === '') {
            throw new \InvalidArgumentException('Le contact doit avoir une adresse e-mail pour recevoir un acces portail.');
        }

        $user = $this->ensureClientUser($contact);

        foreach ($this->portalAccessLinkRepository->findActiveForContact($contact) as $existingLink) {
            $existingLink->revoke();
        }

        $link = (new PortalAccessLink($user, $contact))
            ->setToken(bin2hex(random_bytes(32)))
            ->setSummarySnapshot($this->buildSummarySnapshot($contact))
            ->markSent();

        $this->entityManager->persist($link);
        $this->entityManager->flush();

        $accessUrl = $this->urlGenerator->generate('app_portal_access', [
            'token' => $link->getToken(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $emailMessage = (new TemplatedEmail())
            ->from(new Address('noreply@bank-crm.local', 'Bank CRM'))
            ->to($email)
            ->subject('Votre acces portail est pret')
            ->htmlTemplate('emails/portal_access.html.twig')
            ->context([
                'contact' => $contact,
                'user' => $user,
                'access_url' => $accessUrl,
                'expires_at' => $link->getExpiresAt(),
                'summary' => $link->getSummarySnapshot(),
            ]);

        $this->mailer->send($emailMessage);

        return $link;
    }

    public function buildSummarySnapshot(Contact $contact): array
    {
        $address = trim(implode(', ', array_filter([
            $contact->getStreetNum(),
            trim(implode(' ', array_filter([$contact->getZip(), $contact->getCity()]))),
            $contact->getCountry(),
        ])));

        return [
            'contact' => [
                'full_name' => (string) $contact,
                'email' => $contact->getEmail(),
                'phone' => $contact->getPhone(),
                'gsm' => $contact->getGsm(),
                'address' => $address !== '' ? $address : null,
                'birthdate' => $contact->getBirthdate()?->format('d/m/Y'),
            ],
            'accounts' => array_map(
                static fn ($account): array => [
                    'name' => $account->getName(),
                    'city' => $account->getCity(),
                    'type' => $account->getType(),
                ],
                $contact->getAccounts()->toArray()
            ),
            'documents' => array_map(
                static fn ($document): array => [
                    'name' => (string) $document,
                ],
                $contact->getDocuments()->toArray()
            ),
        ];
    }

    private function ensureClientUser(Contact $contact): User
    {
        $existingUser = $contact->getUserAccount();
        if ($existingUser instanceof User) {
            $existingUser->setRoles(array_values(array_unique([
                ...$existingUser->getRoles(),
                'ROLE_CLIENT',
            ])));
            $existingUser->setEnabled(true);
            if ($contact->getEmail() !== null) {
                $existingUser->setEmail($contact->getEmail());
            }

            return $existingUser;
        }

        $user = (new User())
            ->setUsername($this->generateUniqueUsername($contact))
            ->setEmail($contact->getEmail())
            ->setRoles(['ROLE_CLIENT'])
            ->setEnabled(true)
            ->setContact($contact);

        $user->setPassword($this->passwordHasher->hashPassword($user, bin2hex(random_bytes(16))));

        $this->entityManager->persist($user);

        return $user;
    }

    private function generateUniqueUsername(Contact $contact): string
    {
        $slugger = new AsciiSlugger();
        $base = trim((string) $slugger->slug((string) $contact)->lower()->toString(), '-');

        if ($base === '') {
            $base = $contact->getEmail() !== null
                ? trim((string) $slugger->slug(strtok($contact->getEmail(), '@'))->lower()->toString(), '-')
                : 'client';
        }

        $candidate = $base !== '' ? $base : 'client';
        $suffix = 1;

        while ($this->userRepository->findOneBy(['username' => $candidate]) instanceof User) {
            ++$suffix;
            $candidate = sprintf('%s-%d', $base !== '' ? $base : 'client', $suffix);
        }

        return $candidate;
    }
}
