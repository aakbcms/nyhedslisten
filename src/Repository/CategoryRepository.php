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
 * Repository for Search Entity.
 */
class CategoryRepository extends ServiceEntityRepository
{
    /**
     * SearchRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
     * @return Category[]|array
     */
    public function findAll(): array
    {
        return $this->findBy([], ['name' => 'ASC']);
    }

    /**
     * Find all Categories, join Materials where date is newer than given date.
     *
     * @param DateTimeImmutable $since
     *   The date materials should have been added after
     *
     * @return mixed
     *   Array of Categories
     */
    public function findByMaterialDate(DateTimeImmutable $since)
    {
        return $this->createQueryBuilder('c')
            ->select('c', 'm')
            ->innerJoin('c.materials', 'm')
            ->andWhere('m.date >= :date')
            ->setParameter('date', $since)
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
