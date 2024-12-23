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

    public function findByUser($user)
    {
        return $this->createQueryBuilder('r')
            ->join('r.storyID', 's')
            ->join('s.users', 'u')
            ->where('u = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }
}
