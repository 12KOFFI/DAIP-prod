<?php

namespace App\Repository;

use App\Entity\TypeEvaluation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TypeEvaluation>
 *
 * @method TypeEvaluation|null find($id, $lockMode = null, $lockVersion = null)
 * @method TypeEvaluation|null findOneBy(array $criteria, array $orderBy = null)
 * @method TypeEvaluation[]    findAll()
 * @method TypeEvaluation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TypeEvaluationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypeEvaluation::class);
    }

    public function save(TypeEvaluation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TypeEvaluation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}