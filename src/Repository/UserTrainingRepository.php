<?php

namespace App\Repository;

use App\Entity\UserTraining;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserTraining|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserTraining|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserTraining[]    findAll()
 * @method UserTraining[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserTrainingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserTraining::class);
    }
}
