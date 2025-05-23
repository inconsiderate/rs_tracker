<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
#[UniqueEntity(fields: ['username'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $username = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column]
    private bool $isVerified = false;

    /**
     * @var Collection<int, Story>
     */
    #[ORM\ManyToMany(targetEntity: Story::class, mappedBy: 'users')]
    private Collection $stories;

    #[ORM\Column(type: Types::ARRAY)]
    private array $preferences = [];

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private ?array $patreonData = null;

    #[ORM\Column(nullable: true)]
    private ?int $patreonId = null;

    public function __construct()
    {
        $this->stories = new ArrayCollection();
        $this->preferences['sendMeEmails'] = true;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    /**
     * @return Collection<int, Story>
     */
    public function getStories(): Collection
    {
        return $this->stories;
    }

    public function addStory(Story $story): static
    {
        if (!$this->stories->contains($story)) {
            $this->stories->add($story);
            $story->addUser($this);
        }

        return $this;
    }

    public function removeStory(Story $story): static
    {
        if ($this->stories->removeElement($story)) {
            $story->removeUser($this);
        }

        return $this;
    }

    public function getPreferences(): array
    {
        return $this->preferences;
    }

    public function getPreference($pref)
    {
        return $this->preferences[$pref];
    }

    public function setPreferences(array $preferences): static
    {
        if (!is_array($this->preferences)) {
            $this->preferences = [];
        }
    
        $this->preferences = array_merge($this->preferences, $preferences);
    
        return $this;
    }
    
    public function setPreference(string $key, mixed $value): static
    {
        if (!is_array($this->preferences)) {
            $this->preferences = [];
        }
    
        $this->preferences[$key] = $value;
    
        return $this;
    }
    
    public function getMinRankToNotify(): int
    {
        return $this->preferences['minRankToNotify'] ?? 20;
    }
    
    public function getDisplayHiddenLists(): bool
    {
        if (!$this->isSubscribed()) {
            return false;
        }
        return $this->preferences['displayHiddenLists'] ?? false;
    }
    
    // public function getEmailHiddenLists(): bool
    // {
    //     if (!$this->isSubscribed()) {
    //         return false;
    //     }
    //     return $this->preferences['emailHiddenLists'] ?? false;
    // }
    
    public function getEmailHiddenLists(): bool
    {
        return $this->preferences['emailHiddenLists'] ?? true;
    }
    
    public function getSendMeEmails(): bool
    {
        return $this->preferences['sendMeEmails'] ?? true;
    }
    
    public function isSubscribed(): bool
    {
        $patreonData = $this->getPatreonData();
        if (isset($patreonData['patron_status']) && $patreonData['patron_status'] == 'active_patron') {
            return true;
        }
        return false;
    }
    // helper so we don't get confused
    public function getEmailAddress(): ?string
    {
        return $this->username;
    }

    public function getPatreonData(): ?array
    {
        return $this->patreonData;
    }

    public function setPatreonData(?array $patreonData): static
    {
        if (!is_array($this->patreonData)) {
            $this->patreonData = [];
        }
    
        $this->patreonData = array_merge($this->patreonData, $patreonData);
    
        return $this;
    }

    public function getPatreonId(): ?int
    {
        return $this->patreonId;
    }

    public function setPatreonId(?int $patreonId): static
    {
        $this->patreonId = $patreonId;

        return $this;
    }
}
