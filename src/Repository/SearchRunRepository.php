<?php

/**
 * @file
 */

namespace App\Repository;

use App\Entity\SearchRun;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Repository for SearchRun Entity.
 */
class SearchRunRepository extends ServiceEntityRepository
{
    /**
     * SearchRunRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SearchRun::class);
    }
}
