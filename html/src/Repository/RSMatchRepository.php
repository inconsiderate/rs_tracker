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
            // keep track of the longest duration and the story
            $longestDuration = 0;
            $longestStory = null;
            $longestDurationString = '';
        
            foreach ($result as $match) {
                $startDate = $match->getDate();
                $endDate = $match->getRemovedDate() ?: new \DateTime(); 
        
                // Calculate the duration in seconds
                $duration = $startDate->diff($endDate);
                $durationInSeconds = $duration->days * 86400 + $duration->h * 3600 + $duration->i * 60 + $duration->s;
        
                // Check if this is the longest duration
                if ($durationInSeconds > $longestDuration) {
                    $longestDuration = $durationInSeconds;
                    $longestStory = $match->getStoryID(); 
        
                    // Build the humanreadable string
                    $durationParts = [];
                    if ($duration->days > 0) {
                        $durationParts[] = $duration->days . ' day' . ($duration->days > 1 ? 's' : '');
                    }
                    if ($duration->h > 0) {
                        $durationParts[] = $duration->h . ' hour' . ($duration->h > 1 ? 's' : '');
                    }
                    if ($duration->i > 0) {
                        $durationParts[] = $duration->i . ' minute' . ($duration->i > 1 ? 's' : '');
                    }
        
                    // If no days, hours, or minutes, fall back to seconds
                    if (empty($durationParts) && $duration->s > 0) {
                        $durationParts[] = $duration->s . ' second' . ($duration->s > 1 ? 's' : '');
                    }
        
                    // Join the parts
                    $longestDurationString = implode(', ', $durationParts);
                }
            }
        
            return [
                'story' => $longestStory,
                'duration' => $longestDurationString,
            ];
        }
        
        return [];
    }
}
