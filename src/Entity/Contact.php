<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'contact')]
class Contact
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 1, nullable: true)]
    private ?string $titre = null;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
    private ?string $firstname = null;

    #[Assert\NotBlank]
    #[ORM\Column(type: Types::STRING, length: 100)]
    private string $lastname = '';

    #[ORM\Column(name: 'street_num', type: Types::STRING, length: 100, nullable: true)]
    private ?string $streetNum = null;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
    private ?string $zip = null;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
    private ?string $country = null;

    #[Assert\Email]
    #[ORM\Column(type: Types::STRING, length: 150, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
    private ?string $phone2 = null;

    #[ORM\Column(type: Types::STRING, length: 16, nullable: true)]
    private ?string $gsm = null;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
    private ?string $birthplace = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $birthdate = null;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
    private ?string $eid = null;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
    private ?string $niss = null;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
    private ?string $profession = null;

    #[ORM\Column(name: 'marital_status', type: Types::INTEGER, nullable: true)]
    private ?int $maritalStatus = null;

    #[ORM\Column(name: 'income_amount', type: Types::INTEGER, nullable: true)]
    private ?int $incomeAmount = null;

    #[ORM\Column(name: 'income_recurence', type: Types::STRING, length: 100, nullable: true)]
    private ?string $incomeRecurence = null;

    #[ORM\Column(name: 'income_date', type: Types::STRING, nullable: true)]
    private ?string $incomeDate = null;

    #[ORM\Column(name: 'charged_people', type: Types::INTEGER, nullable: true)]
    private ?int $chargedPeople = null;

    /** @var Collection<int, Account> */
    #[ORM\ManyToMany(targetEntity: Account::class, mappedBy: 'contacts')]
    private Collection $accounts;

    /** @var Collection<int, Document> */
    #[ORM\ManyToMany(targetEntity: Document::class, inversedBy: 'contacts')]
    #[ORM\JoinTable(name: 'contacts_document')]
    private Collection $documents;

    /** @var Collection<int, BankRelationship> */
    #[ORM\OneToMany(mappedBy: 'contact', targetEntity: BankRelationship::class, cascade: ['persist', 'remove'])]
    private Collection $bankRelationships;

    #[ORM\OneToOne(mappedBy: 'contact', targetEntity: User::class)]
    private ?User $userAccount = null;

    public function __construct()
    {
        $this->accounts = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->bankRelationships = new ArrayCollection();
    }

    public function __toString(): string
    {
        return trim(($this->firstname ? $this->firstname.' ' : '').$this->lastname);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(?string $titre): self
    {
        $this->titre = $titre;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): self
    {
        $this->lastname = trim((string) $lastname);

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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email !== null ? mb_strtolower(trim($email)) : null;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getPhone2(): ?string
    {
        return $this->phone2;
    }

    public function setPhone2(?string $phone2): self
    {
        $this->phone2 = $phone2;

        return $this;
    }

    public function getGsm(): ?string
    {
        return $this->gsm;
    }

    public function setGsm(?string $gsm): self
    {
        $this->gsm = $gsm;

        return $this;
    }

    public function getBirthplace(): ?string
    {
        return $this->birthplace;
    }

    public function setBirthplace(?string $birthplace): self
    {
        $this->birthplace = $birthplace;

        return $this;
    }

    public function getBirthdate(): ?\DateTimeInterface
    {
        return $this->birthdate;
    }

    public function setBirthdate(?\DateTimeInterface $birthdate): self
    {
        $this->birthdate = $birthdate;

        return $this;
    }

    public function getEid(): ?string
    {
        return $this->eid;
    }

    public function setEid(?string $eid): self
    {
        $this->eid = $eid;

        return $this;
    }

    public function getNiss(): ?string
    {
        return $this->niss;
    }

    public function setNiss(?string $niss): self
    {
        $this->niss = $niss;

        return $this;
    }

    public function getProfession(): ?string
    {
        return $this->profession;
    }

    public function setProfession(?string $profession): self
    {
        $this->profession = $profession;

        return $this;
    }

    public function getMaritalStatus(): ?int
    {
        return $this->maritalStatus;
    }

    public function setMaritalStatus(?int $maritalStatus): self
    {
        $this->maritalStatus = $maritalStatus;

        return $this;
    }

    public function getIncomeAmount(): ?int
    {
        return $this->incomeAmount;
    }

    public function setIncomeAmount(?int $incomeAmount): self
    {
        $this->incomeAmount = $incomeAmount;

        return $this;
    }

    public function getIncomeRecurence(): ?string
    {
        return $this->incomeRecurence;
    }

    public function setIncomeRecurence(?string $incomeRecurence): self
    {
        $this->incomeRecurence = $incomeRecurence;

        return $this;
    }

    public function getIncomeDate(): ?string
    {
        return $this->incomeDate;
    }

    public function setIncomeDate(?string $incomeDate): self
    {
        $this->incomeDate = $incomeDate;

        return $this;
    }

    public function getChargedPeople(): ?int
    {
        return $this->chargedPeople;
    }

    public function setChargedPeople(?int $chargedPeople): self
    {
        $this->chargedPeople = $chargedPeople;

        return $this;
    }

    /** @return Collection<int, Account> */
    public function getAccounts(): Collection
    {
        return $this->accounts;
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

    /** @return Collection<int, Document> */
    public function getDocuments(): Collection
    {
        return $this->documents;
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

    /** @return Collection<int, BankRelationship> */
    public function getBankRelationships(): Collection
    {
        return $this->bankRelationships;
    }

    public function addBankRelationship(BankRelationship $bankRelationship): self
    {
        if (!$this->bankRelationships->contains($bankRelationship)) {
            $this->bankRelationships->add($bankRelationship);
        }

        return $this;
    }

    public function getUserAccount(): ?User
    {
        return $this->userAccount;
    }

    public function setUserAccount(?User $userAccount): self
    {
        $this->userAccount = $userAccount;

        if ($userAccount instanceof User && $userAccount->getContact() !== $this) {
            $userAccount->setContact($this);
        }

        return $this;
    }
}
