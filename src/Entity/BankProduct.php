<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class BankProduct extends MetaProduct
{
    #[ORM\Column(name: 'amount', type: Types::DECIMAL, precision: 10, scale: 4, nullable: true)]
    private ?string $amount = null;

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(?string $amount): self
    {
        $this->amount = $amount;

        return $this;
    }
}
