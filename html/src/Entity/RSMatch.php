<?php

namespace App\Entity;

use App\Repository\RSMatchRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RSMatchRepository::class)]
class RSMatch
{
    // Genres
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

    // Tags
    public const ANTI_HERO_LEAD = 'anti-hero_lead';
    public const ARTIFICIAL_INTELLIGENCE = 'artificial_intelligence';
    public const ATTRACTIVE_LEAD = 'attractive_lead';
    public const CYBERPUNK = 'cyberpunk';
    public const DUNGEON = 'dungeon';
    public const DYSTOPIA = 'dystopia';
    public const FEMALE_LEAD = 'female_lead';
    public const FIRST_CONTACT = 'first_contact';
    public const GAMELIT = 'gamelit';
    public const GENDER_BENDER = 'gender_bender';
    // i don't know why they added a %20 space to this URL, might cause issues later if they fix it
    public const GENETICALLY_ENGINEERED = 'genetically_engineered ';
    public const GRIMDARK = 'grimdark';
    public const HARD_SCI_FI = 'hard_sci-fi';
    public const HAREM = 'harem';
    public const HIGH_FANTASY = 'high_fantasy';
    public const LITRPG = 'litrpg';
    public const LOOP = 'loop';
    public const LOW_FANTASY = 'low_fantasy';
    public const MAGIC = 'magic';
    public const MALE_LEAD = 'male_lead';
    public const MARTIAL_ARTS = 'martial_arts';
    public const MULTIPLE_LEAD = 'multiple_lead';
    public const MYTHOS = 'mythos';
    public const NON_HUMAN_LEAD = 'non-human_lead';
    public const POST_APOCALYPTIC = 'post_apocalyptic';
    public const PROGRESSION = 'progression';
    public const READER_INTERACTIVE = 'reader_interactive';
    public const REINCARNATION = 'reincarnation';
    public const RULING_CLASS = 'ruling_class';
    public const SCHOOL_LIFE = 'school_life';
    public const SECRET_IDENTITY = 'secret_identity';
    public const SLICE_OF_LIFE = 'slice_of_life';
    public const SOFT_SCI_FI = 'soft_sci-fi';
    public const SPACE_OPERA = 'space_opera';
    public const SPORTS = 'sports';
    public const STEAMPUNK = 'steampunk';
    public const STRATEGY = 'strategy';
    public const STRONG_LEAD = 'strong_lead';
    public const SUMMONED_HERO = 'summoned_hero';
    public const SUPER_HEROES = 'super_heroes';
    public const SUPERNATURAL = 'supernatural';
    public const TECHNOLOGICALLY_ENGINEERED = 'technologically_engineered';
    public const TIME_TRAVEL = 'time_travel';
    public const URBAN_FANTASY = 'urban_fantasy';
    public const VILLAINOUS_LEAD = 'villainous_lead';
    public const VIRTUAL_REALITY = 'virtual_reality';
    public const WAR_AND_MILITARY = 'war_and_military';
    public const WUXIA = 'wuxia';
    public const XIANXIA = 'xianxia';


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

    
    public const ALL_TAGS = [
        self::ANTI_HERO_LEAD,
        self::ARTIFICIAL_INTELLIGENCE,
        self::ATTRACTIVE_LEAD,
        self::CYBERPUNK,
        self::DUNGEON,
        self::DYSTOPIA,
        self::FEMALE_LEAD,
        self::FIRST_CONTACT,
        self::GAMELIT,
        self::GENDER_BENDER,
        self::GENETICALLY_ENGINEERED,
        self::GRIMDARK,
        self::HARD_SCI_FI,
        self::HAREM,
        self::HIGH_FANTASY,
        self::LITRPG,
        self::LOOP,
        self::LOW_FANTASY,
        self::MAGIC,
        self::MALE_LEAD,
        self::MARTIAL_ARTS,
        self::MULTIPLE_LEAD,
        self::MYTHOS,
        self::NON_HUMAN_LEAD,
        self::POST_APOCALYPTIC,
        self::PROGRESSION,
        self::READER_INTERACTIVE,
        self::REINCARNATION,
        self::RULING_CLASS,
        self::SCHOOL_LIFE,
        self::SECRET_IDENTITY,
        self::SLICE_OF_LIFE,
        self::SOFT_SCI_FI,
        self::SPACE_OPERA,
        self::SPORTS,
        self::STEAMPUNK,
        self::STRATEGY,
        self::STRONG_LEAD,
        self::SUMMONED_HERO,
        self::SUPER_HEROES,
        self::SUPERNATURAL,
        self::TECHNOLOGICALLY_ENGINEERED,
        self::TIME_TRAVEL,
        self::URBAN_FANTASY,
        self::VILLAINOUS_LEAD,
        self::VIRTUAL_REALITY,
        self::WAR_AND_MILITARY,
        self::WUXIA,
        self::XIANXIA,
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
            self::ANTI_HERO_LEAD => 'Anti-Hero Lead',
            self::ARTIFICIAL_INTELLIGENCE => 'Artificial Intelligence',
            self::ATTRACTIVE_LEAD => 'Attractive Lead',
            self::CYBERPUNK => 'Cyberpunk',
            self::DUNGEON => 'Dungeon',
            self::DYSTOPIA => 'Dystopia',
            self::FEMALE_LEAD => 'Female Lead',
            self::FIRST_CONTACT => 'First Contact',
            self::GAMELIT => 'Gamelit',
            self::GENDER_BENDER => 'Gender Bender',
            self::GENETICALLY_ENGINEERED => 'Genetically Engineered',
            self::GRIMDARK => 'Grimdark',
            self::HARD_SCI_FI => 'Hard Sci-Fi',
            self::HAREM => 'Harem',
            self::HIGH_FANTASY => 'High Fantasy',
            self::LITRPG => 'LitRPG',
            self::LOOP => 'Time Loop',
            self::LOW_FANTASY => 'Low Fantasy',
            self::MAGIC => 'Magic',
            self::MALE_LEAD => 'Male Lead',
            self::MARTIAL_ARTS => 'Martial Arts',
            self::MULTIPLE_LEAD => 'Multiple Lead Characters',
            self::MYTHOS => 'Mythos',
            self::NON_HUMAN_LEAD => 'Non-Human Lead',
            self::POST_APOCALYPTIC => 'Post-Apocalyptic',
            self::PROGRESSION => 'Progression',
            self::READER_INTERACTIVE => 'Reader Interactive',
            self::REINCARNATION => 'Reincarnation',
            self::RULING_CLASS => 'Ruling Class',
            self::SCHOOL_LIFE => 'School Life',
            self::SECRET_IDENTITY => 'Secret Identity',
            self::SLICE_OF_LIFE => 'Slice of Life',
            self::SOFT_SCI_FI => 'Soft Sci-Fi',
            self::SPACE_OPERA => 'Space Opera',
            self::SPORTS => 'Sports',
            self::STEAMPUNK => 'Steampunk',
            self::STRATEGY => 'Strategy',
            self::STRONG_LEAD => 'Strong Lead',
            self::SUMMONED_HERO => 'Portal Fantasy / Isekai',
            self::SUPER_HEROES => 'Super Heroes',
            self::SUPERNATURAL => 'Supernatural',
            self::TECHNOLOGICALLY_ENGINEERED => 'Technologically Engineered',
            self::TIME_TRAVEL => 'Time Travel',
            self::URBAN_FANTASY => 'Urban Fantasy',
            self::VILLAINOUS_LEAD => 'Villainous Lead',
            self::VIRTUAL_REALITY => 'Virtual Reality',
            self::WAR_AND_MILITARY => 'War and Military',
            self::WUXIA => 'Wuxia',
            self::XIANXIA => 'Xianxia',
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

    #[ORM\Column(nullable: true)]
    private ?array $matchEmailSent = null;

    #[ORM\Column(nullable: true)]
    private ?int $startFollowerCount = null;

    #[ORM\Column(nullable: true)]
    private ?int $endFollowerCount = null;

    #[ORM\Column(nullable: true)]
    private ?int $startPageCount = null;

    #[ORM\Column(nullable: true)]
    private ?int $endPageCount = null;

    #[ORM\Column(nullable: true)]
    private ?int $startViewCount = null;

    #[ORM\Column(nullable: true)]
    private ?int $endViewCount = null;

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

    public function getTimeOnListInt(): ?int
    {
        $endDate = $this->removedDate ?? new \DateTime();

        return $endDate->getTimestamp() - $this->date->getTimestamp();
    }

    public function getTimeOnList(): ?string
    {
        $endDate = $this->removedDate ?? new \DateTime();
        $totalSeconds = $endDate->getTimestamp() - $this->date->getTimestamp();
    
        $days = floor($totalSeconds / 86400);
        $totalSeconds %= 86400;
    
        $hours = floor($totalSeconds / 3600);
        $totalSeconds %= 3600;
    
        $minutes = floor($totalSeconds / 60);
        $seconds = $totalSeconds % 60;
    
        $parts = [];
        if ($days > 0) {
            $parts[] = $days . ' day' . ($days > 1 ? 's' : '');
        }
        if ($hours > 0) {
            $parts[] = $hours . ' hour' . ($hours > 1 ? 's' : '');
        }
        if ($minutes > 0) {
            $parts[] = $minutes . ' minute' . ($minutes > 1 ? 's' : '');
        }
        if ($seconds > 0 && empty($parts)) { // Only show seconds if no higher unit is used
            $parts[] = $seconds . ' second' . ($seconds > 1 ? 's' : '');
        }
    
        return implode(' ', $parts);
    }
    

    public function getMatchEmailSent(): ?array
    {
        return $this->matchEmailSent;
    }

    public function setMatchEmailSent(?array $matchEmailSent): static
    {
        $this->matchEmailSent = $matchEmailSent;

        return $this;
    }
    
    public function addHasBeenEmailed(int $userId): self
    {
        if (!is_array($this->matchEmailSent)) {
            $this->matchEmailSent = [];
        }

        if (!in_array($userId, $this->matchEmailSent, true)) {
            $this->matchEmailSent[] = $userId;
        }

        return $this;
    }

    public function hasBeenEmailed(int $userId): bool
    {
        if ($this->matchEmailSent){
            return in_array($userId, $this->matchEmailSent, true);
        }
        return false;
    }

    public function getStartFollowerCount(): ?int
    {
        return $this->startFollowerCount;
    }

    public function setStartFollowerCount(?int $startFollowerCount): static
    {
        $this->startFollowerCount = $startFollowerCount;

        return $this;
    }

    public function getEndFollowerCount(): ?int
    {
        return $this->endFollowerCount;
    }

    public function setEndFollowerCount(?int $endFollowerCount): static
    {
        $this->endFollowerCount = $endFollowerCount;

        return $this;
    }

    public function getStartPageCount(): ?int
    {
        return $this->startPageCount;
    }

    public function setStartPageCount(?int $startPageCount): static
    {
        $this->startPageCount = $startPageCount;

        return $this;
    }

    public function getEndPageCount(): ?int
    {
        return $this->endPageCount;
    }

    public function setEndPageCount(?int $endPageCount): static
    {
        $this->endPageCount = $endPageCount;

        return $this;
    }

    public function getStartViewCount(): ?int
    {
        return $this->startViewCount;
    }

    public function setStartViewCount(?int $startViewCount): static
    {
        $this->startViewCount = $startViewCount;

        return $this;
    }

    public function getEndViewCount(): ?int
    {
        return $this->endViewCount;
    }

    public function setEndViewCount(?int $endViewCount): static
    {
        $this->endViewCount = $endViewCount;

        return $this;
    }
}
