<?php

namespace App\Tests\Integration;

use App\Entity\Account;
use App\Entity\BankAccessLink;
use App\Entity\BankProduct;
use App\Entity\BankRelationship;
use App\Entity\Contact;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BankRelationshipFlowTest extends WebTestCase
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

    public function testAdvisorCanCreateBankRelationshipAndSendDossier(): void
    {
        $contact = (new Contact())
            ->setFirstname('Emma')
            ->setLastname('Dubois')
            ->setEmail('emma@example.test');
        $account = (new Account())
            ->setName('Compte Emma')
            ->setType('Core')
            ->addContact($contact);

        $this->entityManager->persist($contact);
        $this->entityManager->persist($account);
        $this->entityManager->flush();

        $this->client->loginUser($this->createUser('advisor-bank', ['ROLE_USER']));
        $this->client->request('GET', sprintf('/contacts/%d/banks/new', $contact->getId()));
        $this->client->submitForm('Enregistrer', [
            'bank_relationship[bankName]' => 'Banque Delta',
            'bank_relationship[bankContactName]' => 'Sophie Martin',
            'bank_relationship[bankContactEmail]' => 'sophie.martin@banque.test',
            'bank_relationship[bankContactPhone]' => '0123456789',
        ]);

        self::assertResponseRedirects(sprintf('/contacts/%d', $contact->getId()));
        $this->client->followRedirect();
        $this->assertResponseContains('Banque Delta');
        $this->assertResponseContains('Envoyer le dossier a la banque');

        /** @var BankRelationship $relationship */
        $relationship = $this->entityManager->getRepository(BankRelationship::class)->findOneBy(['bankName' => 'Banque Delta']);
        self::assertNotNull($relationship);

        $this->client->submitForm('Envoyer le dossier a la banque');
        self::assertResponseRedirects(sprintf('/contacts/%d', $contact->getId()));
        $this->entityManager->clear();

        /** @var BankAccessLink $accessLink */
        $accessLink = $this->entityManager->getRepository(BankAccessLink::class)->findOneBy([]);
        self::assertNotNull($accessLink);
        self::assertSame('Banque Delta', $accessLink->getSummarySnapshot()['bank']['name']);
    }

    public function testBankContactCanSubmitProductForClient(): void
    {
        $contact = (new Contact())
            ->setFirstname('Paul')
            ->setLastname('Henry')
            ->setEmail('paul@example.test');
        $account = (new Account())
            ->setName('Compte Paul')
            ->setType('Core')
            ->addContact($contact);
        $relationship = (new BankRelationship($contact))
            ->setBankName('Banque Atlas')
            ->setBankContactName('Luc Bernard')
            ->setBankContactEmail('luc@atlas.test');

        $accessLink = (new BankAccessLink($relationship))
            ->setToken('bank-token-test')
            ->setSummarySnapshot([
                'contact' => ['full_name' => 'Paul Henry'],
                'bank' => ['name' => 'Banque Atlas'],
                'accounts' => [['name' => 'Compte Paul', 'type' => 'Core', 'city' => null]],
                'documents' => [],
            ])
            ->markSent();

        $contact->addBankRelationship($relationship);
        $this->entityManager->persist($contact);
        $this->entityManager->persist($account);
        $this->entityManager->persist($relationship);
        $this->entityManager->persist($accessLink);
        $this->entityManager->flush();

        $this->client->request('GET', '/bank-access/bank-token-test');
        self::assertResponseIsSuccessful();
        $this->assertResponseContains('Completer les produits du client');
        $this->assertResponseContains('Compte Paul');

        $this->client->submitForm('Ajouter le produit', [
            'bank_disclosure_submission[account]' => (string) $account->getId(),
            'bank_disclosure_submission[number]' => 'BNK-4455',
            'bank_disclosure_submission[type]' => 'Compte epargne',
            'bank_disclosure_submission[amount]' => '5400.00',
            'bank_disclosure_submission[notes]' => 'Produit confirme par la banque',
        ]);

        self::assertResponseRedirects('/bank-access/bank-token-test');
        $this->entityManager->clear();

        /** @var BankProduct $product */
        $product = $this->entityManager->getRepository(BankProduct::class)->findOneBy(['number' => 'BNK-4455']);
        self::assertNotNull($product);
        self::assertSame('Banque Atlas', $product->getCompany());
        self::assertStringContainsString('Produit confirme par la banque', (string) $product->getNotes());
    }

    private function createUser(string $username, array $roles): User
    {
        $user = (new User())
            ->setUsername($username)
            ->setEmail($username.'@example.test')
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
