<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Account;
use App\Entity\BankProduct;
use App\Entity\Contact;
use App\Entity\CreditProduct;
use App\Entity\Document;
use App\Entity\FiscalProduct;
use App\Entity\SavingsProduct;

class AccountTest extends EntityTestCase
{
    public function testNameIsTrimmedAndContactsStayUnique(): void
    {
        $account = (new Account())->setName('  ACME Corp  ');
        $contact = new Contact();

        $account->addContact($contact)->addContact($contact);

        self::assertSame('ACME Corp', $account->getName());
        self::assertCount(1, $account->getContacts());
        self::assertTrue($account->getContacts()->contains($contact));
    }

    public function testDocumentsCanBeCleared(): void
    {
        $account = new Account();
        $firstDocument = new Document();
        $secondDocument = new Document();

        $account->addDocument($firstDocument)->addDocument($secondDocument);
        $account->clearDocuments();

        self::assertCount(0, $account->getDocuments());
    }

    public function testProductsAreGroupedByConcreteType(): void
    {
        $account = new Account();
        $bankProduct = new BankProduct();
        $creditProduct = new CreditProduct();
        $fiscalProduct = new FiscalProduct();
        $savingsProduct = new SavingsProduct();

        $account
            ->addProduct($bankProduct)
            ->addProduct($creditProduct)
            ->addProduct($fiscalProduct)
            ->addProduct($savingsProduct);

        self::assertSame([$bankProduct], $account->getBankProducts());
        self::assertSame([$creditProduct], $account->getCreditProducts());
        self::assertSame([$fiscalProduct], $account->getFiscalProducts());
        self::assertSame([$savingsProduct], $account->getSavingsProducts());
    }
}
