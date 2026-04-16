<?php

namespace App\Tests\Integration;

use App\Entity\Account;
use App\Entity\Contact;
use App\Entity\Document;
use App\Entity\PortalAccessLink;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PortalAccessFlowTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->resetDatabase();
    }

    public function testAdminCanSendPortalAccessEmailAndClientCanActivateAccess(): void
    {
        $contact = (new Contact())
            ->setFirstname('Nina')
            ->setLastname('Vermeulen')
            ->setEmail('nina.vermeulen@example.test')
            ->setPhone('0102030405')
            ->setCity('Bruxelles');

        $account = (new Account())
            ->setName('Famille Vermeulen')
            ->setCity('Bruxelles')
            ->setType('Core')
            ->addContact($contact);

        $document = (new Document())
            ->setName('Carte identite');
        $document->addContact($contact);

        $this->entityManager->persist($contact);
        $this->entityManager->persist($account);
        $this->entityManager->persist($document);
        $this->entityManager->flush();

        $admin = $this->createUser('admin-portal', ['ROLE_ADMIN'], 'admin-portal@example.test');
        $this->client->loginUser($admin);

        $this->client->request('GET', sprintf('/contacts/%d', $contact->getId()));
        $this->client->submitForm('Creer et envoyer l acces portail');

        self::assertResponseRedirects(sprintf('/contacts/%d', $contact->getId()));
        $this->client->followRedirect();
        $this->assertResponseContains('Acces portail envoye');

        /** @var User $clientUser */
        $clientUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'nina.vermeulen@example.test']);
        /** @var PortalAccessLink $accessLink */
        $accessLink = $this->entityManager->getRepository(PortalAccessLink::class)->findOneBy(['contact' => $contact]);

        self::assertNotNull($clientUser);
        self::assertTrue($clientUser->isClientUser());
        self::assertNotNull($accessLink);
        self::assertSame($clientUser->getId(), $accessLink->getUser()->getId());
        self::assertSame('Nina Vermeulen', $accessLink->getSummarySnapshot()['contact']['full_name']);

        $this->client->request('GET', '/logout');
        $this->client->request('GET', sprintf('/portal/access/%s', $accessLink->getToken()));

        self::assertResponseIsSuccessful();
        $this->assertResponseContains('Activer mon acces');
        $this->assertResponseContains('Famille Vermeulen');
        $this->assertResponseContains('Carte identite');

        $this->client->submitForm('Definir mon mot de passe', [
            'change_password[plainPassword][first]' => 'MonMotdepasse123!',
            'change_password[plainPassword][second]' => 'MonMotdepasse123!',
        ]);

        self::assertResponseRedirects('/login');
        $this->client->followRedirect();
        $this->assertResponseContains('Votre acces est active');

        $this->entityManager->clear();
        /** @var PortalAccessLink $usedLink */
        $usedLink = $this->entityManager->getRepository(PortalAccessLink::class)->find($accessLink->getId());
        /** @var User $reloadedUser */
        $reloadedUser = $this->entityManager->getRepository(User::class)->find($clientUser->getId());

        self::assertNotNull($usedLink->getUsedAt());
        self::assertNotSame('MonMotdepasse123!', $reloadedUser->getPassword());

        $this->client->request('GET', '/login');
        $this->client->submitForm('Se connecter', [
            'username' => $reloadedUser->getUsername(),
            'password' => 'MonMotdepasse123!',
        ]);

        self::assertResponseRedirects('/portal');
        $this->client->followRedirect();

        $this->assertResponseContains('Mon espace');
        $this->assertResponseContains('Famille Vermeulen');
    }

    public function testPortalContactEditionSynchronizesUserEmail(): void
    {
        $contact = (new Contact())
            ->setFirstname('Leo')
            ->setLastname('Marchal')
            ->setEmail('leo.old@example.test');

        $user = (new User())
            ->setUsername('leo-marchal')
            ->setEmail('leo.old@example.test')
            ->setRoles(['ROLE_CLIENT'])
            ->setPassword('hashed')
            ->setContact($contact);

        $this->entityManager->persist($contact);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->client->loginUser($user);
        $this->client->request('GET', '/portal/dossier');
        $this->client->submitForm('Mettre a jour', [
            'portal_contact[email]' => 'leo.new@example.test',
            'portal_contact[firstname]' => 'Leo',
            'portal_contact[lastname]' => 'Marchal',
        ]);

        self::assertResponseRedirects('/portal');
        $this->entityManager->clear();

        /** @var User $reloadedUser */
        $reloadedUser = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'leo-marchal']);
        self::assertSame('leo.new@example.test', $reloadedUser->getEmail());
    }

    private function createUser(string $username, array $roles, string $email): User
    {
        $user = (new User())
            ->setUsername($username)
            ->setEmail($email)
            ->setRoles($roles)
            ->setPassword('not-used-in-webtest');

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

    private function assertResponseContains(string $text): void
    {
        self::assertStringContainsString($text, (string) $this->client->getResponse()->getContent());
    }
}
