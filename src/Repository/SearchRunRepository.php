<?php

/**
 * @file
 */

namespace App\Repository;

use App\Entity\SearchRun;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository for SearchRun Entity.
 */
class SearchRunRepository extends ServiceEntityRepository
{
    /**
     * SearchRunRepository constructor.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SearchRun::class);
    }

    /**
     * Delete search runs older than date.
     *
     * @param \DateTime $dateTime
     *
     * @return int
     *   Number of deleted rows
     */
    public function deleteBefore(\DateTime $dateTime): int
    {
        return $this->createQueryBuilder('sr')
            ->delete(SearchRun::class, 'sr')
            ->where('sr.runAt < :dateTime')
            ->setParameter('dateTime', $dateTime)
            ->getQuery()
            ->execute();
    }
}
