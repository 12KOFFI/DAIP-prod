<?php

namespace App\Controller;

use App\Entity\TypeEvaluation;
use App\Form\TypeEvaluationType;
use App\Repository\TypeEvaluationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/type/evaluation')]
#[IsGranted('ROLE_ADMIN')]
class TypeEvaluationController extends AbstractController
{
    #[Route('/', name: 'app_type_evaluation_index', methods: ['GET'])]
    public function index(TypeEvaluationRepository $typeEvaluationRepository): Response
    {
        return $this->render('type_evaluation/index.html.twig', [
            'type_evaluations' => $typeEvaluationRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_type_evaluation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $typeEvaluation = new TypeEvaluation();
        $form = $this->createForm(TypeEvaluationType::class, $typeEvaluation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($typeEvaluation);
            $entityManager->flush();

            return $this->redirectToRoute('app_type_evaluation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('type_evaluation/new.html.twig', [
            'type_evaluation' => $typeEvaluation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_type_evaluation_show', methods: ['GET'])]
    public function show(TypeEvaluation $typeEvaluation): Response
    {
        return $this->render('type_evaluation/show.html.twig', [
            'type_evaluation' => $typeEvaluation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_type_evaluation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TypeEvaluation $typeEvaluation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TypeEvaluationType::class, $typeEvaluation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_type_evaluation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('type_evaluation/edit.html.twig', [
            'type_evaluation' => $typeEvaluation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_type_evaluation_delete', methods: ['POST'])]
    public function delete(Request $request, TypeEvaluation $typeEvaluation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $typeEvaluation->getId(), $request->request->get('_token'))) {
            $entityManager->remove($typeEvaluation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_type_evaluation_index', [], Response::HTTP_SEE_OTHER);
    }
}