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
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @TODO: MISSING DOCUMENTATION.
 *
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
     * @param \DateTimeImmutable $since
     *
     * @TODO: MISSING DOCUMENTATION.
     *
     * @return mixed
     */
    public function findBySearchDate(\DateTimeImmutable $since)
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
