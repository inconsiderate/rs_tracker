<?php

namespace App\Repository;

use App\Entity\Story;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Story>
 */
class StoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Story::class);
    }

    public function findStoryIdsByUser(User $user): array
    {
        return $this->createQueryBuilder('s')
            ->select('s.storyId')
            ->innerJoin('s.users', 'u')
            ->where('u.id = :userId')
            ->setParameter('userId', $user->getId())
            ->getQuery()
            ->getResult();
    }

    public function findStoriesWithUsers(): array
    {
        return $this->createQueryBuilder('s')
            ->innerJoin('s.users', 'u') // Perform an inner join to ensure stories have users
            ->distinct() // Ensure no duplicates are returned
            ->getQuery()
            ->getResult();
    }
}
