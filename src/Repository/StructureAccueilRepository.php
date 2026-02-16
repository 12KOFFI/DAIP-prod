<?php

namespace App\Repository;

use App\Entity\StructureAccueil;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StructureAccueil>
 */
class StructureAccueilRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StructureAccueil::class);
    }

    /**
     * @return StructureAccueil[]
     */
    public function findByCfaEtablissement(int $cfaId): array
    {
        return $this->createQueryBuilder('s')
            ->innerJoin('s.prospections', 'p')
            ->innerJoin('p.cfaEtablissement', 'cfa')
            ->andWhere('cfa.id = :cfaId')
            ->setParameter('cfaId', $cfaId)
            ->orderBy('s.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return StructureAccueil[] Returns an array of StructureAccueil objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?StructureAccueil
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
