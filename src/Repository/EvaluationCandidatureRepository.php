<?php

namespace App\Repository;

use App\Entity\EvaluationCandidature;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EvaluationCandidature>
 */
class EvaluationCandidatureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EvaluationCandidature::class);
    }

//    /**
//     * @return EvaluationCandidature[] Returns an array of EvaluationCandidature objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

    /**
     * Récupère toutes les évaluations avec leurs relations
     *
     * @return EvaluationCandidature[]
     */
    public function findAllWithRelations(): array
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.candidature', 'c')
            ->addSelect('c')
            ->leftJoin('e.user', 'u')
            ->addSelect('u')
            ->getQuery()
            ->getResult();
    }
}
