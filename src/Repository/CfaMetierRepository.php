<?php

namespace App\Repository;

use App\Entity\CfaMetier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CfaMetier>
 */
class CfaMetierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CfaMetier::class);
    }

    /**
     * Récupère tous les métiers avec effectifs pour un établissement donné
     * @return CfaMetier[]
     */
    public function findByCfaEtablissement(int $cfaId): array
    {
        return $this->createQueryBuilder('cm')
            ->andWhere('cm.cfaEtablissement = :cfaId')
            ->setParameter('cfaId', $cfaId)
            ->orderBy('cm.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Calcule la somme totale des effectifs pour un établissement
     */
    public function getTotalEffectifByCfa(int $cfaId): int
    {
        return (int) $this->createQueryBuilder('cm')
            ->select('COALESCE(SUM(cm.effectif), 0)')
            ->andWhere('cm.cfaEtablissement = :cfaId')
            ->setParameter('cfaId', $cfaId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
