<?php

namespace App\Repository;

use App\Entity\UserOccupation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserOccupation|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserOccupation|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserOccupation[]    findAll()
 * @method UserOccupation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserOccupationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserOccupation::class);
    }

    // /**
    //  * @return UserOccupation[] Returns an array of UserOccupation objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?UserOccupation
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
