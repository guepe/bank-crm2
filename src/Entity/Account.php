<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'account')]
class Account
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[Assert\NotBlank]
    #[ORM\Column(type: Types::STRING, length: 100)]
    private string $name = '';

    #[ORM\Column(name: 'street_num', type: Types::STRING, length: 100, nullable: true)]
    private ?string $streetNum = null;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
    private ?string $zip = null;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
    private ?string $country = null;

    #[ORM\Column(name: 'company_statut', type: Types::STRING, length: 100, nullable: true)]
    private ?string $companyStatut = null;

    #[ORM\Column(name: 'other_bank', type: Types::STRING, length: 100, nullable: true)]
    private ?string $otherBank = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
    private ?string $type = null;

    #[ORM\Column(name: 'starting_date', type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $startingDate = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    /** @var Collection<int, Contact> */
    #[ORM\ManyToMany(targetEntity: Contact::class, inversedBy: 'accounts')]
    #[ORM\JoinTable(name: 'accounts_contacts')]
    private Collection $contacts;

    /** @var Collection<int, Document> */
    #[ORM\ManyToMany(targetEntity: Document::class, inversedBy: 'accounts')]
    #[ORM\JoinTable(name: 'accounts_document')]
    private Collection $documents;

    /** @var Collection<int, MetaProduct> */
    #[ORM\ManyToMany(targetEntity: MetaProduct::class, inversedBy: 'accounts')]
    private Collection $products;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->contacts = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->products = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = trim((string) $name);

        return $this;
    }

    public function getStreetNum(): ?string
    {
        return $this->streetNum;
    }

    public function setStreetNum(?string $streetNum): self
    {
        $this->streetNum = $streetNum;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getZip(): ?string
    {
        return $this->zip;
    }

    public function setZip(?string $zip): self
    {
        $this->zip = $zip;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getCompanyStatut(): ?string
    {
        return $this->companyStatut;
    }

    public function setCompanyStatut(?string $companyStatut): self
    {
        $this->companyStatut = $companyStatut;

        return $this;
    }

    public function getOtherBank(): ?string
    {
        return $this->otherBank;
    }

    public function setOtherBank(?string $otherBank): self
    {
        $this->otherBank = $otherBank;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getStartingDate(): ?\DateTimeInterface
    {
        return $this->startingDate;
    }

    public function setStartingDate(?\DateTimeInterface $startingDate): self
    {
        $this->startingDate = $startingDate;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /** @return Collection<int, Contact> */
    public function getContacts(): Collection
    {
        return $this->contacts;
    }

    public function addContact(Contact $contact): self
    {
        if (!$this->contacts->contains($contact)) {
            $this->contacts->add($contact);
            $contact->addAccount($this);
        }

        return $this;
    }

    public function removeContact(Contact $contact): self
    {
        if ($this->contacts->removeElement($contact)) {
            $contact->removeAccount($this);
        }

        return $this;
    }

    /** @return Collection<int, Document> */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    /** @return Collection<int, MetaProduct> */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addDocument(Document $document): self
    {
        if (!$this->documents->contains($document)) {
            $this->documents->add($document);
        }

        return $this;
    }

    public function removeDocument(Document $document): self
    {
        $this->documents->removeElement($document);

        return $this;
    }

    public function clearDocuments(): self
    {
        foreach ($this->documents->toArray() as $document) {
            $this->removeDocument($document);
        }

        return $this;
    }

    public function addProduct(MetaProduct $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            $product->addAccount($this);
        }

        return $this;
    }

    public function removeProduct(MetaProduct $product): self
    {
        if ($this->products->removeElement($product)) {
            $product->removeAccount($this);
        }

        return $this;
    }

    /** @return list<BankProduct> */
    public function getBankProducts(): array
    {
        return array_values(array_filter(
            $this->products->toArray(),
            static fn (MetaProduct $product): bool => $product instanceof BankProduct
        ));
    }

    /** @return list<CreditProduct> */
    public function getCreditProducts(): array
    {
        return array_values(array_filter(
            $this->products->toArray(),
            static fn (MetaProduct $product): bool => $product instanceof CreditProduct
        ));
    }

    /** @return list<FiscalProduct> */
    public function getFiscalProducts(): array
    {
        return array_values(array_filter(
            $this->products->toArray(),
            static fn (MetaProduct $product): bool => $product instanceof FiscalProduct
        ));
    }

    /** @return list<SavingsProduct> */
    public function getSavingsProducts(): array
    {
        return array_values(array_filter(
            $this->products->toArray(),
            static fn (MetaProduct $product): bool => $product instanceof SavingsProduct
        ));
    }

    public function clearProducts(): self
    {
        foreach ($this->products->toArray() as $product) {
            $this->removeProduct($product);
        }

        return $this;
    }
}
