<?php

/**
 * @file
 */

namespace App\Repository;

use App\Entity\Category;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Repository for Category Entity.
 */
class CategoryRepository extends ServiceEntityRepository
{
    /**
     * CategoryRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
     * Find all Categories, join Searches, join Materials where date is newer than given date.
     *
     * @param DateTimeImmutable $since
     *   The date materials should have been added after
     *
     * @return mixed
     *   Array of Categories
     */
    public function findBySearchMaterialDate(DateTimeImmutable $since)
    {
        return $this->createQueryBuilder('c')
            ->select('c', 's', 'm')
            ->innerJoin('c.searches', 's')
            ->innerJoin('s.materials', 'm')
            ->andWhere('m.date >= :date')
            ->setParameter('date', $since)
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
