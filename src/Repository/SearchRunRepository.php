<?php

namespace App\Repository;

use App\Entity\SearchRun;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method SearchRun|null find($id, $lockMode = null, $lockVersion = null)
 * @method SearchRun|null findOneBy(array $criteria, array $orderBy = null)
 * @method SearchRun[]    findAll()
 * @method SearchRun[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SearchRunRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SearchRun::class);
    }
}
