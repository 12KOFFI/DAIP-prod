<?php

namespace App\Controller;

use App\Entity\GrilleEvaluation;
use App\Form\GrilleEvaluationType;
use App\Repository\GrilleEvaluationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/grille/evaluation')]
final class GrilleEvaluationController extends AbstractController
{
    #[Route(name: 'app_grille_evaluation_index', methods: ['GET'])]
    public function index(GrilleEvaluationRepository $grilleEvaluationRepository): Response
    {
        $this->checkAccess();

        return $this->render('grille_evaluation/index.html.twig', [
            'grille_evaluations' => $grilleEvaluationRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_grille_evaluation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->checkAccess();

        $grilleEvaluation = new GrilleEvaluation();
        $form = $this->createForm(GrilleEvaluationType::class, $grilleEvaluation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($grilleEvaluation);
            $entityManager->flush();

            $this->addFlash('success', 'Grille d\'évaluation créée avec succès.');
            return $this->redirectToRoute('app_grille_evaluation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('grille_evaluation/form.html.twig', [
            'grille_evaluation' => $grilleEvaluation,
            'form' => $form,
            'title' => 'Nouvelle grille d\'évaluation',
            'action' => 'create',
        ]);
    }

    #[Route('/{id}', name: 'app_grille_evaluation_show', methods: ['GET'])]
    public function show(GrilleEvaluation $grilleEvaluation): Response
    {
        $this->checkAccess();

        return $this->render('grille_evaluation/show.html.twig', [
            'grille_evaluation' => $grilleEvaluation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_grille_evaluation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, GrilleEvaluation $grilleEvaluation, EntityManagerInterface $entityManager): Response
    {
        $this->checkAccess();

        $form = $this->createForm(GrilleEvaluationType::class, $grilleEvaluation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Grille d\'évaluation modifiée avec succès.');
            return $this->redirectToRoute('app_grille_evaluation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('grille_evaluation/form.html.twig', [
            'grille_evaluation' => $grilleEvaluation,
            'form' => $form,
            'title' => 'Modifier la grille d\'évaluation',
            'action' => 'edit',
        ]);
    }

    #[Route('/{id}', name: 'app_grille_evaluation_delete', methods: ['POST'])]
    public function delete(Request $request, GrilleEvaluation $grilleEvaluation, EntityManagerInterface $entityManager): Response
    {
        $this->checkAccess();

        // Vérifier si la grille est utilisée
        if (!$grilleEvaluation->getEvaluations()->isEmpty()) {
            $this->addFlash('error', 'Cette grille ne peut pas être supprimée car elle est utilisée dans des évaluations.');
            return $this->redirectToRoute('app_grille_evaluation_index');
        }

        if ($this->isCsrfTokenValid('delete' . $grilleEvaluation->getId(), $request->request->get('_token'))) {
            $entityManager->remove($grilleEvaluation);
            $entityManager->flush();

            $this->addFlash('success', 'Grille d\'évaluation supprimée avec succès.');
        }

        return $this->redirectToRoute('app_grille_evaluation_index', [], Response::HTTP_SEE_OTHER);
    }

    private function checkAccess(): void
    {
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_JURY')) {
            throw new AccessDeniedException('Accès réservé aux administrateurs et aux jurys.');
        }
    }
}
