<?php

namespace App\Tests\Integration;

use App\Entity\Contact;
use App\Entity\OnboardingSession;
use App\Entity\User;
use App\Entity\UserSecurityToken;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ClientTrustFlowTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $this->resetDatabase();
    }

    public function testRegistrationCreatesEmailVerificationTokenAndVerifiedClientCanLogin(): void
    {
        $this->client->request('GET', '/register');
        $this->client->submitForm('Créer mon compte', [
            'registration[firstname]' => 'Elise',
            'registration[lastname]' => 'Martin',
            'registration[username]' => 'elise-martin',
            'registration[email]' => 'elise.martin@example.test',
            'registration[plainPassword][first]' => 'Password123!',
            'registration[plainPassword][second]' => 'Password123!',
        ]);

        self::assertResponseRedirects('/verify-email');
        /** @var User $user */
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'elise-martin']);
        self::assertNotNull($user);
        self::assertFalse($user->isEmailVerified());

        /** @var UserSecurityToken $token */
        $token = $this->entityManager->getRepository(UserSecurityToken::class)->findOneBy([
            'user' => $user,
            'purpose' => UserSecurityToken::PURPOSE_EMAIL_VERIFICATION,
        ]);
        self::assertNotNull($token);

        $this->client->request('GET', '/verify-email/'.$token->getToken());
        self::assertResponseRedirects('/login');

        $this->entityManager->clear();
        /** @var User $verifiedUser */
        $verifiedUser = $this->entityManager->getRepository(User::class)->find($user->getId());
        self::assertTrue($verifiedUser->isEmailVerified());

        $this->client->request('GET', '/login');
        $this->client->submitForm('Se connecter', [
            'username' => 'elise-martin',
            'password' => 'Password123!',
        ]);

        self::assertResponseRedirects('/portal');
    }

    public function testConsentIsRequiredBeforeStartingOnboarding(): void
    {
        $user = $this->createClientUser('consent-client', 'consent@example.test', verifyEmail: true, acceptConsent: false);
        $this->client->loginUser($user);

        $this->client->request('GET', '/onboarding/new');
        self::assertResponseRedirects('/portal/consentement');

        $this->client->followRedirect();
        $this->client->submitForm('Valider et commencer l\'entretien', [
            'accept_consent' => '1',
        ]);

        self::assertResponseRedirects('/onboarding/new');
        $this->client->followRedirect();
        self::assertResponseRedirects();
        self::assertStringContainsString('/onboarding/', (string) $this->client->getResponse()->headers->get('Location'));

        $this->entityManager->clear();
        /** @var User $updatedUser */
        $updatedUser = $this->entityManager->getRepository(User::class)->find($user->getId());
        self::assertTrue($updatedUser->hasAcceptedConsent());
    }

    public function testClientCanExportDataAndRequestDeletion(): void
    {
        $user = $this->createClientUser('data-client', 'data@example.test');
        $session = new OnboardingSession($user);
        $session->setExtractedData(['client' => ['prenom' => 'Data']]);
        $this->entityManager->persist($session);
        $this->entityManager->flush();

        $this->client->loginUser($user);
        $this->client->request('GET', '/portal/donnees/export');

        self::assertResponseIsSuccessful();
        self::assertSame('application/json', $this->client->getResponse()->headers->get('content-type'));
        self::assertStringContainsString('planilife-donnees.json', (string) $this->client->getResponse()->headers->get('content-disposition'));
        $payload = json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('data-client', $payload['user']['username']);
        self::assertSame('Data', $payload['onboarding_sessions'][0]['extracted_data']['client']['prenom']);

        $this->client->request('GET', '/portal/donnees');
        $this->client->submitForm('Demander la suppression');
        self::assertResponseRedirects('/portal/donnees');

        $this->entityManager->clear();
        /** @var User $updatedUser */
        $updatedUser = $this->entityManager->getRepository(User::class)->find($user->getId());
        self::assertNotNull($updatedUser->getDataExportRequestedAt());
        self::assertNotNull($updatedUser->getDataDeletionRequestedAt());
    }

    public function testClientCanResetPasswordWithEmailToken(): void
    {
        $user = $this->createClientUser('reset-client', 'reset@example.test', password: 'OldPassword123!');

        $this->client->request('GET', '/forgot-password');
        $this->client->submitForm('Envoyer le lien', [
            'form[identifier]' => 'reset@example.test',
        ]);

        self::assertResponseRedirects('/login');
        /** @var UserSecurityToken $token */
        $token = $this->entityManager->getRepository(UserSecurityToken::class)->findOneBy([
            'user' => $user,
            'purpose' => UserSecurityToken::PURPOSE_PASSWORD_RESET,
        ]);
        self::assertNotNull($token);

        $this->client->request('GET', '/reset-password/'.$token->getToken());
        $this->client->submitForm('Reinitialiser mon mot de passe', [
            'change_password[plainPassword][first]' => 'NewPassword123!',
            'change_password[plainPassword][second]' => 'NewPassword123!',
        ]);

        self::assertResponseRedirects('/login');

        $this->client->request('GET', '/login');
        $this->client->submitForm('Se connecter', [
            'username' => 'reset-client',
            'password' => 'NewPassword123!',
        ]);

        self::assertResponseRedirects('/portal');
    }

    private function createClientUser(
        string $username,
        string $email,
        string $password = 'Password123!',
        bool $verifyEmail = true,
        bool $acceptConsent = true,
    ): User {
        $contact = (new Contact())
            ->setFirstname($username)
            ->setLastname('Client')
            ->setEmail($email);

        $user = (new User())
            ->setUsername($username)
            ->setEmail($email)
            ->setRoles(['ROLE_CLIENT'])
            ->setContact($contact);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));

        if ($verifyEmail) {
            $user->markEmailVerified();
        }

        if ($acceptConsent) {
            $user->acceptConsent('planilife-beta-2026-05');
        }

        $this->entityManager->persist($contact);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    private function resetDatabase(): void
    {
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($this->entityManager);

        if ($metadata === []) {
            return;
        }

        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }
}
