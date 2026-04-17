<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'discr', type: 'string')]
#[ORM\DiscriminatorMap([
    'bankproduct' => BankProduct::class,
    'creditproduct' => CreditProduct::class,
    'fiscalproduct' => FiscalProduct::class,
    'savingsproduct' => SavingsProduct::class,
])]
#[ORM\Table(name: 'metaproduct')]
abstract class MetaProduct
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    protected ?int $id = null;

    #[ORM\Column(name: 'number', type: Types::STRING, length: 100, nullable: true)]
    protected ?string $number = null;

    #[ORM\Column(name: 'type', type: Types::STRING, length: 100, nullable: true)]
    protected ?string $type = null;

    #[ORM\Column(name: 'notes', type: Types::TEXT, nullable: true)]
    protected ?string $notes = null;

    #[ORM\Column(name: 'description', type: Types::TEXT, nullable: true)]
    protected ?string $description = null;

    #[ORM\Column(name: 'reference', type: Types::STRING, length: 100, nullable: true)]
    protected ?string $references = null;

    #[ORM\Column(name: 'company', type: Types::TEXT, nullable: true)]
    protected ?string $company = null;

    #[ORM\Column(name: 'taux_interet', type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    protected ?string $tauxInteret = null;

    /** @var Collection<int, Category> */
    #[ORM\ManyToMany(targetEntity: Category::class)]
    private Collection $categories;

    /** @var Collection<int, Account> */
    #[ORM\ManyToMany(targetEntity: Account::class, mappedBy: 'products')]
    private Collection $accounts;

    /** @var Collection<int, Document> */
    #[ORM\ManyToMany(targetEntity: Document::class, inversedBy: 'products')]
    #[ORM\JoinTable(name: 'metaproduct_document')]
    private Collection $documents;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->accounts = new ArrayCollection();
        $this->documents = new ArrayCollection();
    }

    public function __toString(): string
    {
        return sprintf('%s%s', static::classShortName(), $this->number ? ' '.$this->number : '');
    }

    protected static function classShortName(): string
    {
        $parts = explode('\\', static::class);

        return end($parts) ?: 'Product';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(?string $number): self
    {
        $this->number = $number;

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

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getReferences(): ?string
    {
        return $this->references;
    }

    public function setReferences(?string $references): self
    {
        $this->references = $references;

        return $this;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(?string $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getTauxInteret(): ?string
    {
        return $this->tauxInteret;
    }

    public function setTauxInteret(?string $tauxInteret): self
    {
        $this->tauxInteret = $tauxInteret;

        return $this;
    }

    /** @return Collection<int, Category> */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    /** @return Collection<int, Account> */
    public function getAccounts(): Collection
    {
        return $this->accounts;
    }

    /** @return Collection<int, Document> */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        $this->categories->removeElement($category);

        return $this;
    }

    public function addAccount(Account $account): self
    {
        if (!$this->accounts->contains($account)) {
            $this->accounts->add($account);
        }

        return $this;
    }

    public function removeAccount(Account $account): self
    {
        $this->accounts->removeElement($account);

        return $this;
    }

    public function clearAccounts(): self
    {
        foreach ($this->accounts->toArray() as $account) {
            $this->removeAccount($account);
        }

        return $this;
    }

    public function addDocument(Document $document): self
    {
        if (!$this->documents->contains($document)) {
            $this->documents->add($document);
            $document->addProduct($this);
        }

        return $this;
    }

    public function removeDocument(Document $document): self
    {
        if ($this->documents->removeElement($document)) {
            $document->removeProduct($this);
        }

        return $this;
    }
}
