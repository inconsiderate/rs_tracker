<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
#[UniqueEntity(fields: ['username'], message: 'There is already an account with this username')]
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

    /**
     * @var Collection<int, RSMatch>
     */
    #[ORM\OneToMany(targetEntity: RSMatch::class, mappedBy: 'userID', orphanRemoval: true)]
    private Collection $rSMatches;

    #[ORM\Column]
    private bool $isVerified = false;

    /**
     * @var Collection<int, StoryTracker>
     */
    #[ORM\OneToMany(targetEntity: StoryTracker::class, mappedBy: 'UserStories', orphanRemoval: true)]
    private Collection $storyTrackers;

    public function __construct()
    {
        $this->rSMatches = new ArrayCollection();
        $this->storyTrackers = new ArrayCollection();
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

    /**
     * @return Collection<int, RSMatch>
     */
    public function getRSMatches(): Collection
    {
        return $this->rSMatches;
    }

    public function addRSMatch(RSMatch $rSMatch): static
    {
        if (!$this->rSMatches->contains($rSMatch)) {
            $this->rSMatches->add($rSMatch);
            $rSMatch->setUserID($this);
        }

        return $this;
    }

    public function removeRSMatch(RSMatch $rSMatch): static
    {
        if ($this->rSMatches->removeElement($rSMatch)) {
            // set the owning side to null (unless already changed)
            if ($rSMatch->getUserID() === $this) {
                $rSMatch->setUserID(null);
            }
        }

        return $this;
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
     * @return Collection<int, StoryTracker>
     */
    public function getStoryTrackers(): Collection
    {
        return $this->storyTrackers;
    }

    public function addStoryTracker(StoryTracker $storyTracker): static
    {
        if (!$this->storyTrackers->contains($storyTracker)) {
            $this->storyTrackers->add($storyTracker);
            $storyTracker->setUserStories($this);
        }

        return $this;
    }

    public function removeStoryTracker(StoryTracker $storyTracker): static
    {
        if ($this->storyTrackers->removeElement($storyTracker)) {
            // set the owning side to null (unless already changed)
            if ($storyTracker->getUserStories() === $this) {
                $storyTracker->setUserStories(null);
            }
        }

        return $this;
    }
}
