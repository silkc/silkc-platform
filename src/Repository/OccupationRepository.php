<?php

namespace App\Repository;

use App\Entity\Occupation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Occupation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Occupation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Occupation[]    findAll()
 * @method Occupation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OccupationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Occupation::class);
    }

    public function findAll()
    {
        return $this->findBy([], ['preferredLabel' => 'ASC']);
    }

    public function findAllByLocale(string $locale)
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult( 'id', 'id');
        $rsm->addScalarResult( 'preferred_label', 'preferred_label');

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

    /*public function findAllByLocale2(string $locale)
    {
        $entityManager = $this->getEntityManager();
        $rsm = new ResultSetMappingBuilder($entityManager);
        $rsm->addRootEntityFromClassMetadata('App\Entity\Occupation', 'o');
        $query = $entityManager
            ->createNativeQuery("
                SELECT
                    o.id,
                    ot.preferred_label
                FROM occupation AS o
                LEFT JOIN occupation_translation AS ot ON ot.occupation_id = o.id AND ot.locale = :locale
                GROUP BY o.id
            ", $rsm)
            ->setParameter('locale', $locale);

        return $query->getResult();
    }

    public function findAllByLocale3(string $locale)
    {
        $queryBuilder = $this->createQueryBuilder('o')
            ->addSelect('o.id')
            ->addSelect('ot.preferredLabel')
            ->leftJoin('o.translations', 'ot', 'WITH', 'ot.locale=:locale')
            ->groupBy('o.id')
            ->setParameter('locale', $locale);

        $query = $queryBuilder->getQuery();

        return $query->getResult();
    }*/
}
