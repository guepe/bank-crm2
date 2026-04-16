<?php

namespace App\Tests\Unit\Entity;

use App\Entity\BankProduct;
use App\Entity\CreditProduct;
use App\Entity\FiscalProduct;
use App\Entity\SavingsProduct;

class ProductVariantsTest extends EntityTestCase
{
    public function testBankProductAmountCanBeAssigned(): void
    {
        $product = (new BankProduct())->setAmount('1500.2500');

        self::assertSame('1500.2500', $product->getAmount());
    }

    public function testCreditProductFieldsCanBeAssigned(): void
    {
        $startDate = new \DateTimeImmutable('2026-01-01');
        $endDate = new \DateTimeImmutable('2027-01-01');
        $product = (new CreditProduct())
            ->setGarantee('House')
            ->setPurpose('Mortgage')
            ->setVariability('fixed')
            ->setStartDate($startDate)
            ->setEndDate($endDate)
            ->setDuration('12.00')
            ->setRecurrentPrimeAmount('120.5000')
            ->setAmount('50000.0000')
            ->setPaymentDate('05');

        self::assertSame('House', $product->getGarantee());
        self::assertSame('Mortgage', $product->getPurpose());
        self::assertSame('fixed', $product->getVariability());
        self::assertSame($startDate, $product->getStartDate());
        self::assertSame($endDate, $product->getEndDate());
        self::assertSame('12.00', $product->getDuration());
        self::assertSame('120.5000', $product->getRecurrentPrimeAmount());
        self::assertSame('50000.0000', $product->getAmount());
        self::assertSame('05', $product->getPaymentDate());
    }

    public function testFiscalProductFieldsCanBeAssigned(): void
    {
        $reserveDate = new \DateTimeImmutable('2026-04-16');
        $startDate = new \DateTimeImmutable('2026-01-01');
        $endDate = new \DateTimeImmutable('2026-12-31');
        $product = (new FiscalProduct())
            ->setRecurrentPrimeAmount('99.9900')
            ->setCapitalTerme('1000.0000')
            ->setGarantee('Protected')
            ->setPaymentDate('10')
            ->setPaymentDeadline('31-12')
            ->setReserve('300.0000')
            ->setReserveDate($reserveDate)
            ->setStartDate($startDate)
            ->setEndDate($endDate);

        self::assertSame('99.9900', $product->getRecurrentPrimeAmount());
        self::assertSame('1000.0000', $product->getCapitalTerme());
        self::assertSame('Protected', $product->getGarantee());
        self::assertSame('10', $product->getPaymentDate());
        self::assertSame('31-12', $product->getPaymentDeadline());
        self::assertSame('300.0000', $product->getReserve());
        self::assertSame($reserveDate, $product->getReserveDate());
        self::assertSame($startDate, $product->getStartDate());
        self::assertSame($endDate, $product->getEndDate());
    }

    public function testSavingsProductFieldsCanBeAssigned(): void
    {
        $reserveDate = new \DateTimeImmutable('2026-04-16');
        $startDate = new \DateTimeImmutable('2026-01-01');
        $endDate = new \DateTimeImmutable('2026-12-31');
        $product = (new SavingsProduct())
            ->setPrimeRecurence('monthly')
            ->setRecurrentPrimeAmount('49.9900')
            ->setAmount('5000.0000')
            ->setDuration('24.00')
            ->setCapitalTerme('5400.0000')
            ->setGarantee('Protected')
            ->setPaymentDate('15')
            ->setPaymentDeadline('30-12')
            ->setReserve('200.0000')
            ->setReserveDate($reserveDate)
            ->setStartDate($startDate)
            ->setEndDate($endDate);

        self::assertSame('monthly', $product->getPrimeRecurence());
        self::assertSame('49.9900', $product->getRecurrentPrimeAmount());
        self::assertSame('5000.0000', $product->getAmount());
        self::assertSame('24.00', $product->getDuration());
        self::assertSame('5400.0000', $product->getCapitalTerme());
        self::assertSame('Protected', $product->getGarantee());
        self::assertSame('15', $product->getPaymentDate());
        self::assertSame('30-12', $product->getPaymentDeadline());
        self::assertSame('200.0000', $product->getReserve());
        self::assertSame($reserveDate, $product->getReserveDate());
        self::assertSame($startDate, $product->getStartDate());
        self::assertSame($endDate, $product->getEndDate());
    }
}
