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

class CrmListFiltersTest extends WebTestCase
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

    public function testAccountListSupportsSearchAndFilters(): void
    {
        $accountWithContact = (new Account())
            ->setName('Alpha Conseil')
            ->setCity('Bruxelles')
            ->setType('Premium')
            ->setOtherBank('Banque du Centre');
        $accountWithContact->addContact(
            (new Contact())
                ->setFirstname('Nina')
                ->setLastname('Verlaine')
                ->setEmail('nina.verlaine@example.test')
        );

        $accountWithoutContact = (new Account())
            ->setName('Beta Industrie')
            ->setCity('Namur')
            ->setType('Standard');

        $this->entityManager->persist($accountWithContact->getContacts()->first());
        $this->entityManager->persist($accountWithContact);
        $this->entityManager->persist($accountWithoutContact);
        $this->entityManager->flush();

        $this->client->request('GET', '/accounts?q=Alpha&city=Bruxelles&type=Premium&contacts=with');

        self::assertResponseIsSuccessful();
        $this->assertResponseContains('Alpha Conseil');
        $this->assertResponseDoesNotContain('Beta Industrie');
        $this->assertResponseContains('Recherche : Alpha');
        $this->assertResponseContains('Ville : Bruxelles');
        $this->assertResponseContains('Type : Premium');
        $this->assertResponseContains('Avec contacts');
    }

    public function testContactListSupportsLinkAndEmailFilters(): void
    {
        $linkedContact = (new Contact())
            ->setFirstname('Claire')
            ->setLastname('Dupuis')
            ->setEmail('claire.dupuis@example.test')
            ->setCity('Liege');

        $unlinkedContact = (new Contact())
            ->setFirstname('Marc')
            ->setLastname('Simon')
            ->setCity('Liege');

        $account = (new Account())
            ->setName('Compte Lie')
            ->setCity('Liege')
            ->addContact($linkedContact);

        $this->entityManager->persist($linkedContact);
        $this->entityManager->persist($unlinkedContact);
        $this->entityManager->persist($account);
        $this->entityManager->flush();

        $this->client->request('GET', '/contacts?city=Liege&account=unlinked&email=without');

        self::assertResponseIsSuccessful();
        $this->assertResponseContains('Marc Simon');
        $this->assertResponseDoesNotContain('Claire Dupuis');
        $this->assertResponseContains('Ville : Liege');
        $this->assertResponseContains('Sans compte');
        $this->assertResponseContains('Sans email');
    }

    public function testLeadListSupportsCombinedFiltersAndClearEmptyState(): void
    {
        $leadOne = (new Lead())
            ->setName('Projet Atlas')
            ->setCity('Namur')
            ->setType('Habitation')
            ->setStatus(Lead::STATUS_PROPOSAL)
            ->setOtherBank('Banque Horizon');

        $leadTwo = (new Lead())
            ->setName('Projet Boreal')
            ->setCity('Bruxelles')
            ->setType('Investissement')
            ->setStatus(Lead::STATUS_LOST);

        $this->entityManager->persist($leadOne);
        $this->entityManager->persist($leadTwo);
        $this->entityManager->flush();

        $this->client->request('GET', '/leads?q=Horizon&city=Namur&type=Habitation&status='.Lead::STATUS_PROPOSAL);

        self::assertResponseIsSuccessful();
        $this->assertResponseContains('Projet Atlas');
        $this->assertResponseDoesNotContain('Projet Boreal');
        $this->assertResponseContains('Statut : Proposition');

        $this->client->request('GET', '/leads?q=absent&type=Habitation&status='.Lead::STATUS_PROPOSAL);

        self::assertResponseIsSuccessful();
        $this->assertResponseContains('Aucun lead ne correspond a la recherche ou aux filtres actifs.');
    }

    private function createInternalUser(): User
    {
        $user = (new User())
            ->setUsername('crm-lists')
            ->setEmail('crm-lists@example.test')
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
        self::assertStringContainsString($text, (string) $this->client->getResponse()->getContent());
    }

    private function assertResponseDoesNotContain(string $text): void
    {
        self::assertStringNotContainsString($text, (string) $this->client->getResponse()->getContent());
    }
}
