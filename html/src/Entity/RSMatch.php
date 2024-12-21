<?php

namespace App\Entity;

use App\Repository\RSMatchRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RSMatchRepository::class)]
class RSMatch
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'rSMatches')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $userID = null;

    #[ORM\ManyToOne(inversedBy: 'rSMatches')]
    #[ORM\JoinColumn(nullable: false)]
    private ?StoryTracker $trackerID = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $listPosition = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserID(): ?User
    {
        return $this->userID;
    }

    public function setUserID(?User $userID): static
    {
        $this->userID = $userID;

        return $this;
    }

    public function getTrackerID(): ?StoryTracker
    {
        return $this->trackerID;
    }

    public function setTrackerID(?StoryTracker $trackerID): static
    {
        $this->trackerID = $trackerID;

        return $this;
    }

    public function getListPosition(): ?int
    {
        return $this->listPosition;
    }

    public function setListPosition(int $listPosition): static
    {
        $this->listPosition = $listPosition;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }
}
