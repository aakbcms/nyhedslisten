<?php

/**
 * This file is part of aakbcms/nyhedslisten.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Repository;

use App\Entity\SearchRun;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Repository for SearchRun Entity.
 *
 * @method SearchRun|null find($id, $lockMode = null, $lockVersion = null)
 * @method SearchRun|null findOneBy(array $criteria, array $orderBy = null)
 * @method SearchRun[]    findAll()
 * @method SearchRun[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
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
