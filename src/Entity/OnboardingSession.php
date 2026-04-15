<?php

namespace App\Entity;

use App\Repository\OnboardingSessionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OnboardingSessionRepository::class)]
#[ORM\Table(name: 'onboarding_session')]
class OnboardingSession
{
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_DRAFT = 'draft';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_ABANDONED = 'abandoned';

    // PLANILIFE phases
    public const PHASE_DISCOVERY = 'discovery';
    public const PHASE_QUALIFICATION = 'qualification';
    public const PHASE_RISK_ANALYSIS = 'risk_analysis';
    public const PHASE_ETAPES = 'etapes';
    public const PHASE_PATRIMOINE = 'patrimoine';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Account::class, cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?Account $account = null;

    #[ORM\ManyToOne(targetEntity: Contact::class, cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?Contact $contact = null;

    #[ORM\Column(type: Types::STRING, length: 50)]
    private string $status = self::STATUS_IN_PROGRESS;

    #[ORM\Column(type: Types::STRING, length: 50)]
    private string $phase = self::PHASE_DISCOVERY;

    #[ORM\Column(type: Types::JSON)]
    private array $messages = [];

    #[ORM\Column(type: Types::JSON)]
    private array $extractedData = [];

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    private ?float $completeness = null;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(?Account $account): self
    {
        $this->account = $account;
        return $this;
    }

    public function getContact(): ?Contact
    {
        return $this->contact;
    }

    public function setContact(?Contact $contact): self
    {
        $this->contact = $contact;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        if (!in_array($status, [self::STATUS_IN_PROGRESS, self::STATUS_DRAFT, self::STATUS_COMPLETED, self::STATUS_ABANDONED])) {
            throw new \InvalidArgumentException(sprintf('Invalid status: %s', $status));
        }
        $this->status = $status;
        return $this;
    }

    public function getPhase(): string
    {
        return $this->phase;
    }

    public function setPhase(string $phase): self
    {
        $validPhases = [
            self::PHASE_DISCOVERY,
            self::PHASE_QUALIFICATION,
            self::PHASE_RISK_ANALYSIS,
            self::PHASE_ETAPES,
            self::PHASE_PATRIMOINE,
        ];
        if (!in_array($phase, $validPhases)) {
            throw new \InvalidArgumentException(sprintf('Invalid phase: %s', $phase));
        }
        $this->phase = $phase;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function addMessage(string $role, string $content): self
    {
        $this->messages[] = [
            'role' => $role,
            'content' => $content,
            'timestamp' => (new \DateTime())->format('Y-m-d H:i:s'),
        ];
        // Force Doctrine to detect the array change
        $this->messages = array_values($this->messages);
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getExtractedData(): array
    {
        return $this->extractedData;
    }

    public function setExtractedData(array $data): self
    {
        $this->extractedData = $data;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function updateExtractedData(array $newData): self
    {
        $this->extractedData = array_merge($this->extractedData, $newData);
        // Force Doctrine to detect the array change
        $this->extractedData = array_replace($this->extractedData, $this->extractedData);
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getCompleteness(): float
    {
        return $this->completeness ?? 0.0;
    }

    public function setCompleteness(?float $completeness): self
    {
        $this->completeness = $completeness;

        return $this;
    }
}
