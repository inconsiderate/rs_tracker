<?php

namespace App\Repository;

use App\Entity\RSMatch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RSMatch>
 */
class RSMatchRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RSMatch::class);
    }

    public function findByUser($user): ?array
    {
        return $this->createQueryBuilder('r')
            ->join('r.storyID', 's')
            ->join('s.users', 'u')
            ->where('u = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    public function findStoryWithLongestTimeAtNumberOne($genre): ?array
    {
        $qb = $this->createQueryBuilder('r');

        $qb->select('r', 's') 
           ->leftJoin('r.storyID', 's') 
           ->where('r.highestPosition = 1')
           ->andWhere('r.genre = :genre')
           ->setParameter('genre', $genre) 
           ->orderBy('r.date', 'ASC');
        
        $result = $qb->getQuery()->getResult();
        
        if (!empty($result)) {
            $longestDuration = 0;
            $longestStory = null;
            $longestDurationString = '';
        
            foreach ($result as $match) {
                $startDate = $match->getDate();
                $endDate = $match->getRemovedDate() ?: new \DateTime();
            
                // Calculate the duration in seconds
                $durationInSeconds = $this->calculateDurationInSeconds($startDate, $endDate);
            
                // Check if this is the longest duration
                if ($durationInSeconds > $longestDuration) {
                    $longestDuration = $durationInSeconds;
                    $longestStory = $match->getStoryID();
            
                    // Build the human-readable string
                    $longestDurationString = $this->calculateDurationString($startDate, $endDate);
                }
            }
        
            return [
                'story' => $longestStory,
                'duration' => $longestDurationString,
            ];
        }
        
        return [];
    }

    public function findStoriesRecentlyAdded($genre = false): ?array
    {
        $qb = $this->createQueryBuilder('r');

        $qb
            ->select('r, s')
            ->leftJoin('r.storyID', 's')
            // ->where('r.genre = :genre')
            // ->setParameter('genre', $genre)
            ->orderBy('r.date', 'DESC')
            ->setMaxResults(100);

        $result = $qb->getQuery()->getResult();
    
        $data = [];
        $currentDate = new \DateTime(); // Current time for calculating the duration
    
        foreach ($result as $match) {
            $startDate = $match->getDate();
            $durationString = $this->calculateDurationString($startDate, $currentDate);
            if (!isset($data[$match->getStoryID()->getId()])) {
                $data[$match->getStoryID()->getId()] = [
                    'story' => $match->getStoryID(),
                    'genre' => $match->getGenre(),
                    'duration' => $durationString,
                ];
            }
        }
    
        return $data;
    }

    private function calculateDurationString(\DateTime $startDate, \DateTime $endDate): string
    {
        $duration = $startDate->diff($endDate);
    
        $durationParts = [];
        if ($duration->days > 0) {
            $durationParts[] = $duration->days . ' day' . ($duration->days > 1 ? 's' : '');
        }
        if ($duration->h > 0) {
            $durationParts[] = $duration->h . ' hour' . ($duration->h > 1 ? 's' : '');
        }
        if ($duration->days == 0 && $duration->i > 0) {
            $durationParts[] = $duration->i . ' minute' . ($duration->i > 1 ? 's' : '');
        }
        if (empty($durationParts) && $duration->s > 0) {
            $durationParts[] = $duration->s . ' second' . ($duration->s > 1 ? 's' : '');
        }
    
        return implode(' ', $durationParts);
    }
    
    private function calculateDurationInSeconds(\DateTime $startDate, \DateTime $endDate): int
    {
        $duration = $startDate->diff($endDate);
        return $duration->days * 86400 + $duration->h * 3600 + $duration->i * 60 + $duration->s;
    }

    public function findOpenMatchesForStory(int $storyId): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.storyID = :storyId')
            ->andWhere('m.active = true')
            ->setParameter('storyId', $storyId)
            ->getQuery()
            ->getResult();
    }
    
    public function findRecentlyClosedMatchesForStory(int $storyId): array
    {
        $yesterday = (new \DateTimeImmutable('now'))->modify('-24 hours');

        return $this->createQueryBuilder('m')
            ->where('m.storyID = :storyId')
            ->andWhere('m.active = false')
            ->andWhere('m.removedDate > :yesterday')
            ->setParameter('storyId', $storyId)
            ->setParameter('yesterday', $yesterday)
            ->getQuery()
            ->getResult();
    }
}
