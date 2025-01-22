<?php

namespace App\Entity;

use App\Repository\RSDailyRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RSDailyRepository::class)]
class RSDaily
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\ManyToOne(inversedBy: 'rSDailies')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Story $story = null;

    #[ORM\Column(length: 255)]
    private ?string $genre = null;

    #[ORM\Column]
    private ?int $highestPosition = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getStory(): ?Story
    {
        return $this->story;
    }

    public function setStory(?Story $story): static
    {
        $this->story = $story;

        return $this;
    }

    public function getGenre(): ?string
    {
        return $this->genre;
    }

    public function setGenre(string $genre): static
    {
        $this->genre = $genre;

        return $this;
    }

    public function getHighestPosition(): ?int
    {
        return $this->highestPosition;
    }

    public function setHighestPosition(int $highestPosition): static
    {
        $this->highestPosition = $highestPosition;

        return $this;
    }
}
