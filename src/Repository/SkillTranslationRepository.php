<?php

namespace App\Repository;

use App\Entity\SkillTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SkillTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method SkillTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method SkillTranslation[]    findAll()
 * @method SkillTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SkillTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SkillTranslation::class);
    }

    // /**
    //  * @return SkillTranslation[] Returns an array of SkillTranslation objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SkillTranslation
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
