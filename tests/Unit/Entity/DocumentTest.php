<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Account;
use App\Entity\Contact;
use App\Entity\Document;

class DocumentTest extends EntityTestCase
{
    public function testStringRepresentationFallsBackFromNameToPath(): void
    {
        $document = new Document();

        self::assertSame('Document', (string) $document);
        self::assertInstanceOf(\DateTimeImmutable::class, $document->getCreatedAt());

        $document->setPath('  /tmp/file.pdf  ');
        self::assertSame('/tmp/file.pdf', $document->getPath());
        self::assertSame('/tmp/file.pdf', (string) $document);

        $document->setName('  Contract  ');
        self::assertSame('Contract', $document->getName());
        self::assertSame('Contract', (string) $document);
    }

    public function testAccountRelationStaysInSync(): void
    {
        $document = new Document();
        $account = new Account();

        $document->addAccount($account);

        self::assertTrue($document->getAccounts()->contains($account));
        self::assertTrue($account->getDocuments()->contains($document));

        $document->removeAccount($account);

        self::assertFalse($document->getAccounts()->contains($account));
        self::assertFalse($account->getDocuments()->contains($document));
    }

    public function testContactRelationStaysInSync(): void
    {
        $document = new Document();
        $contact = new Contact();

        $document->addContact($contact);

        self::assertTrue($document->getContacts()->contains($contact));
        self::assertTrue($contact->getDocuments()->contains($document));

        $document->removeContact($contact);

        self::assertFalse($document->getContacts()->contains($contact));
        self::assertFalse($contact->getDocuments()->contains($document));
    }
}
