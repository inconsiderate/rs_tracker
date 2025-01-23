<?php

namespace App\Repository;

use App\Entity\RSDaily;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RSDaily>
 */
class RSDailyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RSDaily::class);
    }

    
    public function findByUser($user): ?array
    {
        return $this->createQueryBuilder('r')
            ->join('r.story', 's')
            ->join('s.users', 'u')
            ->where('u = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }
}
