<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
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
