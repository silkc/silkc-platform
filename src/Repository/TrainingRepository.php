<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Occupation;
use App\Entity\OccupationSkill;
use App\Entity\Training;
use App\Entity\Skill;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Training|null find($id, $lockMode = null, $lockVersion = null)
 * @method Training|null findOneBy(array $criteria, array $orderBy = null)
 * @method Training[]    findAll()
 * @method Training[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrainingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Training::class);
    }

    /**
     * Recherche de formation par métier
     *
     * @access public
     */
    public function searchTrainingByOccupation(?User $user = null, Occupation $occupation): ?array
    {
        $entityManager = $this->getEntityManager();
        $rsm = new ResultSetMappingBuilder($entityManager);
        $rsm->addRootEntityFromClassMetadata('App\Entity\Training', 't');
        /*$rsm->addFieldResult('t', 'score', 'score');
        $rsm->addScalarResult('knowledge_coeff', 'knowledge_coeff');
        $rsm->addScalarResult('knowledge_optional_coeff', 'knowledge_optional_coeff');
        $rsm->addScalarResult('skill_coeff', 'skill_coeff');
        $rsm->addScalarResult('skill_optional_coeff', 'skill_optional_coeff');
        $rsm->addScalarResult('skill_weight', 'skill_weight');
        $rsm->addScalarResult('training_completion', 'training_completion');
        $rsm->addScalarResult('institution_completion', 'institution_completion');
        $rsm->addScalarResult('occupation_weight', 'occupation_weight');
        $rsm->addScalarResult('acquired_skill_coefficient', 'acquired_skill_coefficient');
        $rsm->addScalarResult('not_acquired_skill_coefficient', 'not_acquired_skill_coefficient');*/

        $query = $this->getEntityManager()->createNativeQuery(" 
            SELECT 
                t.*,
                IFNULL(sq1.weight, 0) AS skill_weight,
                IFNULL(sq2.trainingCompletion, 0) AS training_completion,
                IFNULL(sq2.institutionCompletion, 0) AS institution_completion,
                IFNULL(sq2.occupationWeight, 0) AS occupation_weight,
                IFNULL(sq1.knowledgeCoeff, 0) AS knowledge_coeff,
                IFNULL(sq1.knowledgeOptionalCoeff, 0) AS knowledge_optional_coeff,
                IFNULL(sq1.skillCoeff, 0) AS skill_coeff,
                IFNULL(sq1.skillOptionalCoeff, 0) AS skill_optional_coeff,
                IFNULL(sq3.acquiredSkillCoefficient, 0) AS acquired_skill_coefficient,
                IFNULL(sq3.notAcquiredSkillCoefficient, 0) AS not_acquired_skill_coefficient,
                (
                    IFNULL(CAST(sq1.weight AS UNSIGNED), 0) + 
                    IFNULL(CAST(sq2.occupationWeight AS UNSIGNED), 0) +
                    IFNULL(CAST(sq3.acquiredSkillCoefficient AS UNSIGNED), 0) -
                    IFNULL(CAST(sq3.notAcquiredSkillCoefficient AS UNSIGNED), 0)
                ) 
                    * CAST(sq2.institutionCompletion AS UNSIGNED) 
                    * CAST(sq2.trainingCompletion AS UNSIGNED) 
                    * 0.001
                AS score
            FROM training AS t
            LEFT JOIN (
                SELECT 
                    ssq1.training_id,
                    ssq1.knowledgeCoeff,
                    ssq1.knowledgeOptionalCoeff,
                    ssq1.skillCoeff,
                    ssq1.skillOptionalCoeff,
                    CAST(ssq1.knowledgeCoeff AS UNSIGNED) + CAST(ssq1.knowledgeOptionalCoeff AS UNSIGNED) + CAST(ssq1.skillCoeff AS UNSIGNED) + CAST(ssq1.skillOptionalCoeff AS UNSIGNED) AS weight
                FROM (
                    SELECT
                        t.id AS training_id,
                        SUM(IF(os.relation_type = :essentialRelationType AND os.skill_type = :knowledgeSkillType, :knowledgeCoefficient, 0)) AS knowledgeCoeff,
                        SUM(IF(os.relation_type = :optionalRelationType AND os.skill_type = :knowledgeSkillType, :knowledgeOptionalCoefficient, 0)) AS knowledgeOptionalCoeff,
                        SUM(IF(os.relation_type = :essentialRelationType AND os.skill_type = :skillSkillType, :skillCoefficient, 0)) AS skillCoeff,
                        SUM(IF(os.relation_type = :optionalRelationType AND os.skill_type = :skillSkillType, :skillOptionalCoefficient, 0)) AS skillOptionalCoeff
                    FROM training t
                    INNER JOIN training_skill ts ON ts.training_id = t.id AND ts.is_to_acquire = 1
                    INNER JOIN occupation_skill os ON os.occupation_id = :occupationId AND os.skill_id = ts.skill_id
                    GROUP BY t.id
                ) AS ssq1
                GROUP BY ssq1.training_id
            ) AS sq1 ON sq1.training_id = t.id
            LEFT JOIN (
                SELECT
                    t.id AS training_id,
                    t.completion AS trainingCompletion,
                    i.completion AS institutionCompletion,
                    IF (t.occupation_id IS NOT NULL, :occupationCoefficient, 0) AS occupationWeight
                FROM training t
                INNER JOIN user i ON i.id = t.user_id
                GROUP BY t.id
            ) AS sq2 ON sq2.training_id = t.id
            LEFT JOIN (
                SELECT 
                    t.id AS training_id,
                    SUM(IF(us.id IS NOT NULL, 1, 0)) * :acquiredCoefficient AS acquiredSkillCoefficient,
                    SUM(IF(us.id IS NULL, 1, 0)) * :notAcquiredCoefficient AS notAcquiredSkillCoefficient
                FROM training t
                    INNER JOIN training_skill ts ON ts.training_id = t.id AND ts.is_required = 1
                    LEFT JOIN user_skill us ON us.skill_id = ts.skill_id AND us.is_selected = 1 AND us.user_id = :userId
                    GROUP BY t.id
            ) AS sq3 ON sq3.training_id = t.id
            GROUP BY t.id
            HAVING score IS NOT NULL AND score > 0
            ORDER BY score DESC
            ", $rsm);
        $query->setParameter('occupationId', $occupation->getId());
        $query->setParameter('userId', ($user) ? $user->getId() : NULL);
        $query->setParameter('essentialRelationType', OccupationSkill::RELATION_TYPE_ESSENTIAL);
        $query->setParameter('optionalRelationType', OccupationSkill::RELATION_TYPE_OPTIONAL);
        $query->setParameter('knowledgeSkillType', OccupationSkill::SKILL_TYPE_KNOWLEDGE);
        $query->setParameter('skillSkillType', OccupationSkill::SKILL_TYPE_SKILL);
        $query->setParameter('occupationCoefficient', Training::SEARCH_OCCUPATION_COEFFICIENT);
        $query->setParameter('skillCoefficient', Training::SEARCH_SKILL_COEFFICIENT);
        $query->setParameter('skillOptionalCoefficient', Training::SEARCH_OPTIONAL_SKILL_COEFFICIENT);
        $query->setParameter('knowledgeCoefficient', Training::SEARCH_KNOWLEDGE_COEFFICIENT);
        $query->setParameter('knowledgeOptionalCoefficient', Training::SEARCH_OPTIONAL_KNOWLEDGE_COEFFICIENT);
        $query->setParameter('acquiredCoefficient', Training::SEARCH_ACQUIRED_REQUIRED_SKILL_COEFFICIENT);
        $query->setParameter('notAcquiredCoefficient', Training::SEARCH_NOT_ACQUIRED_REQUIRED_SKILL_COEFFICIENT);

        return $query->getResult();
    }
    /*public function searchTrainingByOccupation(Occupation $occupation): ?array
    {
        $query = $this->getEntityManager()->createNativeQuery("
            SELECT
                t.*,
                CAST(sq1.weight AS UNSIGNED) + CAST(sq2.occupationWeight AS UNSIGNED) AS score
            FROM training AS t
            LEFT JOIN (
                SELECT
                    ssq1.training_id,
                    CAST(ssq1.knowledgeCoeff AS UNSIGNED) + CAST(ssq1.knowledgeOptionalCoeff AS UNSIGNED) + CAST(ssq1.skillCoeff AS UNSIGNED) + CAST(ssq1.skillOptionalCoeff AS UNSIGNED) AS weight
                FROM (
                    SELECT
                        ANY_VALUE(t.id) AS training_id,
                        SUM(IF(os.relation_type = :essentialRelationType AND os.skill_type = :knowledgeSkillType, :knowledgeCoefficient, 0)) AS knowledgeCoeff,
                        SUM(IF(os.relation_type = :optionalRelationType AND os.skill_type = :knowledgeSkillType, :knowledgeOptionalCoefficient, 0)) AS knowledgeOptionalCoeff,
                        SUM(IF(os.relation_type = :essentialRelationType AND os.skill_type = :skillSkillType, :skillCoefficient, 0)) AS skillCoeff,
                        SUM(IF(os.relation_type = :optionalRelationType AND os.skill_type = :skillSkillType, :skillOptionalCoefficient, 0)) AS skillOptionalCoeff
                    FROM training t
                    INNER JOIN occupation_skill os ON os.occupation_id = :occupationId
                    INNER JOIN training_skill ts ON ts.training_id = t.id AND os.skill_id = ts.skill_id
                    GROUP BY t.id
                ) AS ssq1
                GROUP BY ssq1.training_id
            ) AS sq1 ON sq1.training_id = t.id
            LEFT JOIN (
                SELECT
                    ANY_VALUE(t.id) AS training_id,
                    IF (toc.training_id IS NOT NULL, :occupationCoefficient, 1) AS occupationWeight
                FROM training t
                LEFT JOIN training_occupation toc ON toc.training_id = t.id AND toc.occupation_id = :occupationId
                GROUP BY t.id
            ) AS sq2 ON sq2.training_id = t.id
            GROUP BY t.id
            HAVING score IS NOT NULL AND score > 0
            ORDER BY score DESC
            ", $rsm);
        $query->setParameter('occupationId', $occupation->getId());
        $query->setParameter('essentialRelationType', OccupationSkill::RELATION_TYPE_ESSENTIAL);
        $query->setParameter('optionalRelationType', OccupationSkill::RELATION_TYPE_OPTIONAL);
        $query->setParameter('knowledgeSkillType', OccupationSkill::SKILL_TYPE_KNOWLEDGE);
        $query->setParameter('skillSkillType', OccupationSkill::SKILL_TYPE_SKILL);
        $query->setParameter('occupationCoefficient', Training::SEARCH_OCCUPATION_COEFFICIENT);
        $query->setParameter('skillCoefficient', Training::SEARCH_SKILL_COEFFICIENT);
        $query->setParameter('skillOptionalCoefficient', Training::SEARCH_OPTIONAL_SKILL_COEFFICIENT);
        $query->setParameter('knowledgeCoefficient', Training::SEARCH_KNOWLEDGE_COEFFICIENT);
        $query->setParameter('knowledgeOptionalCoefficient', Training::SEARCH_OPTIONAL_KNOWLEDGE_COEFFICIENT);

        return $query->getResult();
    }*/

    /**
     * Recherche de formation par compétence
     *
     * @access public
     */
    public function searchTrainingBySkill(Skill $skill): ?array
    {
        $entityManager = $this->getEntityManager();
        $rsm = new ResultSetMappingBuilder($entityManager);
        $rsm->addRootEntityFromClassMetadata('App\Entity\Training', 't');

        $query = $this->getEntityManager()->createNativeQuery(" 
            SELECT
                t.*
            FROM training t
            INNER JOIN training_skill ts ON ts.training_id = t.id AND ts.skill_id = :skillId
            GROUP BY t.id
            ", $rsm);
        $query->setParameter('skillId', $skill->getId());

        return $query->getResult();
    }

    // /**
    //  * @return Training[] Returns an array of Training objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Training
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
