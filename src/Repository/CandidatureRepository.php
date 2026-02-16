<?php

namespace App\Repository;

use App\Entity\Candidature;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;

/**
 * @extends ServiceEntityRepository<Candidature>
 */
class CandidatureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Candidature::class);
    }

    /**
     * Récupère le dernier numéro de candidature pour une année donnée
     */
    public function getLastCandidatureNumberOfYear(string $year): int
    {
        $conn = $this->getEntityManager()->getConnection();
        
        $sql = "SELECT MAX(CAST(SUBSTRING(c.num_candidature, 8) AS UNSIGNED)) as max_num 
                FROM candidature c 
                WHERE c.num_candidature LIKE :pattern";
                
        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery(['pattern' => 'CAND' . $year . '%']);
        
        $maxNum = $result->fetchOne();
        
        return $maxNum ? (int)$maxNum : 0;
    }
    
    public function saveWithAutoNumber(Candidature $candidature): void
    {
        if (null === $candidature->getNumCandidature() || str_starts_with($candidature->getNumCandidature(), 'TEMP_')) {
            $year = date('y');
            $lastNumber = $this->getLastCandidatureNumberOfYear($year);
            $nextSequential = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
            
            $candidature->setNumCandidature("CAND{$year}{$nextSequential}");
        }
        
        $this->getEntityManager()->persist($candidature);
        $this->getEntityManager()->flush();
    }

//    public function findOneBySomeField($value): ?Candidature
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }


// Filtres des candidatures 

public function findWithFilters(?string $type = null, ?string $nom = null): array
{
    $qb = $this->createQueryBuilder('c');

    if ($type) {
        switch ($type) {
            case 'recrutement':
                $qb->andWhere('c.recrutement IS NOT NULL');
                break;
            case 'formation':
                $qb->andWhere('c.formation IS NOT NULL');
                break;
            case 'vae':
                $qb->andWhere('c.vae IS NOT NULL');
                break;
        }
    }

    if ($nom) {
        $qb->leftJoin('c.recrutement', 'r')
           ->leftJoin('c.formation', 'f')
           ->leftJoin('c.vae', 'v')
           ->andWhere('r.libelle LIKE :nom OR f.libelle LIKE :nom OR v.nom LIKE :nom')
           ->setParameter('nom', '%' . $nom . '%');
    }

    return $qb->orderBy('c.dateCandidature', 'DESC')
              ->getQuery()
              ->getResult();
}

public function findByUserWithFilters(User $user, ?string $type = null, ?string $nom = null): array
{
    $qb = $this->createQueryBuilder('c')
        ->andWhere('c.user = :user')
        ->setParameter('user', $user);

    if ($type) {
        switch ($type) {
            case 'recrutement':
                $qb->andWhere('c.recrutement IS NOT NULL');
                break;
            case 'formation':
                $qb->andWhere('c.formation IS NOT NULL');
                break;
            case 'vae':
                $qb->andWhere('c.vae IS NOT NULL');
                break;
        }
    }

    if ($nom) {
        $qb->leftJoin('c.recrutement', 'r')
           ->leftJoin('c.formation', 'f')
           ->leftJoin('c.vae', 'v')
           ->andWhere('r.libelle LIKE :nom OR f.libelle LIKE :nom OR v.nom LIKE :nom')
           ->setParameter('nom', '%' . $nom . '%');
    }

    return $qb->orderBy('c.dateCandidature', 'DESC')
              ->getQuery()
              ->getResult();
}

public function findLatestByUser(User $user): ?Candidature
{
    return $this->createQueryBuilder('c')
        ->andWhere('c.user = :user')
        ->setParameter('user', $user)
        ->orderBy('c.dateCandidature', 'DESC')
        ->addOrderBy('c.id', 'DESC')
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult();
}

public function findAllRecrutements(): array
{
    return $this->createQueryBuilder('c')
        ->select('DISTINCT r.libelle, r.id')
        ->innerJoin('c.recrutement', 'r')
        ->where('c.recrutement IS NOT NULL')
        ->orderBy('r.libelle', 'ASC')
        ->getQuery()
        ->getResult();
}

public function findAllFormations(): array
{
    return $this->createQueryBuilder('c')
        ->select('DISTINCT f.libelle, f.id')
        ->innerJoin('c.formation', 'f')
        ->where('c.formation IS NOT NULL')
        ->orderBy('f.libelle', 'ASC')
        ->getQuery()
        ->getResult();
}

