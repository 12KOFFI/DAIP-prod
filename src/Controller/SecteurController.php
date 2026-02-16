<?php

namespace App\Controller;

use App\Entity\Secteur;
use App\Form\SecteurType;
use App\Repository\SecteurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/secteur')]
#[IsGranted('ROLE_ADMIN')]
final class SecteurController extends AbstractController
{
    #[Route(name: 'app_secteur_index', methods: ['GET'])]
    public function index(SecteurRepository $secteurRepository): Response
    {
        return $this->render('secteur/index.html.twig', [
            'secteurs' => $secteurRepository->findBy([], ['nom' => 'ASC']),
        ]);
    }

    #[Route('/new', name: 'app_secteur_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $secteur = new Secteur();
        $form = $this->createForm(SecteurType::class, $secteur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($secteur);
            $entityManager->flush();
            $this->addFlash('success', 'Le secteur a été créé avec succès.');
            return $this->redirectToRoute('app_secteur_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('secteur/_form.html.twig', [
            'secteur' => $secteur,
            'form' => $form,
            'title' => 'Nouveau secteur',
        ]);
    }

    #[Route('/{id}/edit', name: 'app_secteur_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Secteur $secteur, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SecteurType::class, $secteur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Le secteur a été mis à jour avec succès.');
            return $this->redirectToRoute('app_secteur_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('secteur/_form.html.twig', [
            'secteur' => $secteur,
            'form' => $form,
            'title' => 'Modifier le secteur',
        ]);
    }

    #[Route('/{id}', name: 'app_secteur_delete', methods: ['POST'])]
    public function delete(Request $request, Secteur $secteur, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $secteur->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($secteur);
            $entityManager->flush();
            $this->addFlash('success', 'Le secteur a été supprimé.');
        }

        return $this->redirectToRoute('app_secteur_index', [], Response::HTTP_SEE_OTHER);
    }
}
