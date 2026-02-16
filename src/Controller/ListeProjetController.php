<?php

namespace App\Controller;

use App\Repository\FormationRepository;
use App\Repository\RecrutementRepository;
use App\Repository\VaeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ListeProjetController extends AbstractController
{
    #[Route('/ListeProjets', name: 'app_liste_projet')]
    public function index(
        Request $request,
        RecrutementRepository $recrutementRepository,
        FormationRepository $formationRepository,
        VaeRepository $vaeRepository
    ): Response {
        $perPage = 6;
        $currentPage = max(1, $request->query->getInt('page', 1));
        $totalRecrutements = $recrutementRepository->count([]);
        $totalPages = max(1, (int) ceil($totalRecrutements / $perPage));

        if ($currentPage > $totalPages) {
            $currentPage = $totalPages;
        }

        $offset = ($currentPage - 1) * $perPage;

        return $this->render('liste_projet/index.html.twig', [
            'recrutements' => $recrutementRepository->findBy([], ['id' => 'DESC'], $perPage, $offset),
            'recrutementCurrentPage' => $currentPage,
            'recrutementTotalPages' => $totalPages,
            'recrutementTotalItems' => $totalRecrutements,
            'formations' => $formationRepository->findAll(),
            'vae' => $vaeRepository->findAll(),
        ]);
    }
}
