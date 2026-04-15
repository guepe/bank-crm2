<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class CreditProduct extends MetaProduct
{
    #[ORM\Column(name: 'garantee', type: Types::TEXT, nullable: true)]
    private ?string $garantee = null;

    #[ORM\Column(name: 'purpose', type: Types::TEXT, nullable: true)]
    private ?string $purpose = null;

    #[ORM\Column(name: 'variability', type: Types::STRING, length: 100, nullable: true)]
    private ?string $variability = null;

    #[ORM\Column(name: 'start_date', type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(name: 'end_date', type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(name: 'duration', type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $duration = null;

    #[ORM\Column(name: 'recurrent_prime_amount', type: Types::DECIMAL, precision: 10, scale: 4, nullable: true)]
    private ?string $recurrentPrimeAmount = null;

    #[ORM\Column(name: 'amount', type: Types::DECIMAL, precision: 10, scale: 4, nullable: true)]
    private ?string $amount = null;

    #[ORM\Column(name: 'payment_date', type: Types::STRING, length: 100, nullable: true)]
    private ?string $paymentDate = null;

    public function getGarantee(): ?string
    {
        return $this->garantee;
    }

    public function setGarantee(?string $garantee): self
    {
        $this->garantee = $garantee;

        return $this;
    }

    public function getPurpose(): ?string
    {
        return $this->purpose;
    }

    public function setPurpose(?string $purpose): self
    {
        $this->purpose = $purpose;

        return $this;
    }

    public function getVariability(): ?string
    {
        return $this->variability;
    }

    public function setVariability(?string $variability): self
    {
        $this->variability = $variability;

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

    public function getDuration(): ?string
    {
        return $this->duration;
    }

    public function setDuration(?string $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getRecurrentPrimeAmount(): ?string
    {
        return $this->recurrentPrimeAmount;
    }

    public function setRecurrentPrimeAmount(?string $recurrentPrimeAmount): self
    {
        $this->recurrentPrimeAmount = $recurrentPrimeAmount;

        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(?string $amount): self
    {
        $this->amount = $amount;

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
}
