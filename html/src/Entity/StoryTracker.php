<?php

namespace App\Entity;

use App\Repository\StoryTrackerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StoryTrackerRepository::class)]
class StoryTracker
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $storyName = null;

    #[ORM\Column(type: Types::ARRAY)]
    private array $trackedGenres = [];

    /**
     * @var Collection<int, RSMatch>
     */
    #[ORM\OneToMany(targetEntity: RSMatch::class, mappedBy: 'trackerID', orphanRemoval: true)]
    private Collection $rSMatches;

    #[ORM\ManyToOne(inversedBy: 'storyTrackers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $UserStories = null;

    #[ORM\Column(length: 255)]
    private ?string $storyAddress = null;

    public function __construct()
    {
        $this->rSMatches = new ArrayCollection();
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

    public function getTrackedGenres(): array
    {
        return $this->trackedGenres;
    }

    public function setTrackedGenres(array $trackedGenres): static
    {
        $this->trackedGenres = $trackedGenres;

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
            $rSMatch->setTrackerID($this);
        }

        return $this;
    }

    public function removeRSMatch(RSMatch $rSMatch): static
    {
        if ($this->rSMatches->removeElement($rSMatch)) {
            // set the owning side to null (unless already changed)
            if ($rSMatch->getTrackerID() === $this) {
                $rSMatch->setTrackerID(null);
            }
        }

        return $this;
    }

    public function getUserStories(): ?User
    {
        return $this->UserStories;
    }

    public function setUserStories(?User $UserStories): static
    {
        $this->UserStories = $UserStories;

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
}
