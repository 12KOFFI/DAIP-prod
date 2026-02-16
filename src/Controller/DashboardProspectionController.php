<?php

namespace App\Controller;

use App\Repository\ProspectionRepository;
use App\Repository\CfaEtablissementRepository;
use App\Repository\StructureAccueilRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardProspectionController extends AbstractController
{
   #[Route('/dashboard/prospection', name: 'app_dashboard_prospection', methods: ['GET'])]
    public function prospectionDashboard(
        Request $request,
        ProspectionRepository $prospectionRepository,
        CfaEtablissementRepository $cfaRepository
    ): Response {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $selectedCfaId = $request->query->get('cfa');
        $selectedCfaId = $selectedCfaId !== null && $selectedCfaId !== '' ? (int) $selectedCfaId : null;

        // Suppression du traitement des dates
        $kpis = $prospectionRepository->getProspectionDashboardKpis($selectedCfaId, null, null);
        $postesByEntreprise = $prospectionRepository->getPostesByEntreprise($selectedCfaId, null, null, 8);
        $entrepriseTable = $prospectionRepository->getEntrepriseTable($selectedCfaId, null, null, 30);

        return $this->render('dashboard/prospection_dashboard.html.twig', [
            'cfas' => $cfaRepository->findAll(),
            'selectedCfaId' => $selectedCfaId,
            'kpis' => $kpis,
            'postesByEntreprise' => $postesByEntreprise,
            'entrepriseTable' => $entrepriseTable,
        ]);
    }

    #[Route('/dashboard/prospection/entreprise/{id}', name: 'app_dashboard_prospection_detail', methods: ['GET'])]
    public function prospectionEntrepriseDetail(
        int $id,
        ProspectionRepository $prospectionRepository,
        StructureAccueilRepository $structureAccueilRepository
    ): Response {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $structure = $structureAccueilRepository->find($id);
        if (!$structure) {
            throw $this->createNotFoundException('Structure introuvable.');
        }

        $details = $prospectionRepository->getStructureProspectionDetails($id);

        return $this->render('dashboard/prospection_detail.html.twig', [
            'structure' => $structure,
            'details' => $details,
        ]);
    }
}