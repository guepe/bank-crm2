<?php

namespace App\Entity;

use App\Repository\BankRelationshipRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BankRelationshipRepository::class)]
#[ORM\Table(name: 'bank_relationship')]
class BankRelationship
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Contact::class, inversedBy: 'bankRelationships')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Contact $contact;

    #[Assert\NotBlank]
    #[ORM\Column(type: Types::STRING, length: 150)]
    private string $bankName = '';

    #[ORM\Column(type: Types::STRING, length: 150, nullable: true)]
    private ?string $bankContactName = null;

    #[Assert\Email]
    #[ORM\Column(type: Types::STRING, length: 180, nullable: true)]
    private ?string $bankContactEmail = null;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
    private ?string $bankContactPhone = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    /** @var Collection<int, BankAccessLink> */
    #[ORM\OneToMany(mappedBy: 'bankRelationship', targetEntity: BankAccessLink::class, cascade: ['persist', 'remove'])]
    private Collection $accessLinks;

    public function __construct(Contact $contact)
    {
        $this->contact = $contact;
        $this->createdAt = new \DateTimeImmutable();
        $this->accessLinks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContact(): Contact
    {
        return $this->contact;
    }

    public function getBankName(): string
    {
        return $this->bankName;
    }

    public function setBankName(?string $bankName): self
    {
        $this->bankName = trim((string) $bankName);

        return $this;
    }

    public function getBankContactName(): ?string
    {
        return $this->bankContactName;
    }

    public function setBankContactName(?string $bankContactName): self
    {
        $this->bankContactName = $bankContactName !== null ? trim($bankContactName) : null;

        return $this;
    }

    public function getBankContactEmail(): ?string
    {
        return $this->bankContactEmail;
    }

    public function setBankContactEmail(?string $bankContactEmail): self
    {
        $this->bankContactEmail = $bankContactEmail !== null ? mb_strtolower(trim($bankContactEmail)) : null;

        return $this;
    }

    public function getBankContactPhone(): ?string
    {
        return $this->bankContactPhone;
    }

    public function setBankContactPhone(?string $bankContactPhone): self
    {
        $this->bankContactPhone = $bankContactPhone;

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

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /** @return Collection<int, BankAccessLink> */
    public function getAccessLinks(): Collection
    {
        return $this->accessLinks;
    }
}
