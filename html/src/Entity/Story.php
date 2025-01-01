<?php

namespace App\Entity;

use App\Repository\StoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StoryRepository::class)]
class Story
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $storyName = null;

    /**
     * @var Collection<int, RSMatch>
     */
    #[ORM\OneToMany(targetEntity: RSMatch::class, mappedBy: 'storyID', orphanRemoval: true)]
    private Collection $rSMatches;

    #[ORM\Column(length: 255)]
    private ?string $storyAddress = null;

    #[ORM\Column]
    private ?int $storyId = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'stories')]
    private Collection $users;

    #[ORM\Column(length: 255)]
    private ?string $storyAuthor = null;

    #[ORM\Column]
    private ?int $storyAuthorId = null;

    public function __construct()
    {
        $this->rSMatches = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStoryName(): ?string
    {
        return $this->storyName;
    }

    public function setStoryName(string $storyName): static
    {
        $this->storyName = $storyName;

        return $this;
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
            $rSMatch->setStoryID($this);
        }

        return $this;
    }

    public function removeRSMatch(RSMatch $rSMatch): static
    {
        if ($this->rSMatches->removeElement($rSMatch)) {
            // set the owning side to null (unless already changed)
            if ($rSMatch->getStoryID() === $this) {
                $rSMatch->setStoryID(null);
            }
        }

        return $this;
    }

    public function getStoryAddress(): ?string
    {
        return $this->storyAddress;
    }

    public function setStoryAddress(string $storyAddress): static
    {
        $this->storyAddress = $storyAddress;

        return $this;
    }

    public function getStoryId(): ?int
    {
        return $this->storyId;
    }

    public function setStoryId(int $storyId): static
    {
        $this->storyId = $storyId;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        $this->users->removeElement($user);

        return $this;
    }

    public function getStoryAuthor(): ?string
    {
        return $this->storyAuthor;
    }

    public function setStoryAuthor(string $storyAuthor): static
    {
        $this->storyAuthor = $storyAuthor;

        return $this;
    }
    
    public function getStoryAuthorId(): ?int
    {
        return $this->storyAuthorId;
    }

    public function setStoryAuthorId(int $storyAuthorId): static
    {
        $this->storyAuthorId = $storyAuthorId;

        return $this;
    }
}
