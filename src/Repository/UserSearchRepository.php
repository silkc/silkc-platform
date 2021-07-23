<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Occupation;
use App\Entity\Skill;
use App\Entity\UserSearch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
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
            ->andWhere('us.isActive = 1')
            ->andWhere('(us.occupation IS NOT NULL OR us.skill IS NOT NULL)')
            ->setParameter('user', $user)
            ->orderBy('MAX(us.createdAt)', 'DESC')
            ->addGroupBy('us.occupation')
            ->addGroupBy('us.skill')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return UserSearch[] Returns an array of UserSearch objects
     */
    /*public function getAll(User $user)
    {
        return $this->createQueryBuilder('us')
            ->select('us')
            ->addSelect('COUNT(us.id) AS countSearches')
            ->andWhere('us.user = :user')
            ->andWhere('(us.occupation IS NOT NULL OR us.skill IS NOT NULL)')
            ->setParameter('user', $user)
            ->orderBy('us.createdAt', 'DESC')
            ->addGroupBy('us.occupation')
            ->addGroupBy('us.skill')
            ->getQuery()
            ->getResult();
    }*/

    public function getAll(User $user)
    {
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult('App\Entity\UserSearch', 'us');
        $rsm->addJoinedEntityResult('App\Entity\User', 'u', 'us', 'user');
        $rsm->addJoinedEntityResult('App\Entity\Occupation', 'o', 'us', 'occupation');
        $rsm->addJoinedEntityResult('App\Entity\Skill', 's', 'us', 'skill');
        $rsm->addFieldResult('us', 'id', 'id');
        $rsm->addFieldResult('us', 'created_at', 'createdAt');
        $rsm->addFieldResult('us', 'count_results', 'countResults');
        $rsm->addFieldResult('us', 'count_searches', 'countSearches');
        $rsm->addFieldResult('u', 'u_id', 'id');
        $rsm->addFieldResult('u', 'u_firstname', 'firstname');
        $rsm->addFieldResult('u', 'u_lastname', 'lastname');
        $rsm->addFieldResult('o', 'o_id', 'id');
        $rsm->addFieldResult('o', 'o_preferred_label', 'preferredLabel');
        $rsm->addFieldResult('s', 's_id', 'id');
        $rsm->addFieldResult('s', 's_preferred_label', 'preferredLabel');


        $query = $this->getEntityManager()->createNativeQuery('
                SELECT
                    us.id,
                    us.user_id,
                    us.occupation_id,
                    us.skill_id,
                    us.created_at,
                    us.count_results,
                    COUNT(DISTINCT(us.id)) AS count_searches,
                    u.id AS u_id,
                    u.firstname AS u_firstname,
                    u.lastname AS u_lastname,
                    o.id AS o_id,
                    o.preferred_label AS o_preferred_label,
                    s.id AS s_id,
                    s.preferred_label AS s_preferred_label
                FROM user_search us
                LEFT JOIN user u ON u.id = us.user_id
                LEFT JOIN occupation o ON o.id = us.occupation_id
                LEFT JOIN skill s ON s.id = us.skill_id     
                WHERE us.is_active = 1 AND us.user_id = :userId AND (us.skill_id IS NOT NULL OR us.occupation_id IS NOT NULL)
                GROUP BY us.occupation_id, us.skill_id
                ORDER BY MAX(us.created_at) DESC
            ', $rsm);
        $query->setParameter('userId', $user->getId());

        return $query->getResult();
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
