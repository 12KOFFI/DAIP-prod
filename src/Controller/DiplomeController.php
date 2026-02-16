<?php

namespace App\Controller;

use App\Entity\Diplome;
use App\Form\DiplomeType;
use App\Repository\DiplomeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/diplome')]
#[IsGranted('ROLE_ADMIN')]
final class DiplomeController extends AbstractController
{
    #[Route(name: 'app_diplome_index', methods: ['GET'])]
    public function index(DiplomeRepository $diplomeRepository): Response
    {
        return $this->render('diplome/index.html.twig', [
            'diplomes' => $diplomeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_diplome_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $diplome = new Diplome();
        $form = $this->createForm(DiplomeType::class, $diplome);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($diplome);
            $entityManager->flush();

            return $this->redirectToRoute('app_diplome_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('diplome/_form.html.twig', [
            'diplome' => $diplome,
            'form' => $form,
            'title' => 'Nouveau diplome',
        ]);
    }

    #[Route('/{id}', name: 'app_diplome_show', methods: ['GET'])]
    public function show(Diplome $diplome): Response
    {
        return $this->render('diplome/_form.html.twig', [
            'diplome' => $diplome,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_diplome_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Diplome $diplome, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DiplomeType::class, $diplome);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_diplome_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('diplome/_form.html.twig', [
            'diplome' => $diplome,
            'form' => $form,
            'title' => 'Modifier le diplome',
        ]);
    }

    #[Route('/{id}', name: 'app_diplome_delete', methods: ['POST'])]
    public function delete(Request $request, Diplome $diplome, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$diplome->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($diplome);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_diplome_index', [], Response::HTTP_SEE_OTHER);
    }
}
