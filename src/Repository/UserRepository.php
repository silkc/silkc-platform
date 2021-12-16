<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Retourn un utilisateur par son adresse e-mail ou son nom d'usage
     * @param $value
     * @return User|null
     */
    public function findOneByEmailOrUsername($username)
    {
        return $this->createQueryBuilder('u')
            ->where('u.username = :username')
            ->orWhere('u.email = :username')
            ->setParameter('username', $username)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByRole(string $role)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.roles LIKE :role')
            ->setParameter('role', '%"' . $role . '"%')
            ->getQuery()
            ->getResult();
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    public function searchAffectedUsers(array $skills)
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult( 'all_user_id', 'all_user_id');
        $rsm->addScalarResult( 'count_all', 'count_all');
        $rsm->addScalarResult( 'count_listening', 'count_listening');

        $query = $this->getEntityManager()
            ->createNativeQuery('
                SELECT 
                    GROUP_CONCAT(ssq.user_id) AS all_user_id,
                    COUNT(ssq.user_id) AS count_all,
                    IFNULL(SUM(IF(u.is_listening_position = 1, 1, 0)), 0) AS count_listening
                FROM (
                    SELECT 
                        sq.user_id,
                        COUNT(DISTINCT(sq.skill_id)) AS count_skills
                    FROM (
                        SELECT
                            us.user_id,
                            us.skill_id
                        FROM
                            user_skill AS us
                        WHERE
                            us.skill_id IN(:skillIDs) AND us.is_selected = 1
                
                        UNION
                
                        SELECT
                            ut.user_id,
                            ts.skill_id
                        FROM
                            training_skill AS ts
                        INNER JOIN user_training AS ut ON ts.training_id = ut.training_id 
                        WHERE ts.skill_id IN (:skillIDs)
                        GROUP BY ut.user_id, ts.skill_id
                        
                        UNION
                
                        SELECT
                            uo.user_id,
                            os.skill_id
                        FROM
                            occupation_skill AS os
                        INNER JOIN user_occupation AS uo ON uo.occupation_id = os.occupation_id
                        WHERE os.skill_id IN (:skillIDs)
                        GROUP BY uo.user_id, os.skill_id
                    ) AS sq
                    GROUP BY sq.user_id
                    HAVING count_skills >= :countRequiredSkills
                ) AS ssq
                INNER JOIN user AS u ON u.id = ssq.user_id
            ', $rsm)
            ->setParameter('skillIDs', $skills)
            ->setParameter('countRequiredSkills', count($skills));

        $result = $query->getOneOrNullResult();

        return $result;
    }

    public function fetchAffectedUsers(array $skills)
    {
        $entityManager = $this->getEntityManager();
        $rsm = new ResultSetMappingBuilder($entityManager);
        $rsm->addRootEntityFromClassMetadata('App\Entity\User', 'u');

        $query = $this->getEntityManager()
            ->createNativeQuery('
                SELECT 
                    u.*
                FROM (
                    SELECT 
                        sq.user_id,
                        COUNT(DISTINCT(sq.skill_id)) AS count_skills
                    FROM (
                        SELECT
                            us.user_id,
                            us.skill_id
                        FROM
                            user_skill AS us
                        WHERE
                            us.skill_id IN(:skillIDs) AND us.is_selected = 1
                
                        UNION
                
                        SELECT
                            ut.user_id,
                            ts.skill_id
                        FROM
                            training_skill AS ts
                        INNER JOIN user_training AS ut ON ts.training_id = ut.training_id 
                        WHERE ts.skill_id IN (:skillIDs)
                        GROUP BY ut.user_id, ts.skill_id
                        
                        UNION
                
                        SELECT
                            uo.user_id,
                            os.skill_id
                        FROM
                            occupation_skill AS os
                        INNER JOIN user_occupation AS uo ON uo.occupation_id = os.occupation_id
                        WHERE os.skill_id IN (:skillIDs)
                        GROUP BY uo.user_id, os.skill_id
                    ) AS sq
                    GROUP BY sq.user_id
                    HAVING count_skills >= :countRequiredSkills
                ) AS ssq
                INNER JOIN user AS u ON u.id = ssq.user_id
            ', $rsm)
            ->setParameter('skillIDs', $skills)
            ->setParameter('countRequiredSkills', count($skills));

        $result = $query->getResult();

        return $result;
    }
}
