<?php

namespace App\Controller;

use App\Entity\Jury;
use App\Form\JuryType;
use App\Repository\JuryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/jury')]
final class JuryController extends AbstractController
{
    #[Route(name: 'app_jury_index', methods: ['GET'])]
    public function index(JuryRepository $juryRepository): Response
    {
        return $this->render('jury/index.html.twig', [
            'juries' => $juryRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_jury_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $jury = new Jury();
        $form = $this->createForm(JuryType::class, $jury);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($jury);
            $entityManager->flush();

            return $this->redirectToRoute('app_jury_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('jury/form.html.twig', [
            'jury' => $jury,
            'form' => $form,
            'title' => 'Nouveau jury',
        ]);
    }

    #[Route('/{id}', name: 'app_jury_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Jury $jury): Response
    {
        return $this->render('jury/form.html.twig', [
            'jury' => $jury,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_jury_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, Jury $jury, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(JuryType::class, $jury);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_jury_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('jury/form.html.twig', [
            'jury' => $jury,
            'form' => $form,
            'title' => 'Modifier le jury',
        ]);
    }

    #[Route('/{id}', name: 'app_jury_delete', methods: ['POST'])]
    public function delete(Request $request, Jury $jury, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$jury->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($jury);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_jury_index', [], Response::HTTP_SEE_OTHER);
    }
}
