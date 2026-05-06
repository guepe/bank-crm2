<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\UserSecurityToken;
use App\Repository\UserRepository;
use App\Repository\UserSecurityTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UserAccountSecurityManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly UserSecurityTokenRepository $tokenRepository,
        private readonly BrevoMailer $mailer,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function sendEmailVerification(User $user): ?UserSecurityToken
    {
        if ($user->isEmailVerified()) {
            return null;
        }

        $email = $user->getEmail();
        if ($email === null || $email === '') {
            return null;
        }

        $token = $this->createToken($user, UserSecurityToken::PURPOSE_EMAIL_VERIFICATION, '+24 hours');
        $verificationUrl = $this->urlGenerator->generate('app_verify_email', [
            'token' => $token->getToken(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $this->mailer->sendTemplatedEmail(
            $email,
            'Confirmez votre adresse e-mail Planilife',
            'emails/email_verification.html.twig',
            [
                'user' => $user,
                'verification_url' => $verificationUrl,
                'expires_at' => $token->getExpiresAt(),
            ],
            new Address($email, $user->getDisplayName()),
        );

        return $token;
    }

    public function verifyEmailToken(string $token): ?User
    {
        $securityToken = $this->tokenRepository->findActiveByToken($token, UserSecurityToken::PURPOSE_EMAIL_VERIFICATION);
        if (!$securityToken instanceof UserSecurityToken) {
            return null;
        }

        $user = $securityToken->getUser();
        $user->markEmailVerified();
        $securityToken->markUsed();
        $this->entityManager->flush();

        return $user;
    }

    public function sendPasswordResetForIdentifier(string $identifier): void
    {
        $identifier = mb_strtolower(trim($identifier));
        if ($identifier === '') {
            return;
        }

        $user = $this->userRepository->findOneBy(['email' => $identifier])
            ?? $this->userRepository->findOneBy(['username' => $identifier]);

        if (!$user instanceof User || $user->getEmail() === null || $user->getEmail() === '') {
            return;
        }

        $token = $this->createToken($user, UserSecurityToken::PURPOSE_PASSWORD_RESET, '+1 hour');
        $resetUrl = $this->urlGenerator->generate('app_password_reset', [
            'token' => $token->getToken(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $this->mailer->sendTemplatedEmail(
            $user->getEmail(),
            'Reinitialisation de votre mot de passe Planilife',
            'emails/password_reset.html.twig',
            [
                'user' => $user,
                'reset_url' => $resetUrl,
                'expires_at' => $token->getExpiresAt(),
            ],
            new Address($user->getEmail(), $user->getDisplayName()),
        );
    }

    public function consumePasswordResetToken(string $token): ?UserSecurityToken
    {
        return $this->tokenRepository->findActiveByToken($token, UserSecurityToken::PURPOSE_PASSWORD_RESET);
    }

    public function markPasswordResetUsed(UserSecurityToken $token): void
    {
        $token->markUsed();
        $this->entityManager->flush();
    }

    private function createToken(User $user, string $purpose, string $ttl): UserSecurityToken
    {
        foreach ($this->tokenRepository->findActiveForUser($user, $purpose) as $existingToken) {
            $existingToken->markUsed();
        }

        $token = new UserSecurityToken(
            $user,
            $purpose,
            bin2hex(random_bytes(32)),
            (new \DateTimeImmutable())->modify($ttl),
        );

        $this->entityManager->persist($token);
        $this->entityManager->flush();

        return $token;
    }
}
