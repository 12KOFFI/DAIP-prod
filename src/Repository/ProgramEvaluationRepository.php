<?php

namespace App\Repository;

use App\Entity\ProgramEvaluation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProgramEvaluation>
 *
 * @method ProgramEvaluation|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProgramEvaluation|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProgramEvaluation[]    findAll()
 * @method ProgramEvaluation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProgramEvaluationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProgramEvaluation::class);
    }

    public function save(ProgramEvaluation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ProgramEvaluation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}