<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Lead;

class LeadTest extends EntityTestCase
{
    public function testNameIsTrimmedAndUsedAsStringRepresentation(): void
    {
        $lead = new Lead();

        $this->assertFluent($lead, $lead->setName('  Prospect Alpha  '));

        self::assertSame('Prospect Alpha', $lead->getName());
        self::assertSame('Prospect Alpha', (string) $lead);
    }

    public function testOptionalFieldsCanBeAssigned(): void
    {
        $date = new \DateTimeImmutable('2026-04-16');
        $lead = (new Lead())
            ->setStreetNum('12A')
            ->setCity('Brussels')
            ->setZip('1000')
            ->setCountry('Belgium')
            ->setCompanyStatut('SRL')
            ->setOtherBank('Other Bank')
            ->setNotes('Important lead')
            ->setType('professional')
            ->setStartingDate($date);

        self::assertSame('12A', $lead->getStreetNum());
        self::assertSame('Brussels', $lead->getCity());
        self::assertSame('1000', $lead->getZip());
        self::assertSame('Belgium', $lead->getCountry());
        self::assertSame('SRL', $lead->getCompanyStatut());
        self::assertSame('Other Bank', $lead->getOtherBank());
        self::assertSame('Important lead', $lead->getNotes());
        self::assertSame('professional', $lead->getType());
        self::assertSame($date, $lead->getStartingDate());
    }
}