public function findAllVaes(): array
{
    return $this->createQueryBuilder('c')
        ->select('DISTINCT v.nom, v.id')
        ->innerJoin('c.vae', 'v')
        ->where('c.vae IS NOT NULL')
        ->orderBy('v.nom', 'ASC')
        ->getQuery()
        ->getResult();
}

    /**
     * Retourne les candidatures accessibles à un jury selon les recrutements/formations/VAE assignés
     *
     * @param int[] $recrutementIds
     * @param int[] $formationIds
     * @param int[] $vaeIds
     */
    public function findByAssignments(
        array $recrutementIds,
        array $formationIds,
        array $vaeIds,
        ?string $type = null,
        ?string $nom = null,
        ?string $juryStatus = null,
        ?string $juryEvaluationType = null
    ): array {
        if (empty($recrutementIds) && empty($formationIds) && empty($vaeIds)) {
            return [];
        }

        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.recrutement', 'r')
            ->leftJoin('c.formation', 'f')
            ->leftJoin('c.vae', 'v');

        $orConditions = [];

        if (!empty($recrutementIds)) {
            $orConditions[] = 'r.id IN (:recrutementIds)';
            $qb->setParameter('recrutementIds', $recrutementIds);
        }

        if (!empty($formationIds)) {
            $orConditions[] = 'f.id IN (:formationIds)';
            $qb->setParameter('formationIds', $formationIds);
        }

        if (!empty($vaeIds)) {
            $orConditions[] = 'v.id IN (:vaeIds)';
            $qb->setParameter('vaeIds', $vaeIds);
        }

        if ($orConditions) {
            $qb->andWhere(implode(' OR ', $orConditions));
        }

        if ($type) {
            switch ($type) {
                case 'recrutement':
                    $qb->andWhere('r.id IS NOT NULL');
                    break;
                case 'formation':
                    $qb->andWhere('f.id IS NOT NULL');
                    break;
                case 'vae':
                    $qb->andWhere('v.id IS NOT NULL');
                    break;
            }
        }

        if ($nom) {
            $qb->andWhere('r.libelle LIKE :nom OR f.libelle LIKE :nom OR v.nom LIKE :nom')
               ->setParameter('nom', '%' . $nom . '%');
        }

        if ($juryStatus) {
            $statusMap = [
                'accepted' => [
                    'dossier validé',
                    'valide',
                    'validé',
                    'accepté',
                    'accepte',
                    'acceptée',
                    'acceptee',
                ],
                'refused' => [
                    'dossier refusé',
                    'refuse',
                    'refusé',
                    'rejeté',
                    'rejete',
                ],
                'incomplete' => [
                    'incomplet',
                    'incomplete',
                ],
            ];

            $normalized = strtolower($juryStatus);
            if (isset($statusMap[$normalized])) {
                $paramName = sprintf('status_%s', $normalized);
                $qb->andWhere('LOWER(c.statut) IN (:'.$paramName.')')
                   ->setParameter($paramName, array_map('strtolower', $statusMap[$normalized]));
            }
        }

        if ($juryEvaluationType) {
            $qb->leftJoin('c.evaluation', 'juryEvalFilter')
               ->andWhere('LOWER(juryEvalFilter.libelle) = :juryEvaluationType')
               ->setParameter('juryEvaluationType', strtolower($juryEvaluationType));
        }

        return $qb->groupBy('c.id')
                  ->orderBy('c.dateCandidature', 'DESC')
                  ->getQuery()
                  ->getResult();
    }

    /**
     * Retourne des statistiques agrégées pour un jury (total/accepté/refusé/incomplet/en attente)
     */
    public function getAssignmentStats(
        array $recrutementIds,
        array $formationIds,
        array $vaeIds
    ): array {
        if (empty($recrutementIds) && empty($formationIds) && empty($vaeIds)) {
            return [
                'total' => 0,
                'accepted' => 0,
                'refused' => 0,
                'incomplete' => 0,
                'pending' => 0,
            ];
        }

        $acceptedStatuses = [
            'dossier validé',
            'valide',
            'validé',
            'validée',
            'accepte',
            'accepté',
            'acceptée',
            'acceptes',
            'accepted',
        ];

        $refusedStatuses = [
            'dossier refusé',
            'refuse',
            'refusé',
            'refusée',
            'rejeté',
            'rejete',
            'rejetée',
            'rejetes',
            'rejected',
        ];

        $incompleteStatuses = [
            'incomplet',
            'incomplète',
            'incomplete',
            'dossier incomplet',
        ];

        $qb = $this->createQueryBuilder('c')
            ->select('COUNT(DISTINCT c.id) AS total')
            ->addSelect('COALESCE(SUM(CASE WHEN LOWER(c.statut) IN (:acceptedStatuses) THEN 1 ELSE 0 END), 0) AS accepted')
            ->addSelect('COALESCE(SUM(CASE WHEN LOWER(c.statut) IN (:refusedStatuses) THEN 1 ELSE 0 END), 0) AS refused')
            ->addSelect('COALESCE(SUM(CASE WHEN LOWER(c.statut) IN (:incompleteStatuses) THEN 1 ELSE 0 END), 0) AS incomplete')
            ->addSelect('COALESCE(SUM(CASE WHEN (LOWER(c.statut) NOT IN (:acceptedStatuses) AND LOWER(c.statut) NOT IN (:refusedStatuses) AND LOWER(c.statut) NOT IN (:incompleteStatuses)) OR c.statut IS NULL THEN 1 ELSE 0 END), 0) AS pending')
            ->leftJoin('c.recrutement', 'r')
            ->leftJoin('c.formation', 'f')
            ->leftJoin('c.vae', 'v');

        $orConditions = [];

        if (!empty($recrutementIds)) {
            $orConditions[] = 'r.id IN (:statsRecrutements)';
            $qb->setParameter('statsRecrutements', $recrutementIds);
        }

        if (!empty($formationIds)) {
            $orConditions[] = 'f.id IN (:statsFormations)';
            $qb->setParameter('statsFormations', $formationIds);
        }

        if (!empty($vaeIds)) {
            $orConditions[] = 'v.id IN (:statsVaes)';
            $qb->setParameter('statsVaes', $vaeIds);
        }

        if ($orConditions) {
            $qb->andWhere(implode(' OR ', $orConditions));
        }

        $stats = $qb
            ->setParameter('acceptedStatuses', array_map('strtolower', $acceptedStatuses))
            ->setParameter('refusedStatuses', array_map('strtolower', $refusedStatuses))
            ->setParameter('incompleteStatuses', array_map('strtolower', $incompleteStatuses))
            ->getQuery()
            ->getSingleResult();

        return array_map(static fn ($value) => (int) $value, $stats);
    }

    public function getDashboardCountsByRecrutement(?int $recrutementId = null): array
    {
        $acceptedStatuses = [
            'dossier validé',
            'valide',
            'validé',
            'validée',
            'accepte',
            'accepté',
            'acceptée',
            'accepted',
        ];

        $rejectedStatuses = [
            'dossier refusé',
            'refuse',
            'refusé',
            'refusée',
            'rejeté',
            'rejete',
            'rejetée',
            'rejected',
        ];

        $qb = $this->createQueryBuilder('c')
            ->select('COUNT(DISTINCT c.id) AS total')
            ->addSelect('COALESCE(SUM(CASE WHEN LOWER(c.statut) IN (:acceptedStatuses) THEN 1 ELSE 0 END), 0) AS admitted')
            ->addSelect('COALESCE(SUM(CASE WHEN LOWER(c.statut) IN (:rejectedStatuses) THEN 1 ELSE 0 END), 0) AS rejected')
            ->leftJoin('c.recrutement', 'r');

        if ($recrutementId) {
            $qb->andWhere('r.id = :recrutementId')
                ->setParameter('recrutementId', $recrutementId);
        }

        $stats = $qb
            ->setParameter('acceptedStatuses', array_map('strtolower', $acceptedStatuses))
            ->setParameter('rejectedStatuses', array_map('strtolower', $rejectedStatuses))
            ->getQuery()
            ->getSingleResult();

        $total = (int) ($stats['total'] ?? 0);
        $admitted = (int) ($stats['admitted'] ?? 0);
        $rejected = (int) ($stats['rejected'] ?? 0);

        return [
            'total' => $total,
            'admitted' => $admitted,
            'rejected' => $rejected,
            'pending' => max(0, $total - $admitted - $rejected),
        ];
    }

    public function getAdmittedByEvaluationTypeForDashboard(?int $recrutementId = null): array
    {
        $acceptedStatuses = [
            'dossier validé',
            'valide',
            'validé',
            'validée',
            'accepte',
            'accepté',
            'acceptée',
            'accepted',
        ];

        $qb = $this->createQueryBuilder('c')
            ->select('COALESCE(te.libelle, e.libelle) AS type_evaluation')
            ->addSelect('COUNT(DISTINCT c.id) AS admitted_count')
            ->innerJoin('c.evaluation', 'e')
            ->leftJoin('e.typeEvaluation', 'te')
            ->leftJoin('c.recrutement', 'r')
            ->andWhere('LOWER(e.statut) IN (:acceptedStatuses)')
            ->setParameter('acceptedStatuses', array_map('strtolower', $acceptedStatuses))
            ->groupBy('type_evaluation')
            ->orderBy('type_evaluation', 'ASC');

        if ($recrutementId) {
            $qb->andWhere('r.id = :recrutementId')
                ->setParameter('recrutementId', $recrutementId);
        }

        return $qb->getQuery()->getArrayResult();
    }
}
