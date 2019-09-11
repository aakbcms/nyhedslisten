<?php

/**
 * This file is part of aakbcms/nyhedslisten.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Repository;

use App\Entity\Material;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\QueryException;

/**
 * Repository for Material Entity.
 *
 * @method Material|null find($id, $lockMode = null, $lockVersion = null)
 * @method Material|null findOneBy(array $criteria, array $orderBy = null)
 * @method Material[]    findAll()
 * @method Material[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MaterialRepository extends ServiceEntityRepository
{
    /**
     * MaterialRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Material::class);
    }

    /**
     * Find materials from list of match PIDs.
     *
     * @param array $pidList The array of PID's to search for
     *
     * @return mixed Array of materials indexed by match PID
     *
     * @throws QueryException
     */
    public function findByPidList(array &$pidList)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.pid IN (:ids)')
            ->setParameter('ids', $pidList)
            ->indexBy('m', 'm.pid')
            ->getQuery()
            ->getResult();
    }
}