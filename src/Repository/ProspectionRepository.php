<?php

namespace App\Repository;

use App\Entity\Prospection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DateTimeInterface;

/**
 * @extends ServiceEntityRepository<Prospection>
 */
class ProspectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Prospection::class);
    }

    private function applyDashboardFilters($qb, ?int $cfaId, ?DateTimeInterface $startDate, ?DateTimeInterface $endDate): void
    {
        if ($cfaId) {
            $qb->andWhere('cfa.id = :cfaId')
                ->setParameter('cfaId', $cfaId);
        }

        if ($startDate) {
            $qb->andWhere('p.date >= :startDate')
                ->setParameter('startDate', $startDate);
        }

        if ($endDate) {
            $qb->andWhere('p.date <= :endDate')
                ->setParameter('endDate', $endDate);
        }
    }

    public function getProspectionDashboardKpis(
        ?int $cfaId,
        ?DateTimeInterface $startDate,
        ?DateTimeInterface $endDate
    ): array {
        $qb = $this->createQueryBuilder('p')
            ->select('COUNT(DISTINCT s.id) AS entreprises')
            ->addSelect('COALESCE(SUM(pm.nombre_postes), 0) AS postes')
            ->leftJoin('p.structureAcceuil', 's')
            ->leftJoin('p.cfaEtablissement', 'cfa')
            ->leftJoin('p.prospectionMetiers', 'pm')
            ->leftJoin('pm.metier', 'm');

        $this->applyDashboardFilters($qb, $cfaId, $startDate, $endDate);

        $data = $qb->getQuery()->getSingleResult();

        return [
            'entreprises' => (int) ($data['entreprises'] ?? 0),
            'postes' => (int) ($data['postes'] ?? 0),
        ];
    }

    public function getPostesByEntreprise(
        ?int $cfaId,
        ?DateTimeInterface $startDate,
        ?DateTimeInterface $endDate,
        int $limit = 10
    ): array {
        $qb = $this->createQueryBuilder('p')
            ->select('s.id AS structure_id')
            ->addSelect('s.nom_structure AS structure_nom')
            ->addSelect('COALESCE(SUM(pm.nombre_postes), 0) AS total_postes')
            ->leftJoin('p.structureAcceuil', 's')
            ->leftJoin('p.cfaEtablissement', 'cfa')
            ->leftJoin('p.prospectionMetiers', 'pm')
            ->leftJoin('pm.metier', 'm')
            ->groupBy('s.id')
            ->orderBy('total_postes', 'DESC')
            ->setMaxResults($limit);

        $this->applyDashboardFilters($qb, $cfaId, $startDate, $endDate);

        return $qb->getQuery()->getArrayResult();
    }

    public function getPostesByMetier(
        ?int $cfaId,
        ?DateTimeInterface $startDate,
        ?DateTimeInterface $endDate
    ): array {
        $qb = $this->createQueryBuilder('p')
            ->select('m.id AS metier_id')
            ->addSelect('m.nom AS metier_nom')
            ->addSelect('COALESCE(SUM(pm.nombre_postes), 0) AS total_postes')
            ->leftJoin('p.cfaEtablissement', 'cfa')
            ->leftJoin('p.prospectionMetiers', 'pm')
            ->leftJoin('pm.metier', 'm')
            ->groupBy('m.id')
            ->orderBy('total_postes', 'DESC');

        $this->applyDashboardFilters($qb, $cfaId, $startDate, $endDate);

        return $qb->getQuery()->getArrayResult();
    }

    public function getEntrepriseTable(
        ?int $cfaId,
        ?DateTimeInterface $startDate,
        ?DateTimeInterface $endDate,
        int $limit = 50
    ): array {
        // Première requête pour récupérer les données de base
        $qb = $this->createQueryBuilder('p')
            ->select('s.id AS structure_id')
            ->addSelect('s.nom_structure AS structure_nom')
            ->addSelect('s.localite AS structure_ville')
            ->addSelect('s.secteur_activite AS structure_secteur')
            ->addSelect('cfa.nomEtablissement AS cfa_nom')
            ->addSelect('COALESCE(SUM(pm.nombre_postes), 0) AS total_postes')
            ->addSelect('MAX(p.date) AS last_date')
            ->addSelect('MAX(u.email) AS user_email')
            ->leftJoin('p.structureAcceuil', 's')
            ->leftJoin('p.cfaEtablissement', 'cfa')
            ->leftJoin('p.user', 'u')
            ->leftJoin('p.prospectionMetiers', 'pm')
            ->leftJoin('pm.metier', 'm')
            ->groupBy('s.id')
            ->orderBy('last_date', 'DESC')
            ->setMaxResults($limit);

        $this->applyDashboardFilters($qb, $cfaId, $startDate, $endDate);

        $results = $qb->getQuery()->getArrayResult();

        // Deuxième requête pour récupérer les noms des métiers par structure
        $structureIds = array_column($results, 'structure_id');
        if (empty($structureIds)) {
            return $results;
        }

        $qbMetiers = $this->createQueryBuilder('p2')
            ->select('s2.id AS struct_id')
            ->addSelect('m2.nom AS metier_nom')
            ->leftJoin('p2.structureAcceuil', 's2')
            ->leftJoin('p2.prospectionMetiers', 'pm2')
            ->leftJoin('pm2.metier', 'm2')
            ->where('s2.id IN (:structureIds)')
            ->andWhere('m2.id IS NOT NULL')
            ->groupBy('s2.id')
            ->addGroupBy('m2.nom')
            ->setParameter('structureIds', $structureIds);

        $metiersResult = $qbMetiers->getQuery()->getArrayResult();

        // Regrouper les métiers par structure
        $metiersByStructure = [];
        foreach ($metiersResult as $row) {
            $structId = $row['struct_id'];
            if (!isset($metiersByStructure[$structId])) {
                $metiersByStructure[$structId] = [];
            }
            $metiersByStructure[$structId][] = $row['metier_nom'];
        }

        // Fusionner les résultats
        foreach ($results as &$result) {
            $structId = $result['structure_id'];
            $metiers = $metiersByStructure[$structId] ?? [];
            $result['metiers_noms'] = !empty($metiers) ? implode(', ', array_unique($metiers)) : null;
        }

        return $results;
    }

    public function getStructureProspectionDetails(int $structureId): array
    {
        $qb = $this->createQueryBuilder('p')
            ->select('p.id AS prospection_id')
            ->addSelect('p.date AS prospection_date')
            ->addSelect('p.commentaire AS prospection_commentaire')
            ->addSelect('cfa.nomEtablissement AS cfa_nom')
            ->addSelect('u.email AS user_email')
            ->addSelect('m.nom AS metier_nom')
            ->addSelect('COALESCE(pm.nombre_postes, 0) AS postes')
            ->leftJoin('p.structureAcceuil', 's')
            ->leftJoin('p.cfaEtablissement', 'cfa')
            ->leftJoin('p.user', 'u')
            ->leftJoin('p.prospectionMetiers', 'pm')
            ->leftJoin('pm.metier', 'm')
            ->andWhere('s.id = :structureId')
            ->setParameter('structureId', $structureId)
            ->orderBy('p.date', 'DESC');

        return $qb->getQuery()->getArrayResult();
    }

//    /**
//     * @return Prospection[] Returns an array of Prospection objects
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

//    public function findOneBySomeField($value): ?Prospection
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
