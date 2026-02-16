<?php

namespace App\Controller;

use App\Entity\Vae;
use App\Form\VaeType;
use App\Repository\VaeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/vae')]
final class VaeController extends AbstractController
{
    #[Route(name: 'app_vae_index', methods: ['GET'])]
    public function index(VaeRepository $vaeRepository): Response
    {
        return $this->render('vae/index.html.twig', [
            'vaes' => $vaeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_vae_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $vae = new Vae();
        $form = $this->createForm(VaeType::class, $vae);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($vae);
            $entityManager->flush();

            return $this->redirectToRoute('app_vae_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('vae/_form.html.twig', [
            'vae' => $vae,
            'form' => $form,
            'title' => 'Nouvelle Vae',

        ]);
    }

    #[Route('/{id}', name: 'app_vae_show', methods: ['GET'])]
    public function show(Vae $vae): Response
    {
        return $this->render('vae/form.html.twig', [
            'vae' => $vae,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_vae_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Vae $vae, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(VaeType::class, $vae);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_vae_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('vae/_form.html.twig', [
            'vae' => $vae,
            'form' => $form,
            'title' => 'Modifier Vae',

        ]);
    }

    #[Route('/{id}', name: 'app_vae_delete', methods: ['POST'])]
    public function delete(Request $request, Vae $vae, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$vae->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($vae);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_vae_index', [], Response::HTTP_SEE_OTHER);
    }
}
