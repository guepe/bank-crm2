<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'user')]
#[ORM\UniqueConstraint(name: 'uniq_user_username', columns: ['username'])]
#[ORM\UniqueConstraint(name: 'uniq_user_email', columns: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 180)]
    private string $username = '';

    #[ORM\Column(type: Types::STRING, length: 180, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(type: Types::JSON)]
    private array $roles = [];

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $password = '';

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    private bool $enabled = true;

    #[ORM\OneToOne(inversedBy: 'userAccount', targetEntity: Contact::class)]
    #[ORM\JoinColumn(name: 'contact_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Contact $contact = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = mb_strtolower(trim($username));

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

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;

        if (in_array('ROLE_ADMIN', $roles, true)) {
            $roles[] = 'ROLE_USER';
        }

        return array_values(array_unique($roles));
    }

    public function setRoles(array $roles): self
    {
        $this->roles = array_values(array_unique(array_filter($roles)));

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getContact(): ?Contact
    {
        return $this->contact;
    }

    public function setContact(?Contact $contact): self
    {
        if ($this->contact === $contact) {
            return $this;
        }

        if ($this->contact instanceof Contact && $this->contact->getUserAccount() === $this) {
            $this->contact->setUserAccount(null);
        }

        $this->contact = $contact;

        if ($contact instanceof Contact && $contact->getUserAccount() !== $this) {
            $contact->setUserAccount($this);
        }

        return $this;
    }

    public function isInternalUser(): bool
    {
        return in_array('ROLE_USER', $this->getRoles(), true) || in_array('ROLE_ADMIN', $this->getRoles(), true);
    }

    public function isClientUser(): bool
    {
        return in_array('ROLE_CLIENT', $this->getRoles(), true);
    }

    public function getDisplayName(): string
    {
        if ($this->contact instanceof Contact) {
            return (string) $this->contact;
        }

        return $this->username;
    }

    public function getRoleLabels(): array
    {
        $labels = [];
        $roles = $this->getRoles();
        $hasAdminRole = in_array('ROLE_ADMIN', $roles, true);

        foreach ($roles as $role) {
            if ($role === 'ROLE_USER' && $hasAdminRole) {
                continue;
            }

            $labels[] = match ($role) {
                'ROLE_ADMIN' => 'Administrateur',
                'ROLE_USER' => 'Utilisateur interne',
                'ROLE_CLIENT' => 'Client portail',
                default => $role,
            };
        }

        return array_values(array_unique($labels));
    }

    public function eraseCredentials(): void
    {
    }
}
