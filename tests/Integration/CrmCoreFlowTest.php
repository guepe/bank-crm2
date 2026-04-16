<?php

namespace App\Tests\Integration;

use App\Entity\Account;
use App\Entity\Contact;
use App\Entity\Lead;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CrmCoreFlowTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $this->resetDatabase();
        $this->client->loginUser($this->createInternalUser());
    }

    public function testDashboardShowsUsefulEntryPoints(): void
    {
        $account = (new Account())
            ->setName('Famille Martin')
            ->setCity('Bruxelles')
            ->setType('Core');

        $contact = (new Contact())
            ->setFirstname('Jeanne')
            ->setLastname('Martin')
            ->setEmail('jeanne.martin@example.test')
            ->setCity('Bruxelles');

        $lead = (new Lead())
            ->setName('Prospect Delta')
            ->setCity('Namur')
            ->setType('Potentiel');

        $account->addContact($contact);

        $this->entityManager->persist($contact);
        $this->entityManager->persist($account);
        $this->entityManager->persist($lead);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Tableau de bord');
        self::assertSame(['1', '1', '1'], $crawler->filter('.stat-card strong')->each(
            static fn ($node): string => trim($node->text())
        ));
        $this->assertResponseContains('Prospect Delta');
        $this->assertResponseContains('Jeanne Martin');
        $this->assertResponseContains('Famille Martin');
        $this->assertResponseContains('Nouveau compte');
        $this->assertResponseContains('Nouveau contact');
    }

    public function testAccountCreationRequiresANameAndRedirectsToTheAccountPage(): void
    {
        $this->client->request('GET', '/accounts/new');

        $this->client->submitForm('Enregistrer', [
            'account[name]' => '',
            'account[city]' => 'Bruxelles',
        ]);

        self::assertResponseStatusCodeSame(422);
        $this->assertResponseContains('This value should not be blank.');
        self::assertSame(0, $this->entityManager->getRepository(Account::class)->count([]));

        $this->client->submitForm('Enregistrer', [
            'account[name]' => 'Compte Alpha',
            'account[city]' => 'Bruxelles',
            'account[type]' => 'Core',
        ]);

        self::assertResponseRedirects();
        $this->client->followRedirect();

        self::assertSelectorTextContains('h1', 'Compte Alpha');
        $this->assertResponseContains('Bruxelles');
        self::assertSame(1, $this->entityManager->getRepository(Account::class)->count([]));
    }

    public function testAccountShowDisplaysMainInformationAndLinkedContacts(): void
    {
        $contact = (new Contact())
            ->setFirstname('Luc')
            ->setLastname('Dupont')
            ->setEmail('luc.dupont@example.test');

        $account = (new Account())
            ->setName('Compte Horizon')
            ->setStreetNum('Rue de la Loi 1')
            ->setZip('1000')
            ->setCity('Bruxelles')
            ->setCountry('BE')
            ->setCompanyStatut('Personne physique')
            ->setType('Core')
            ->setOtherBank('Banque Externe')
            ->setNotes('Client historique')
            ->addContact($contact);

        $this->entityManager->persist($contact);
        $this->entityManager->persist($account);
        $this->entityManager->flush();

        $this->client->request('GET', sprintf('/accounts/%d', $account->getId()));

        self::assertResponseIsSuccessful();
        $this->assertResponseContains('Compte Horizon');
        $this->assertResponseContains('Rue de la Loi 1');
        $this->assertResponseContains('Banque Externe');
        $this->assertResponseContains('Luc Dupont');
        $this->assertResponseContains('Client historique');
        $this->assertResponseContains('Ajouter un contact');
    }

    public function testContactCanBeCreatedFromAnAccountContext(): void
    {
        $account = (new Account())
            ->setName('Compte Contextuel')
            ->setCity('Liege');

        $this->entityManager->persist($account);
        $this->entityManager->flush();

        $this->client->request('GET', sprintf('/contacts/new?account=%d', $account->getId()));

        self::assertResponseIsSuccessful();
        $this->assertResponseContains('Creation depuis le compte');
        $this->assertResponseContains('Compte Contextuel');

        $this->client->submitForm('Enregistrer', [
            'contact[firstname]' => 'Alice',
            'contact[lastname]' => 'Bernard',
            'contact[email]' => 'alice.bernard@example.test',
            'contact[city]' => 'Liege',
        ]);

        self::assertResponseRedirects(sprintf('/accounts/%d', $account->getId()));
        $this->client->followRedirect();

        $this->entityManager->clear();

        /** @var Account $reloadedAccount */
        $reloadedAccount = $this->entityManager->getRepository(Account::class)->find($account->getId());

        $this->assertResponseContains('Alice Bernard');
        self::assertCount(1, $reloadedAccount->getContacts());
    }

    public function testContactCreationPageIsAlsoAvailableWithoutAccountContext(): void
    {
        $this->client->request('GET', '/contacts/new');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Nouveau contact');
        $this->assertResponseDoesNotContain('Creation depuis le compte');
    }

    private function createInternalUser(): User
    {
        $user = (new User())
            ->setUsername('crm-tester')
            ->setEmail('crm-tester@example.test')
            ->setRoles(['ROLE_USER'])
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
        self::assertStringContainsString($text, $this->client->getResponse()->getContent());
    }

    private function assertResponseDoesNotContain(string $text): void
    {
        self::assertStringNotContainsString($text, $this->client->getResponse()->getContent());
    }
}
