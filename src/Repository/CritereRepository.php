<?php

namespace App\Repository;

use App\Entity\Critere;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;

/**
 * @extends ServiceEntityRepository<Critere>
 */
class CritereRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Critere::class);
    }

    //    /**
    //     * @return Critere[] Returns an array of Critere objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    public function findByCreatedBy(User $user): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.user = :user')  // Adapter le nom du champ si différent (creator, author, etc.)
            ->setParameter('user', $user)
            ->orderBy('c.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    //    public function findOneBySomeField($value): ?Critere
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
