<?php

namespace App\Controller;

use App\Entity\Notation;
use App\Form\NotationType;
use App\Repository\NotationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/notation')]
final class NotationController extends AbstractController
{
    #[Route(name: 'app_notation_index', methods: ['GET'])]
    public function index(NotationRepository $notationRepository): Response
    {
        return $this->render('notation/index.html.twig', [
            'notations' => $notationRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_notation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $notation = new Notation();
        $form = $this->createForm(NotationType::class, $notation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($notation);
            $entityManager->flush();

            return $this->redirectToRoute('app_notation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('notation/_form.html.twig', [
            'notation' => $notation,
            'form' => $form,
            'title' => 'Nouvelle notation',
        ]);
    }

    #[Route('/{id}', name: 'app_notation_show', methods: ['GET'])]
    public function show(Notation $notation): Response
    {
        return $this->render('notation/form.html.twig', [
            'notation' => $notation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_notation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Notation $notation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(NotationType::class, $notation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_notation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('notation/form.html.twig', [
            'notation' => $notation,
            'form' => $form,
            'title' => 'Modifier la notation',
        ]);
    }

    #[Route('/{id}', name: 'app_notation_delete', methods: ['POST'])]
    public function delete(Request $request, Notation $notation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$notation->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($notation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_notation_index', [], Response::HTTP_SEE_OTHER);
    }
}
