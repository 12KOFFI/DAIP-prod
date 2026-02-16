<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\CandidatureRepository;
use App\Repository\RecrutementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard', methods: ['GET'])]
    public function index(Request $request, CandidatureRepository $candidatureRepository, RecrutementRepository $recrutementRepository): Response
    {
        if (
            $this->isGranted('ROLE_CANDIDAT')
            && !$this->isGranted('ROLE_ADMIN')
            && !$this->isGranted('ROLE_JURY')
        ) {
            return $this->redirectToRoute('app_candidature_index');
        }

        if (
            !$this->isGranted('ROLE_ADMIN')
            && !$this->isGranted('ROLE_JURY')
        ) {
            throw $this->createAccessDeniedException();
        }

        $juryStats = null;

        $selectedRecrutementId = $request->query->get('recrutement');
        $selectedRecrutementId = $selectedRecrutementId !== null && $selectedRecrutementId !== '' ? (int) $selectedRecrutementId : null;

        $recrutements = $recrutementRepository->findAll();
        $dashboardCounts = $candidatureRepository->getDashboardCountsByRecrutement($selectedRecrutementId);
        $admittedByEvaluationType = $candidatureRepository->getAdmittedByEvaluationTypeForDashboard($selectedRecrutementId);

        if ($this->isGranted('ROLE_JURY')) {
            $user = $this->getUser();
            $jury = $user instanceof User ? $user->getJury() : null;

            if ($jury) {
                $recrutementIds = array_filter(array_map(
                    static fn ($recrutement) => $recrutement?->getId(),
                    $jury->getRecrutements()->toArray()
                ));
                $formationIds = array_filter(array_map(
                    static fn ($formation) => $formation?->getId(),
                    $jury->getFormations()->toArray()
                ));
                $vaeIds = array_filter(array_map(
                    static fn ($vae) => $vae?->getId(),
                    $jury->getVaes()->toArray()
                ));

                $juryStats = $candidatureRepository->getAssignmentStats(
                    $recrutementIds,
                    $formationIds,
                    $vaeIds
                );
            }
        }

        $template = $this->isGranted('ROLE_JURY')
            ? 'jury/dashboard.html.twig'
            : 'dashboard/dashboard.html.twig';

        return $this->render($template, [
            'juryStats' => $juryStats,
            'recrutements' => $recrutements,
            'selectedRecrutementId' => $selectedRecrutementId,
            'dashboardCounts' => $dashboardCounts,
            'admittedByEvaluationType' => $admittedByEvaluationType,
        ]);
    }

    #[Route('/dashboard/centre', name: 'app_dashboard_centre', methods: ['GET'])]
    public function centreDashboard(): Response
    {
        if (!$this->isGranted('ROLE_USER_CENTRE')) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('dashboard/dashboard_centre.html.twig');
    }

    
}
