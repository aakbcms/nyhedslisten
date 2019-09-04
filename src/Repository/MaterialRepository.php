<?php

namespace App\Repository;

use App\Entity\Material;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\QueryException;

/**
 * @method Material|null find($id, $lockMode = null, $lockVersion = null)
 * @method Material|null findOneBy(array $criteria, array $orderBy = null)
 * @method Material[]    findAll()
 * @method Material[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MaterialRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Material::class);
    }

    /**
     * Find materials from list of match PIDs.
     *
     * @param array $pidList
     *
     * @return mixed
     *  Array of materials indexed by match PID
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
