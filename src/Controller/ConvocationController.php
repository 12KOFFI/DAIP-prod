<?php

namespace App\Controller;

use App\Entity\Convocation;
use App\Form\ConvocationType;
use App\Repository\ConvocationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/convocation')]
class ConvocationController extends AbstractController
{
    #[Route('/', name: 'app_convocation_index', methods: ['GET'])]
    public function index(ConvocationRepository $convocationRepository): Response
    {
        return $this->render('convocation/index.html.twig', [
            'convocations' => $convocationRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_convocation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $convocation = new Convocation();
        $form = $this->createForm(ConvocationType::class, $convocation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($convocation);
            $entityManager->flush();

            return $this->redirectToRoute('app_convocation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('convocation/new.html.twig', [
            'convocation' => $convocation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_convocation_show', methods: ['GET'])]
    public function show(Convocation $convocation): Response
    {
        return $this->render('convocation/show.html.twig', [
            'convocation' => $convocation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_convocation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Convocation $convocation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ConvocationType::class, $convocation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_convocation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('convocation/edit.html.twig', [
            'convocation' => $convocation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_convocation_delete', methods: ['POST'])]
    public function delete(Request $request, Convocation $convocation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$convocation->getId(), $request->request->get('_token'))) {
            $entityManager->remove($convocation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_convocation_index', [], Response::HTTP_SEE_OTHER);
    }
}
