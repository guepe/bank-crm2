<?php

namespace App\Tests\Integration;

use App\Entity\Account;
use App\Entity\BankProduct;
use App\Entity\Contact;
use App\Entity\Document;
use App\Entity\Lead;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CrmOperationsFlowTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private Filesystem $filesystem;
    private string $uploadDir;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->filesystem = new Filesystem();
        $this->uploadDir = static::getContainer()->getParameter('document_upload_dir');

        $this->resetDatabase();
        $this->filesystem->remove($this->uploadDir);
    }

    public function testLeadCanBeCreatedAndReviewed(): void
    {
        $this->client->loginUser($this->createUser('lead-user', ['ROLE_USER']));
        $this->client->request('GET', '/leads/new');

        $this->client->submitForm('Enregistrer', [
            'lead[name]' => 'Prospect Orion',
            'lead[city]' => 'Namur',
            'lead[type]' => 'Potentiel',
            'lead[otherBank]' => 'Banque Regionale',
            'lead[notes]' => 'A relancer en priorite',
        ]);

        self::assertResponseRedirects();
        $this->client->followRedirect();

        self::assertSelectorTextContains('h1', 'Prospect Orion');
        $this->assertResponseContains('Banque Regionale');
        $this->assertResponseContains('A relancer en priorite');
        self::assertSame(1, $this->entityManager->getRepository(Lead::class)->count([]));
    }

    public function testDocumentCanBeUploadedLinkedToAnAccountAndDownloaded(): void
    {
        $this->client->loginUser($this->createUser('document-user', ['ROLE_USER']));

        $account = (new Account())
            ->setName('Compte Documents')
            ->setCity('Bruxelles');
        $this->entityManager->persist($account);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', sprintf('/documents/new?account=%d', $account->getId()));

        $tempFile = tempnam(sys_get_temp_dir(), 'crm-doc-');
        file_put_contents($tempFile, "%PDF-1.4\nTest document content");

        $uploadedFile = new UploadedFile(
            $tempFile,
            'resume-client.pdf',
            'application/pdf',
            null,
            true
        );

        $form = $crawler->selectButton('Enregistrer')->form([
            'document[name]' => 'Resume client',
        ]);
        $form['document[uploadedFile]']->upload($uploadedFile);

        $this->client->submit($form);

        self::assertResponseRedirects(sprintf('/accounts/%d', $account->getId()));
        $this->client->followRedirect();

        $this->entityManager->clear();
        /** @var Document $document */
        $document = $this->entityManager->getRepository(Document::class)->findOneBy(['name' => 'Resume client']);

        self::assertNotNull($document);
        self::assertNotNull($document->getPath());
        self::assertSame('application/octet-stream', $document->getMimeType());
        self::assertFileExists($this->uploadDir.'/'.$document->getPath());
        $this->assertResponseContains('Resume client');

        $this->client->request('GET', sprintf('/documents/%d/download', $document->getId()));

        self::assertResponseIsSuccessful();
        self::assertSame('application/pdf', $this->client->getResponse()->headers->get('content-type'));
        self::assertStringContainsString(
            'attachment; filename="Resume client"',
            (string) $this->client->getResponse()->headers->get('content-disposition')
        );
    }

    public function testBankProductCanBeCreatedFromAccountContext(): void
    {
        $this->client->loginUser($this->createUser('product-user', ['ROLE_USER']));

        $account = (new Account())
            ->setName('Compte Produits')
            ->setCity('Liege');
        $this->entityManager->persist($account);
        $this->entityManager->flush();

        $this->client->request('GET', sprintf('/products/bank/new?account=%d', $account->getId()));
        $this->assertResponseContains('Creation depuis le compte');
        $this->assertResponseContains('Compte Produits');

        $this->client->submitForm('Enregistrer', [
            'bank_product[number]' => 'BE-ACC-001',
            'bank_product[type]' => 'Compte courant',
            'bank_product[amount]' => '1250.50',
            'bank_product[notes]' => 'Produit principal',
        ]);

        self::assertResponseRedirects(sprintf('/accounts/%d', $account->getId()));
        $this->client->followRedirect();

        $this->entityManager->clear();
        /** @var Account $reloadedAccount */
        $reloadedAccount = $this->entityManager->getRepository(Account::class)->find($account->getId());

        self::assertCount(1, $reloadedAccount->getBankProducts());
        $this->assertResponseContains('BE-ACC-001');
        $this->assertResponseContains('Ajouter un produit bancaire');

        /** @var BankProduct $product */
        $product = $reloadedAccount->getBankProducts()[0];

        $this->client->request('GET', sprintf('/products/bank/%d', $product->getId()));

        self::assertResponseIsSuccessful();
        $this->assertResponseContains('Compte Produits');
        $this->assertResponseContains('Produit principal');
    }

    public function testAdministratorCanCreateAClientUserLinkedToAContact(): void
    {
        $contact = (new Contact())
            ->setFirstname('Claire')
            ->setLastname('Durand')
            ->setEmail('claire.durand@example.test');
        $this->entityManager->persist($contact);
        $this->entityManager->flush();

        $admin = $this->createUser('admin-user', ['ROLE_ADMIN'], 'admin@example.test');
        $this->client->loginUser($admin);

        $this->client->request('GET', sprintf('/users/new?contact=%d', $contact->getId()));

        self::assertResponseIsSuccessful();
        $this->assertResponseContains('Nouvel utilisateur');

        $this->client->submitForm('Enregistrer', [
            'user[username]' => 'claire.portal',
            'user[email]' => 'claire.durand@example.test',
            'user[enabled]' => '1',
            'user[contact]' => (string) $contact->getId(),
            'user[plainPassword][first]' => 'Motdepasse123!',
            'user[plainPassword][second]' => 'Motdepasse123!',
        ]);

        self::assertResponseRedirects();
        $this->client->followRedirect();

        /** @var User $createdUser */
        $createdUser = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'claire.portal']);

        self::assertNotNull($createdUser);
        self::assertTrue($createdUser->isClientUser());
        self::assertNotSame('Motdepasse123!', $createdUser->getPassword());
        self::assertSame($contact->getId(), $createdUser->getContact()?->getId());
        $this->assertResponseContains('Claire Durand');
        $this->assertResponseContains('Client portail');
    }

    public function testNonAdminCannotAccessUserManagement(): void
    {
        $this->client->loginUser($this->createUser('standard-user', ['ROLE_USER']));
        $this->client->request('GET', '/users');

        self::assertResponseStatusCodeSame(403);
    }

    protected function tearDown(): void
    {
        $this->filesystem->remove($this->uploadDir);
        parent::tearDown();
    }

    private function createUser(string $username, array $roles, ?string $email = null): User
    {
        $user = (new User())
            ->setUsername($username)
            ->setEmail($email ?? $username.'@example.test')
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
