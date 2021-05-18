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
    public function searchTrainingByOccupation(User $user, Occupation $occupation): ?array
    {
        $entityManager = $this->getEntityManager();
        $rsm = new ResultSetMappingBuilder($entityManager);
        $rsm->addRootEntityFromClassMetadata('App\Entity\Training', 't');
        $rsm->addFieldResult('t', 'score', 'score');
        /*$rsm->addScalarResult('total_weight', 'total_weight');
        $rsm->addScalarResult('weight', 'weight');
        $rsm->addScalarResult('occupationWeight', 'occupationWeight');
        $rsm->addScalarResult('knowledgeCoeff', 'knowledgeCoeff');
        $rsm->addScalarResult('knowledgeOptionalCoeff', 'knowledgeOptionalCoeff');
        $rsm->addScalarResult('skillCoeff', 'skillCoeff');
        $rsm->addScalarResult('skillOptionalCoeff', 'skillOptionalCoeff');*/

        $query = $this->getEntityManager()->createNativeQuery(" 
            SELECT 
                t.*,
                (
                    CAST(sq1.weight AS UNSIGNED) + 
                    CAST(sq2.occupationWeight AS UNSIGNED) 
                ) 
                    * CAST(sq2.institutionCompletion AS UNSIGNED) 
                    * CAST(sq2.trainingCompletion AS UNSIGNED) 
                AS score
            FROM training AS t
            LEFT JOIN (
                SELECT 
                    ssq1.training_id,
                    CAST(ssq1.knowledgeCoeff AS UNSIGNED) + CAST(ssq1.knowledgeOptionalCoeff AS UNSIGNED) + CAST(ssq1.skillCoeff AS UNSIGNED) + CAST(ssq1.skillOptionalCoeff AS UNSIGNED) AS weight
                FROM (
                    SELECT
                        t.id AS training_id,
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
                    t.id AS training_id,
                    t.completion AS trainingCompletion,
                    i.completion AS institutionCompletion,
                    IF (t.occupation_id IS NOT NULL, :occupationCoefficient, 1) AS occupationWeight
                FROM training t
                INNER JOIN user i ON i.id = t.user_id
                GROUP BY t.id
            ) AS sq2 ON sq2.training_id = t.id
            GROUP BY t.id
            HAVING score IS NOT NULL AND score > 0
            ORDER BY score DESC
            ", $rsm);
        $query->setParameter('occupationId', $occupation->getId());
        //$query->setParameter('userId', $user->getId());
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
