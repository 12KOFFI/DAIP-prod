<?php

namespace App\Controller;

use App\Entity\ProgramEvaluation;
use App\Form\ProgramEvaluationType;
use App\Repository\ProgramEvaluationRepository;
use App\Service\ConvocationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


#[Route('/programmation/evaluation')]
class ProgrammationEvaluationController extends AbstractController
{
    #[Route('/', name: 'app_programmation_evaluation_index', methods: ['GET'])]
    public function index(ProgramEvaluationRepository $programEvaluationRepository): Response
    {
        $this->checkAccess();

        if ($this->isGranted('ROLE_ADMIN')) {
            $programEvaluations = $programEvaluationRepository->findAll();
        } else {
            // ROLE_JURY : n'afficher que les programmations liées à l'utilisateur connecté
            $programEvaluations = $programEvaluationRepository->findBy(['user' => $this->getUser()]);
        }

        return $this->render('programmation_evaluation/index.html.twig', [
            'program_evaluations' => $programEvaluations,
        ]);
    }

    #[Route('/new', name: 'app_programmation_evaluation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->checkAccess();

        $programEvaluation = new ProgramEvaluation();
        $form = $this->createForm(ProgramEvaluationType::class, $programEvaluation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // forcer l'association à l'utilisateur courant (empêche un jury de créer pour un autre)
            $programEvaluation->setUser($this->getUser());
            $entityManager->persist($programEvaluation);
            $entityManager->flush();

            return $this->redirectToRoute('app_programmation_evaluation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('programmation_evaluation/form.html.twig', [
            'program_evaluation' => $programEvaluation,
            'form' => $form->createView(),
            'title' => 'Nouvelle Programmation',
        ]);
    }

    #[Route('/{id}', name: 'app_programmation_evaluation_show', methods: ['GET'])]
    public function show(ProgramEvaluation $programEvaluation): Response
    {
        $this->checkAccess();
        $this->ensureOwnership($programEvaluation);

        return $this->render('programmation_evaluation/show.html.twig', [
            'program_evaluation' => $programEvaluation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_programmation_evaluation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ProgramEvaluation $programEvaluation, EntityManagerInterface $entityManager): Response
    {
        $this->checkAccess();
        $this->ensureOwnership($programEvaluation);

        $form = $this->createForm(ProgramEvaluationType::class, $programEvaluation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // garantir que seul l'admin peut changer l'utilisateur ; sinon maintenir l'association actuelle
            if (!$this->isGranted('ROLE_ADMIN')) {
                $programEvaluation->setUser($this->getUser());
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_programmation_evaluation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('programmation_evaluation/form.html.twig', [
            'program_evaluation' => $programEvaluation,
            'form' => $form->createView(),
            'title' => 'Modifier Programmation',
        ]);
    }

    #[Route('/{id}/generate-convocations', name: 'app_programmation_evaluation_generate_convocations', methods: ['POST'])]
    public function generateConvocations(Request $request, ProgramEvaluation $programEvaluation, ConvocationService $convocationService): Response
    {
        $this->checkAccess();
        $this->ensureOwnership($programEvaluation);

        if (!$this->isCsrfTokenValid('generate_convocations' . $programEvaluation->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('app_programmation_evaluation_show', ['id' => $programEvaluation->getId()]);
        }

        if ($programEvaluation->getStatut() !== 'OUVERT') {
            $this->addFlash('error', 'La programmation doit être au statut OUVERT pour générer les convocations.');
            return $this->redirectToRoute('app_programmation_evaluation_show', ['id' => $programEvaluation->getId()]);
        }

        try {
            $result = $convocationService->generateForProgramEvaluation($programEvaluation->getId());

            $this->addFlash('success', sprintf(
                'Convocations générées: %d. Déjà existantes ignorées: %d. Ignorées faute de créneaux: %d.',
                $result['created'] ?? 0,
                $result['skipped_already_exists'] ?? 0,
                $result['skipped_no_slots'] ?? 0
            ));
        } catch (\Throwable $e) {
            // $e est bien un Throwable (conforme à "instance de Exception ou Throwable")
            $this->addFlash('error', 'Erreur lors de la génération des convocations: ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_programmation_evaluation_show', ['id' => $programEvaluation->getId()]);
    }

    #[Route('/{id}', name: 'app_programmation_evaluation_delete', methods: ['POST'])]
    public function delete(Request $request, ProgramEvaluation $programEvaluation, EntityManagerInterface $entityManager): Response
    {
        $this->checkAccess();
        $this->ensureOwnership($programEvaluation);

        if ($this->isCsrfTokenValid('delete' . $programEvaluation->getId(), $request->request->get('_token'))) {
            $entityManager->remove($programEvaluation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_programmation_evaluation_index', [], Response::HTTP_SEE_OTHER);
    }

    private function checkAccess(): void
    {
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_JURY')) {
            throw new AccessDeniedException('Accès réservé aux administrateurs et aux jurys.');
        }
    }

    private function ensureOwnership(ProgramEvaluation $programEvaluation): void
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return;
        }

        $currentUser = $this->getUser();
        $owner = $programEvaluation->getUser();

        if ($owner !== $currentUser) {
            if (method_exists($owner, 'getId') && method_exists($currentUser, 'getId')) {
                if ($owner->getId() === $currentUser->getUserIdentifier()) {
                    return;
                }
            }
            throw new AccessDeniedException('Accès refusé : vous n\'êtes pas le propriétaire de cet élément.');
        }
    }
}
