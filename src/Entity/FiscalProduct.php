<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class FiscalProduct extends MetaProduct
{
    #[ORM\Column(name: 'recurrent_prime_amount', type: Types::DECIMAL, precision: 10, scale: 4, nullable: true)]
    private ?string $recurrentPrimeAmount = null;

    #[ORM\Column(name: 'capital_terme', type: Types::DECIMAL, precision: 10, scale: 4, nullable: true)]
    private ?string $capitalTerme = null;

    #[ORM\Column(name: 'garantee', type: Types::STRING, length: 100, nullable: true)]
    private ?string $garantee = null;

    #[ORM\Column(name: 'payment_date', type: Types::STRING, length: 100, nullable: true)]
    private ?string $paymentDate = null;

    #[ORM\Column(name: 'payment_deadline', type: Types::STRING, length: 100, nullable: true)]
    private ?string $paymentDeadline = null;

    #[ORM\Column(name: 'reserve', type: Types::DECIMAL, precision: 10, scale: 4, nullable: true)]
    private ?string $reserve = null;

    #[ORM\Column(name: 'reserve_date', type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $reserveDate = null;

    #[ORM\Column(name: 'start_date', type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(name: 'end_date', type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $endDate = null;

    public function getRecurrentPrimeAmount(): ?string
    {
        return $this->recurrentPrimeAmount;
    }

    public function setRecurrentPrimeAmount(?string $recurrentPrimeAmount): self
    {
        $this->recurrentPrimeAmount = $recurrentPrimeAmount;

        return $this;
    }

    public function getCapitalTerme(): ?string
    {
        return $this->capitalTerme;
    }

    public function setCapitalTerme(?string $capitalTerme): self
    {
        $this->capitalTerme = $capitalTerme;

        return $this;
    }

    public function getGarantee(): ?string
    {
        return $this->garantee;
    }

    public function setGarantee(?string $garantee): self
    {
        $this->garantee = $garantee;

        return $this;
    }

    public function getPaymentDate(): ?string
    {
        return $this->paymentDate;
    }

    public function setPaymentDate(?string $paymentDate): self
    {
        $this->paymentDate = $paymentDate;

        return $this;
    }

    public function getPaymentDeadline(): ?string
    {
        return $this->paymentDeadline;
    }

    public function setPaymentDeadline(?string $paymentDeadline): self
    {
        $this->paymentDeadline = $paymentDeadline;

        return $this;
    }

    public function getReserve(): ?string
    {
        return $this->reserve;
    }

    public function setReserve(?string $reserve): self
    {
        $this->reserve = $reserve;

        return $this;
    }

    public function getReserveDate(): ?\DateTimeInterface
    {
        return $this->reserveDate;
    }

    public function setReserveDate(?\DateTimeInterface $reserveDate): self
    {
        $this->reserveDate = $reserveDate;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }
}
