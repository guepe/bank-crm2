<?php

namespace App\Entity;

use App\Repository\FieldEditRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * FieldEdit - Audit trail for each field modification in OnboardingSession.
 * Tracks: who changed what, when, why, and from which source.
 */
#[ORM\Entity(repositoryClass: FieldEditRepository::class)]
#[ORM\Table(name: 'field_edit')]
#[ORM\Index(columns: ['session_id', 'created_at'], name: 'idx_session_timeline')]
#[ORM\Index(columns: ['session_id', 'field_path'], name: 'idx_session_field')]
class FieldEdit
{
    public const SOURCE_DECLARED = 'declared';
    public const SOURCE_DETECTED = 'detected';
    public const SOURCE_UPDATED = 'updated';
    public const SOURCE_CORRECTED = 'corrected';

    public const ROLE_CLIENT = 'client';
    public const ROLE_PRESCRIBER = 'prescriber';
    public const ROLE_SYSTEM = 'system';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: OnboardingSession::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private OnboardingSession $session;

    /**
     * Dot-notation field path: "client.prenom", "patrimoine.immo.0.value"
     */
    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $fieldPath;

    /**
     * Previous value (JSON-encoded) - null if creation.
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private mixed $oldValue = null;

    /**
     * New/current value (JSON-encoded).
     */
    #[ORM\Column(type: Types::JSON)]
    private mixed $newValue;

    /**
     * Source of change: declared|detected|updated|corrected
     */
    #[ORM\Column(type: Types::STRING, length: 50)]
    private string $source = self::SOURCE_DECLARED;

    /**
     * Actor role: client|prescriber|system
     */
    #[ORM\Column(type: Types::STRING, length: 50)]
    private string $role = self::ROLE_SYSTEM;

    /**
     * Who made the change (null if system).
     */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $author = null;

    /**
     * Optional reason for change (e.g., prescriber note).
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $reason = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    public function __construct(
        OnboardingSession $session,
        string $fieldPath,
        mixed $newValue,
        string $source = self::SOURCE_DECLARED,
    ) {
        $this->session = $session;
        $this->fieldPath = $fieldPath;
        $this->newValue = $newValue;
        $this->setSource($source);
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSession(): OnboardingSession
    {
        return $this->session;
    }

    public function getFieldPath(): string
    {
        return $this->fieldPath;
    }

    public function getOldValue(): mixed
    {
        return $this->oldValue;
    }

    public function setOldValue(mixed $oldValue): self
    {
        $this->oldValue = $oldValue;
        return $this;
    }

    public function getNewValue(): mixed
    {
        return $this->newValue;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function setSource(string $source): self
    {
        if (!in_array($source, self::validSources(), true)) {
            throw new \InvalidArgumentException(sprintf('Invalid source: %s', $source));
        }

        $this->source = $source;

        return $this;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        if (!in_array($role, self::validRoles(), true)) {
            throw new \InvalidArgumentException(sprintf('Invalid role: %s', $role));
        }

        $this->role = $role;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;
        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): self
    {
        $this->reason = $reason;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Export as provenance entry for UI/report.
     */
    public function toProvenanceEntry(): array
    {
        return [
            'value' => $this->newValue,
            'oldValue' => $this->oldValue,
            'source' => $this->source,
            'role' => $this->role,
            'timestamp' => $this->createdAt->format(\DateTimeInterface::ATOM),
            'author' => $this->author?->getEmail(),
            'reason' => $this->reason,
        ];
    }

    /**
     * @return list<string>
     */
    public static function validSources(): array
    {
        return [
            self::SOURCE_DECLARED,
            self::SOURCE_DETECTED,
            self::SOURCE_UPDATED,
            self::SOURCE_CORRECTED,
        ];
    }

    /**
     * @return list<string>
     */
    public static function validRoles(): array
    {
        return [
            self::ROLE_CLIENT,
            self::ROLE_PRESCRIBER,
            self::ROLE_SYSTEM,
        ];
    }
}
