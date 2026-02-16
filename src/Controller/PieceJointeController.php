<?php

namespace App\Controller;

use App\Entity\PieceJointe;
use App\Form\PieceJointeType;
use App\Repository\PieceJointeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/piece/jointe')]
final class PieceJointeController extends AbstractController
{
    #[Route(name: 'app_piece_jointe_index', methods: ['GET'])]
    public function index(PieceJointeRepository $pieceJointeRepository): Response
    {
        return $this->render('piece_jointe/index.html.twig', [
            'piece_jointes' => $pieceJointeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_piece_jointe_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $pieceJointe = new PieceJointe();
        $form = $this->createForm(PieceJointeType::class, $pieceJointe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($pieceJointe);
            $entityManager->flush();

            return $this->redirectToRoute('app_piece_jointe_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('piece_jointe/_form.html.twig', [
            'piece_jointe' => $pieceJointe,
            'form' => $form,
            'title' => 'Nouvelle pièce jointe',

        ]);
    }

    #[Route('/{id}', name: 'app_piece_jointe_show', methods: ['GET'])]
    public function show(PieceJointe $pieceJointe): Response
    {
        return $this->render('piece_jointe/form.html.twig', [
            'piece_jointe' => $pieceJointe,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_piece_jointe_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, PieceJointe $pieceJointe, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PieceJointeType::class, $pieceJointe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_piece_jointe_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('piece_jointe/_form.html.twig', [
            'piece_jointe' => $pieceJointe,
            'form' => $form,
            'title' => 'Modifier la pièce jointe',
        ]);
    }

    #[Route('/{id}', name: 'app_piece_jointe_delete', methods: ['POST'])]
    public function delete(Request $request, PieceJointe $pieceJointe, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$pieceJointe->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($pieceJointe);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_piece_jointe_index', [], Response::HTTP_SEE_OTHER);
    }
}
