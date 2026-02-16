<?php

namespace App\Repository;

use App\Entity\ProspectionMetier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProspectionMetier>
 */
class ProspectionMetierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProspectionMetier::class);
    }

    /**
     * @return ProspectionMetier[]
     */
    public function findByCfaEtablissement(int $cfaId): array
    {
        return $this->createQueryBuilder('pm')
            ->innerJoin('pm.prospection', 'p')
            ->innerJoin('p.cfaEtablissement', 'cfa')
            ->andWhere('cfa.id = :cfaId')
            ->setParameter('cfaId', $cfaId)
            ->orderBy('pm.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Calcule la somme des postes pour une prospection donnée
     * @param int $prospectionId
     * @param int|null $excludeProspectionMetierId ID à exclure du calcul (pour l'édition)
     * @return int
     */
    public function getSumPostesByProspection(int $prospectionId, ?int $excludeProspectionMetierId = null): int
    {
        $qb = $this->createQueryBuilder('pm')
            ->select('COALESCE(SUM(pm.nombre_postes), 0)')
            ->where('pm.prospection = :prospectionId')
            ->setParameter('prospectionId', $prospectionId);

        if ($excludeProspectionMetierId !== null) {
            $qb->andWhere('pm.id != :excludeId')
                ->setParameter('excludeId', $excludeProspectionMetierId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

//    /**
//     * @return ProspectionMetier[] Returns an array of ProspectionMetier objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ProspectionMetier
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
