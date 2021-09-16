<?php

namespace App\Repository;

use App\Entity\OccupationTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OccupationTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method OccupationTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method OccupationTranslation[]    findAll()
 * @method OccupationTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OccupationTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OccupationTranslation::class);
    }

    // /**
    //  * @return OccupationTranslation[] Returns an array of OccupationTranslation objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?OccupationTranslation
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
