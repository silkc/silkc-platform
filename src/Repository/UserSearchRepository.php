<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserSearch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserSearch|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserSearch|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserSearch[]    findAll()
 * @method UserSearch[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserSearchRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserSearch::class);
    }

     /**
      * @return UserSearch[] Returns an array of UserSearch objects
      */
    public function getLast(User $user)
    {
        return $this->createQueryBuilder('us')
            ->andWhere('us.user = :user')
            ->andWhere('(us.occupation IS NOT NULL OR us.skill IS NOT NULL)')
            ->setParameter('user', $user)
            ->orderBy('us.createdAt', 'DESC')
            ->addGroupBy('us.occupation')
            ->addGroupBy('us.skill')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult()
        ;
    }

    /*
    public function findOneBySomeField($value): ?UserSearch
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
