<?php

namespace App\Entity;

use App\Repository\PrescriberInvitationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PrescriberInvitationRepository::class)]
#[ORM\Table(name: 'prescriber_invitation')]
#[ORM\Index(columns: ['token'], name: 'idx_prescriber_token')]
#[ORM\Index(columns: ['session_id', 'created_at'], name: 'idx_prescriber_session')]
class PrescriberInvitation
{
    public const ROLE_NOTAIRE            = 'notaire';
    public const ROLE_BANQUIER           = 'banquier';
    public const ROLE_EXPERT_COMPTABLE   = 'expert-comptable';
    public const ROLE_CONSEILLER         = 'conseiller';
    public const ROLE_FAMILLE            = 'famille';
    public const ROLE_AUTRE              = 'autre';

    public const ALL_ROLES = [
        self::ROLE_NOTAIRE          => 'Notaire',
        self::ROLE_BANQUIER         => 'Banquier',
        self::ROLE_EXPERT_COMPTABLE => 'Expert-comptable',
        self::ROLE_CONSEILLER       => 'Conseiller financier',
        self::ROLE_FAMILLE          => 'Membre de la famille',
        self::ROLE_AUTRE            => 'Autre',
    ];

    public const ALL_BLOCKS = ['profil', 'projets', 'valeurs', 'timing', 'patrimoine', 'flux'];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: OnboardingSession::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private OnboardingSession $session;

    #[ORM\Column(type: Types::STRING, length: 128, unique: true)]
    private string $token;

    #[ORM\Column(type: Types::STRING, length: 50)]
    private string $prescriberRole;

    /**
     * Dashboard block keys the prescriber is allowed to see: ['profil', 'projets', ...].
     * @var list<string>
     */
    #[ORM\Column(type: Types::JSON)]
    private array $authorizedBlocks = [];

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $note = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $expiresAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $accessedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $revokedAt = null;

    #[ORM\Column(type: Types::INTEGER)]
    private int $correctionCount = 0;

    public function __construct(OnboardingSession $session, string $prescriberRole, array $authorizedBlocks)
    {
        $this->session          = $session;
        $this->prescriberRole   = $prescriberRole;
        $this->authorizedBlocks = $authorizedBlocks;
        $this->token            = bin2hex(random_bytes(32));
        $this->createdAt        = new \DateTimeImmutable();
        $this->expiresAt        = new \DateTimeImmutable('+30 days');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSession(): OnboardingSession
    {
        return $this->session;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getPrescriberRole(): string
    {
        return $this->prescriberRole;
    }

    public function getPrescriberRoleLabel(): string
    {
        return self::ALL_ROLES[$this->prescriberRole] ?? $this->prescriberRole;
    }

    /** @return list<string> */
    public function getAuthorizedBlocks(): array
    {
        return $this->authorizedBlocks;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;
        return $this;
    }

    public function getExpiresAt(): \DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getAccessedAt(): ?\DateTimeImmutable
    {
        return $this->accessedAt;
    }

    public function getRevokedAt(): ?\DateTimeImmutable
    {
        return $this->revokedAt;
    }

    public function getCorrectionCount(): int
    {
        return $this->correctionCount;
    }

    public function incrementCorrectionCount(): void
    {
        ++$this->correctionCount;
    }

    public function markAccessed(): void
    {
        if ($this->accessedAt === null) {
            $this->accessedAt = new \DateTimeImmutable();
        }
    }

    public function revoke(): void
    {
        $this->revokedAt = new \DateTimeImmutable();
    }

    public function isActive(): bool
    {
        return $this->revokedAt === null && $this->expiresAt > new \DateTimeImmutable();
    }

    public function isExpired(): bool
    {
        return $this->expiresAt <= new \DateTimeImmutable();
    }
}
