<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'tenant')]
#[ORM\UniqueConstraint(name: 'uniq_tenant_code', columns: ['code'])]
class Tenant
{
    public const PLAN_BETA = 'beta';
    public const PLAN_PRO = 'pro';
    public const PLAN_PREMIUM = 'premium';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_SUSPENDED = 'suspended';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[Assert\NotBlank]
    #[ORM\Column(type: Types::STRING, length: 120)]
    private string $name = '';

    #[Assert\NotBlank]
    #[ORM\Column(type: Types::STRING, length: 80)]
    private string $code = '';

    #[ORM\Column(type: Types::STRING, length: 30)]
    private string $plan = self::PLAN_BETA;

    #[ORM\Column(type: Types::STRING, length: 30)]
    private string $status = self::STATUS_ACTIVE;

    #[ORM\Column(type: Types::STRING, length: 180, nullable: true)]
    private ?string $betaContactEmail = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    /** @var Collection<int, User> */
    #[ORM\OneToMany(mappedBy: 'tenant', targetEntity: User::class)]
    private Collection $users;

    /** @var Collection<int, BetaPilotageIncident> */
    #[ORM\OneToMany(mappedBy: 'tenant', targetEntity: BetaPilotageIncident::class)]
    private Collection $incidents;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->users = new ArrayCollection();
        $this->incidents = new ArrayCollection();
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

    public function setName(string $name): self
    {
        $this->name = trim($name);
        $this->touch();

        return $this;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $code = mb_strtolower(trim($code));
        $this->code = preg_replace('/[^a-z0-9\-]+/', '-', $code) ?: $code;
        $this->code = trim($this->code, '-');
        $this->touch();

        return $this;
    }

    public function getPlan(): string
    {
        return $this->plan;
    }

    public function setPlan(string $plan): self
    {
        if (!array_key_exists($plan, self::planChoices())) {
            throw new \InvalidArgumentException(sprintf('Invalid tenant plan: %s', $plan));
        }

        $this->plan = $plan;
        $this->touch();

        return $this;
    }

    public function getPlanLabel(): string
    {
        return self::planChoices()[$this->plan] ?? $this->plan;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        if (!array_key_exists($status, self::statusChoices())) {
            throw new \InvalidArgumentException(sprintf('Invalid tenant status: %s', $status));
        }

        $this->status = $status;
        $this->touch();

        return $this;
    }

    public function getStatusLabel(): string
    {
        return self::statusChoices()[$this->status] ?? $this->status;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function getBetaContactEmail(): ?string
    {
        return $this->betaContactEmail;
    }

    public function setBetaContactEmail(?string $betaContactEmail): self
    {
        $this->betaContactEmail = $betaContactEmail !== null ? mb_strtolower(trim($betaContactEmail)) : null;
        $this->touch();

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $notes = $notes !== null ? trim($notes) : null;
        $this->notes = $notes !== '' ? $notes : null;
        $this->touch();

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

    /** @return Collection<int, User> */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    /** @return Collection<int, BetaPilotageIncident> */
    public function getIncidents(): Collection
    {
        return $this->incidents;
    }

    /**
     * @return array<string, string>
     */
    public static function planChoices(): array
    {
        return [
            self::PLAN_BETA => 'Beta cabinet',
            self::PLAN_PRO => 'Pro',
            self::PLAN_PREMIUM => 'Premium',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function statusChoices(): array
    {
        return [
            self::STATUS_ACTIVE => 'Actif',
            self::STATUS_SUSPENDED => 'Suspendu',
        ];
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
