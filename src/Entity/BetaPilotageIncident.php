<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'beta_pilotage_incident')]
#[ORM\Index(columns: ['status', 'created_at'], name: 'idx_beta_incident_status')]
class BetaPilotageIncident
{
    public const CATEGORY_ABANDON = 'abandon';
    public const CATEGORY_EXTRACTION = 'extraction';
    public const CATEGORY_REPORT = 'report';
    public const CATEGORY_ACCESS = 'access';
    public const CATEGORY_FEEDBACK = 'feedback';

    public const SEVERITY_LOW = 'low';
    public const SEVERITY_MEDIUM = 'medium';
    public const SEVERITY_HIGH = 'high';

    public const STATUS_OPEN = 'open';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_RESOLVED = 'resolved';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[Assert\NotBlank]
    #[ORM\Column(type: Types::STRING, length: 160)]
    private string $title = '';

    #[ORM\Column(type: Types::STRING, length: 40)]
    private string $category = self::CATEGORY_FEEDBACK;

    #[ORM\Column(type: Types::STRING, length: 30)]
    private string $severity = self::SEVERITY_MEDIUM;

    #[ORM\Column(type: Types::STRING, length: 30)]
    private string $status = self::STATUS_OPEN;

    #[ORM\ManyToOne(targetEntity: Tenant::class, inversedBy: 'incidents')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Tenant $tenant = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $createdBy = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $summary = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $resolutionNotes = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $resolvedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = trim($title);

        return $this;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        if (!array_key_exists($category, self::categoryChoices())) {
            throw new \InvalidArgumentException(sprintf('Invalid incident category: %s', $category));
        }

        $this->category = $category;

        return $this;
    }

    public function getCategoryLabel(): string
    {
        return self::categoryChoices()[$this->category] ?? $this->category;
    }

    public function getSeverity(): string
    {
        return $this->severity;
    }

    public function setSeverity(string $severity): self
    {
        if (!array_key_exists($severity, self::severityChoices())) {
            throw new \InvalidArgumentException(sprintf('Invalid incident severity: %s', $severity));
        }

        $this->severity = $severity;

        return $this;
    }

    public function getSeverityLabel(): string
    {
        return self::severityChoices()[$this->severity] ?? $this->severity;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        if (!array_key_exists($status, self::statusChoices())) {
            throw new \InvalidArgumentException(sprintf('Invalid incident status: %s', $status));
        }

        $this->status = $status;
        if ($status === self::STATUS_RESOLVED && $this->resolvedAt === null) {
            $this->resolvedAt = new \DateTimeImmutable();
        }
        if ($status !== self::STATUS_RESOLVED) {
            $this->resolvedAt = null;
        }

        return $this;
    }

    public function getStatusLabel(): string
    {
        return self::statusChoices()[$this->status] ?? $this->status;
    }

    public function getTenant(): ?Tenant
    {
        return $this->tenant;
    }

    public function setTenant(?Tenant $tenant): self
    {
        $this->tenant = $tenant;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(?string $summary): self
    {
        $summary = $summary !== null ? trim($summary) : null;
        $this->summary = $summary !== '' ? $summary : null;

        return $this;
    }

    public function getResolutionNotes(): ?string
    {
        return $this->resolutionNotes;
    }

    public function setResolutionNotes(?string $resolutionNotes): self
    {
        $resolutionNotes = $resolutionNotes !== null ? trim($resolutionNotes) : null;
        $this->resolutionNotes = $resolutionNotes !== '' ? $resolutionNotes : null;

        return $this;
    }

    public function resolve(?string $notes = null): self
    {
        $this->setStatus(self::STATUS_RESOLVED);
        $this->setResolutionNotes($notes);

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getResolvedAt(): ?\DateTimeImmutable
    {
        return $this->resolvedAt;
    }

    /**
     * @return array<string, string>
     */
    public static function categoryChoices(): array
    {
        return [
            self::CATEGORY_ABANDON => 'Abandon',
            self::CATEGORY_EXTRACTION => 'Qualite extraction',
            self::CATEGORY_REPORT => 'Rapport beta',
            self::CATEGORY_ACCESS => 'Acces / droits',
            self::CATEGORY_FEEDBACK => 'Retour beta',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function severityChoices(): array
    {
        return [
            self::SEVERITY_LOW => 'Faible',
            self::SEVERITY_MEDIUM => 'Moyenne',
            self::SEVERITY_HIGH => 'Haute',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function statusChoices(): array
    {
        return [
            self::STATUS_OPEN => 'Ouvert',
            self::STATUS_IN_PROGRESS => 'En cours',
            self::STATUS_RESOLVED => 'Resolue',
        ];
    }
}
