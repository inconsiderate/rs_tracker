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

    public function findStoriesRecentlyAdded($genre): ?array
    {
        $qb = $this->createQueryBuilder('r');

        $qb->select('r', 's')
           ->leftJoin('r.storyID', 's');
    
        if (!empty($genre)) {
            $qb->andWhere('r.genre = :genre')
               ->setParameter('genre', $genre);
        }
        $qb->orderBy('r.date', 'DESC')
           ->setMaxResults(10);
    
        $result = $qb->getQuery()->getResult();
    
        $data = [];
        $currentDate = new \DateTime(); // Current time for calculating the duration
    
        foreach ($result as $match) {
            $startDate = $match->getDate();
            $durationString = $this->calculateDurationString($startDate, $currentDate);
    
            $data[] = [
                'story' => $match->getStoryID(),
                'duration' => $durationString,
            ];
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
    
}
