<?php

namespace App\Repository;

use App\Entity\Skill;
use App\Entity\User;
use App\Entity\OccupationSkill;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

/**
 * @method Skill|null find($id, $lockMode = null, $lockVersion = null)
 * @method Skill|null findOneBy(array $criteria, array $orderBy = null)
 * @method Skill[]    findAll()
 * @method Skill[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SkillRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Skill::class);
    }

    public function findAllByLocale(string $locale)
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult( 'id', 'id');
        $rsm->addScalarResult( 'preferred_label', 'preferredLabel');

        $query = $this->getEntityManager()
            ->createNativeQuery('
                SELECT 
                    o.id,
                    ot.preferred_label 
                FROM 
                     occupation AS o
                INNER JOIN occupation_translation AS ot ON ot.occupation_id = o.id AND locale = :locale
                GROUP BY o.id
            ', $rsm)
            ->setParameter('locale', $locale);

        $result = $query->getScalarResult();

        return $result;
    }

    public function getByOccupationAndTraining(User $user, $isValidated = true)
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('s_id', 'skillId');
        $rsm->addScalarResult('s_preferred_label', 'skillPreferredLabel');
        $rsm->addScalarResult('s_description', 'skillDescription');
        $rsm->addScalarResult('o_ids', 'occupationIds');
        $rsm->addScalarResult('o_preferred_labels', 'occupationPreferredLabels');
        $rsm->addScalarResult('t_ids', 'trainingIds');
        $rsm->addScalarResult('t_names', 'trainingNames');

        $query = $this->getEntityManager()->createNativeQuery("
            SELECT
                s_id,
                s_preferred_label,
                s_description,
                o_ids,
                o_preferred_labels,
                t_ids,
                t_names
            FROM (
                    SELECT
                        s.id AS s_id,
                        s.preferred_label AS s_preferred_label,
                        s.description AS s_description,
                        GROUP_CONCAT(o.id SEPARATOR ', ') AS o_ids,
                        GROUP_CONCAT(o.preferred_label SEPARATOR ', ') AS o_preferred_labels,
                        NULL AS t_ids,
                        NULL AS t_names
                    FROM skill s
                    INNER JOIN occupation_skill os ON os.skill_id = s.id AND os.relation_type = :essentialRelationType AND os.skill_type = :skillSkillType
                    INNER JOIN user_occupation uo ON uo.user_id = :user AND uo.occupation_id = os.occupation_id AND (uo.is_current = 1 OR uo.is_previous = 1)
                    INNER JOIN occupation o ON o.id = uo.occupation_id
                    GROUP BY s.id
                    ORDER BY s.id DESC
                ) AS sq1
            UNION
            SELECT
                s_id,
                s_preferred_label,
                s_description,
                o_ids,
                o_preferred_labels,
                t_ids,
                t_names
            FROM (
                SELECT
                        s.id AS s_id,
                        s.preferred_label AS s_preferred_label,
                        s.description AS s_description,
                        GROUP_CONCAT(t.id SEPARATOR ', ') AS t_ids,
                        GROUP_CONCAT(t.name SEPARATOR ', ') AS t_names,
                        NULL AS o_ids,
                        NULL AS o_preferred_labels
                    FROM skill s
                    INNER JOIN training_skill ts ON ts.skill_id = s.id AND ts.is_required = 1
                    INNER JOIN user_training ut ON ut.user_id = :user AND ut.training_id = ts.training_id
                    INNER JOIN training t ON t.id = ut.training_id
                    GROUP BY s.id
                    ORDER BY s.id DESC
            ) AS sq2
            ", $rsm);
        $query->setParameter('user', $user);
        $query->setParameter('essentialRelationType', OccupationSkill::RELATION_TYPE_ESSENTIAL);
        $query->setParameter('skillSkillType', OccupationSkill::SKILL_TYPE_SKILL);

        return $query->getResult();

    }
    /*public function getByOccupationAndTraining(User $user, $isValidated = true)
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('s_id', 'skillId');
        $rsm->addScalarResult('s_preferred_label', 'skillPreferredLabel');
        $rsm->addScalarResult('s_description', 'skillDescription');
        $rsm->addScalarResult('o_ids', 'occupationIds');
        $rsm->addScalarResult('o_preferred_labels', 'occupationPreferredLabels');
        $rsm->addScalarResult('t_ids', 'trainingIds');
        $rsm->addScalarResult('t_names', 'trainingNames');

        $query = $this->getEntityManager()->createNativeQuery("
            SELECT
                s_id,
                s_preferred_label,
                s_description,
                o_ids,
                o_preferred_labels,
                t_ids,
                t_names
            FROM (
                    SELECT
                        ANY_VALUE(s.id) AS s_id,
                        ANY_VALUE(s.preferred_label) AS s_preferred_label,
                        ANY_VALUE(s.description) AS s_description,
                        GROUP_CONCAT(o.id SEPARATOR ', ') AS o_ids,
                        GROUP_CONCAT(o.preferred_label SEPARATOR ', ') AS o_preferred_labels,
                        NULL AS t_ids,
                        NULL AS t_names
                    FROM skill s
                    INNER JOIN occupation_skill os ON os.skill_id = s.id AND os.relation_type = :essentialRelationType AND os.skill_type = :skillSkillType
                    INNER JOIN user_occupation uo ON uo.user_id = :user AND uo.occupation_id = os.occupation_id AND (uo.is_current = 1 OR uo.is_previous = 1)
                    INNER JOIN occupation o ON o.id = uo.occupation_id
                    GROUP BY s.id
                    ORDER BY s.id DESC
                ) AS sq1
            UNION
            SELECT
                s_id,
                s_preferred_label,
                s_description,
                o_ids,
                o_preferred_labels,
                t_ids,
                t_names
            FROM (
                SELECT
                        ANY_VALUE(s.id) AS s_id,
                        ANY_VALUE(s.preferred_label) AS s_preferred_label,
                        ANY_VALUE(s.description) AS s_description,
                        GROUP_CONCAT(t.id SEPARATOR ', ') AS t_ids,
                        GROUP_CONCAT(t.name SEPARATOR ', ') AS t_names,
                        NULL AS o_ids,
                        NULL AS o_preferred_labels
                    FROM skill s
                    INNER JOIN training_skill ts ON ts.skill_id = s.id AND ts.is_required = 1
                    INNER JOIN user_training ut ON ut.user_id = :user AND ut.training_id = ts.training_id
                    INNER JOIN training t ON t.id = ut.training_id
                    GROUP BY s.id
                    ORDER BY s.id DESC
            ) AS sq2
            ", $rsm);
        $query->setParameter('user', $user);
        $query->setParameter('essentialRelationType', OccupationSkill::RELATION_TYPE_ESSENTIAL);
        $query->setParameter('skillSkillType', OccupationSkill::SKILL_TYPE_SKILL);

        return $query->getResult();

    }*/
    // /**
    //  * @return Skill[] Returns an array of Skill objects
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
    public function findOneBySomeField($value): ?Skill
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
