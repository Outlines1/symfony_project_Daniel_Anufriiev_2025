<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
#[ORM\Table(name: "users")]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", unique: true)]
    private string $email;

    #[ORM\Column(type: "json")]
    private array $roles = [];

    #[ORM\Column(type: "string")]
    private string $password;

    // Plain password is used for form processing, not stored in the database.
    private ?string $plainPassword = null;

    #[ORM\ManyToMany(targetEntity: Group::class, inversedBy: "users")]
    #[ORM\JoinTable(name: "user_groups")]
    private Collection $groups;

    public function __construct()
    {
        $this->groups = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;

        // Add roles from groups (avoiding duplicates)
        foreach ($this->groups as $group) {
            $roles = array_merge($roles, $group->getRoles());
        }

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
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

    // The plain password is only used during form submission (not stored in the database)
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }

    // Clear plain password after processing to ensure it is not exposed
    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    public function getGroups(): Collection
    {
        return $this->groups;
    }

    // Add a group to the user
    public function addGroup(Group $group): self
    {
        if (!$this->groups->contains($group)) {
            $this->groups->add($group);
            $group->addUser($this); // Bidirectional relationship
        }
        return $this;
    }

    // Remove a group from the user
    public function removeGroup(Group $group): self
    {
        if ($this->groups->removeElement($group)) {
            $group->removeUser($this);
        }
        return $this;
    }

    // Get the user identifier (Symfony 6+ compatibility with UserInterface)
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    // Clear all groups the user belongs to
    public function clearGroups(): self
    {
        foreach ($this->groups as $group) {
            $this->removeGroup($group);
        }
        return $this;
    }
}
