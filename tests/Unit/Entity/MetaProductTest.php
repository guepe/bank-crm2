<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Account;
use App\Entity\BankProduct;
use App\Entity\Category;
use App\Entity\CreditProduct;
use App\Entity\Document;
use App\Entity\FiscalProduct;
use App\Entity\MetaProduct;
use App\Entity\SavingsProduct;
use PHPUnit\Framework\Attributes\DataProvider;

class MetaProductTest extends EntityTestCase
{
    #[DataProvider('productProvider')]
    public function testStringRepresentationAndCommonFields(MetaProduct $product, string $expectedLabel): void
    {
        $category = (new Category())->setName('Retail');
        $account = new Account();
        $document = (new Document())->setName('Contrat');

        $product
            ->setNumber('ACC-001')
            ->setType('standard')
            ->setNotes('notes')
            ->setDescription('description')
            ->setReferences('REF')
            ->setCompany('Acme')
            ->setTauxInteret('2.50')
            ->addCategory($category)
            ->addAccount($account)
            ->addDocument($document);

        self::assertSame($expectedLabel.' ACC-001', (string) $product);
        self::assertSame('ACC-001', $product->getNumber());
        self::assertSame('standard', $product->getType());
        self::assertSame('notes', $product->getNotes());
        self::assertSame('description', $product->getDescription());
        self::assertSame('REF', $product->getReferences());
        self::assertSame('Acme', $product->getCompany());
        self::assertSame('2.50', $product->getTauxInteret());
        self::assertTrue($product->getCategories()->contains($category));
        self::assertTrue($product->getAccounts()->contains($account));
        self::assertTrue($product->getDocuments()->contains($document));
        self::assertTrue($document->getProducts()->contains($product));

        $product->clearAccounts()->removeCategory($category)->removeDocument($document);

        self::assertFalse($product->getAccounts()->contains($account));
        self::assertFalse($product->getCategories()->contains($category));
        self::assertFalse($product->getDocuments()->contains($document));
    }

    public static function productProvider(): iterable
    {
        yield 'bank' => [new BankProduct(), 'BankProduct'];
        yield 'credit' => [new CreditProduct(), 'CreditProduct'];
        yield 'fiscal' => [new FiscalProduct(), 'FiscalProduct'];
        yield 'savings' => [new SavingsProduct(), 'SavingsProduct'];
    }
}
