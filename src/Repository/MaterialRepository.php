<?php

/**
 * @file
 */

namespace App\Repository;

use App\Entity\Material;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\QueryException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository for Material Entity.
 */
class MaterialRepository extends ServiceEntityRepository
{
    /**
     * MaterialRepository constructor.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Material::class);
    }

    /**
     * Find materials from list of match PIDs.
     *
     * @param array $pidList
     *   The array of PID's to search for
     *
     * @return mixed
     *   Array of materials indexed by match PID
     *
     * @throws QueryException
     */
    public function findByPidList(array &$pidList): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.pid IN (:ids)')
            ->setParameter('ids', $pidList)
            ->indexBy('m', 'm.pid')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find materials received since a given date.
     *
     * @return mixed
     *   Array of materials
     */
    public function findLatest(\DateTimeInterface $since): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.date >= :date')
            ->setParameter('date', $since)
            ->orderBy('m.creatorFiltered', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find materials received since a given date and belonging to a specific search.
     *
     * @return mixed
     *   Array of materials
     */
    public function findLatestBySearch(\DateTimeInterface $since, int $searchId): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere(':searchId MEMBER OF m.searches')
            ->setParameter('searchId', $searchId)
            ->andWhere('m.date >= :date')
            ->setParameter('date', $since)
            ->getQuery()
            ->getResult();
    }
}
