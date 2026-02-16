<?php

namespace App\Repository;

use App\Entity\Metier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Recrutement;

/**
 * @extends ServiceEntityRepository<Metier>
 */
class MetierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Metier::class);
    }

 
  /**
     * Récupère les métiers associés à un recrutement donné
     */
       public function findByRecrutement(?Recrutement $recrutement)
    {
        $qb = $this->createQueryBuilder('m')
            ->orderBy('m.nom', 'ASC');

        if ($recrutement) {
            $qb->innerJoin('m.recrutements', 'r')
               ->where('r.id = :recrutement_id')
               ->setParameter('recrutement_id', $recrutement->getId());
        }

        return $qb->getQuery()->getResult();
    }

//    /**
//     * @return Metier[] Returns an array of Metier objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Metier
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
