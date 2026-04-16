<?php

namespace App\Entity;

use App\Repository\BankAccessLinkRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BankAccessLinkRepository::class)]
#[ORM\Table(name: 'bank_access_link')]
#[ORM\UniqueConstraint(name: 'uniq_bank_access_link_token', columns: ['token'])]
class BankAccessLink
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: BankRelationship::class, inversedBy: 'accessLinks')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private BankRelationship $bankRelationship;

    #[ORM\ManyToOne(targetEntity: Contact::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Contact $contact;

    #[ORM\Column(type: Types::STRING, length: 128)]
    private string $token = '';

    #[ORM\Column(type: Types::JSON)]
    private array $summarySnapshot = [];

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $expiresAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $sentAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $respondedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $revokedAt = null;

    public function __construct(BankRelationship $bankRelationship)
    {
        $this->bankRelationship = $bankRelationship;
        $this->contact = $bankRelationship->getContact();
        $this->createdAt = new \DateTimeImmutable();
        $this->expiresAt = $this->createdAt->modify('+7 days');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBankRelationship(): BankRelationship
    {
        return $this->bankRelationship;
    }

    public function getContact(): Contact
    {
        return $this->contact;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getSummarySnapshot(): array
    {
        return $this->summarySnapshot;
    }

    public function setSummarySnapshot(array $summarySnapshot): self
    {
        $this->summarySnapshot = $summarySnapshot;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getExpiresAt(): \DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(\DateTimeImmutable $expiresAt): self
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function getSentAt(): ?\DateTimeImmutable
    {
        return $this->sentAt;
    }

    public function markSent(): self
    {
        $this->sentAt = new \DateTimeImmutable();

        return $this;
    }

    public function getRespondedAt(): ?\DateTimeImmutable
    {
        return $this->respondedAt;
    }

    public function markResponded(): self
    {
        $this->respondedAt = new \DateTimeImmutable();

        return $this;
    }

    public function revoke(): self
    {
        $this->revokedAt = new \DateTimeImmutable();

        return $this;
    }

    public function isActive(): bool
    {
        return $this->revokedAt === null && $this->expiresAt > new \DateTimeImmutable();
    }
}
