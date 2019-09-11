<?php

/**
 * This file is part of aakbcms/nyhedslisten.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Repository;

use App\Entity\Category;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Repository for Category Entity.
 *
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
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
     * @param DateTimeImmutable $since The date materials should have been added after
     *
     * @return mixed Array of Categories
     */
    public function findBySearchMaterialDate(DateTimeImmutable $since)
    {
        return $this->createQueryBuilder('c')
            ->select('c', 's', 'm')
            ->innerJoin('c.searches', 's')
            ->innerJoin('s.materials', 'm')
            ->andWhere('m.date >= :date')
            ->setParameter('date', $since)
            ->getQuery()
            ->getResult();
    }
}
