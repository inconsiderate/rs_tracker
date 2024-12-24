<?php

namespace App\Entity;

use App\Repository\RSMatchRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RSMatchRepository::class)]
class RSMatch
{
    public const ADVENTURE = 'adventure';
    public const ACTION = 'action';
    public const COMEDY = 'comedy';
    public const CONTEMPORARY = 'contemporary';
    public const DRAMA = 'drama';
    public const FANTASY = 'fantasy';
    public const HISTORICAL = 'historical';
    public const HORROR = 'horror';
    public const MYSTERY = 'mystery';
    public const PSYCHOLOGICAL = 'psychological';
    public const ROMANCE = 'romance';
    public const SATIRE = 'satire';
    public const SCI_FI = 'sci_fi';
    public const ONE_SHOT = 'one_shot';
    public const TRAGEDY = 'tragedy';

    public const ALL_GENRES = [
        self::ADVENTURE,
        self::ACTION,
        self::COMEDY,
        self::CONTEMPORARY,
        self::DRAMA,
        self::FANTASY,
        self::HISTORICAL,
        self::HORROR,
        self::MYSTERY,
        self::PSYCHOLOGICAL,
        self::ROMANCE,
        self::SATIRE,
        self::SCI_FI,
        self::ONE_SHOT,
        self::TRAGEDY,
    ];
    
    public static function getHumanReadableName(string $genre): string
    {
        $genreNames = [
            self::ADVENTURE => 'Adventure',
            self::ACTION => 'Action',
            self::COMEDY => 'Comedy',
            self::CONTEMPORARY => 'Contemporary',
            self::DRAMA => 'Drama',
            self::FANTASY => 'Fantasy',
            self::HISTORICAL => 'Historical',
            self::HORROR => 'Horror',
            self::MYSTERY => 'Mystery',
            self::PSYCHOLOGICAL => 'Psychological',
            self::ROMANCE => 'Romance',
            self::SATIRE => 'Satire',
            self::SCI_FI => 'Sci-Fi',
            self::ONE_SHOT => 'Short Story',
            self::TRAGEDY => 'Tragedy',
        ];

        return $genreNames[$genre] ?? $genre;
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'rSMatches')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Story $storyID = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column]
    private ?bool $active = null;

    #[ORM\Column(nullable: true)]
    private ?int $highestPosition = null;

    #[ORM\Column(length: 255)]
    private ?string $genre = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $removedDate = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStoryID(): ?Story
    {
        return $this->storyID;
    }

    public function setStoryID(?Story $storyID): static
    {
        $this->storyID = $storyID;

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

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function getHighestPosition(): ?int
    {
        return $this->highestPosition;
    }

    public function setHighestPosition(?int $highestPosition): static
    {
        $this->highestPosition = $highestPosition;

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

    public function getRemovedDate(): ?\DateTimeInterface
    {
        return $this->removedDate;
    }

    public function setRemovedDate(?\DateTimeInterface $removedDate): static
    {
        $this->removedDate = $removedDate;

        return $this;
    }

    public function getTimeOnList(): ?string
    {
        $endDate = $this->removedDate;
        if (!$endDate) {
            $endDate = new \DateTime();
        }
    
        $interval = $this->date->diff($endDate);
    
        // Build the output string based on the available parts
        $parts = [];
        if ($interval->d > 0) {
            $parts[] = $interval->d . ' day' . ($interval->d > 1 ? 's' : '');
        }
        if ($interval->h > 0) {
            $parts[] = $interval->h . ' hour' . ($interval->h > 1 ? 's' : '');
        }
        if ($interval->i > 0) {
            $parts[] = $interval->i . ' minute' . ($interval->i > 1 ? 's' : '');
        }
    
        // If no days, hours, or minutes, fall back to seconds
        if (empty($parts) && $interval->s > 0) {
            $parts[] = $interval->s . ' second' . ($interval->s > 1 ? 's' : '');
        }
    
        // Join the parts with a space
        return implode(' ', $parts);
    }
}
