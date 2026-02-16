<?php

namespace App\Controller;

use App\Entity\NiveauEtude;
use App\Form\NiveauEtudeType;
use App\Repository\NiveauEtudeRepository;
use App\Repository\MetierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/niveau/etude')]
#[IsGranted('ROLE_ADMIN')]
final class NiveauEtudeController extends AbstractController
{
    #[Route(name: 'app_niveau_etude_index', methods: ['GET'])]
    public function index(NiveauEtudeRepository $niveauEtudeRepository): Response
    {
        return $this->render('niveau_etude/index.html.twig', [
            'niveau_etudes' => $niveauEtudeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_niveau_etude_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, MetierRepository $metierRepository): Response
    {
        $niveauEtude = new NiveauEtude();
        
        // Récupérer tous les métiers pour les passer au formulaire si nécessaire
        $metiers = $metierRepository->findAll();
        
        $form = $this->createForm(NiveauEtudeType::class, $niveauEtude, [
            'metiers' => $metiers
        ]);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // La collection de métiers sera gérée automatiquement grâce à 'by_reference' => false
            $entityManager->persist($niveauEtude);
            $entityManager->flush();

            $this->addFlash('success', 'Le niveau d\'étude a été créé avec succès.');
            return $this->redirectToRoute('app_niveau_etude_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('niveau_etude/_form.html.twig', [
            'niveau_etude' => $niveauEtude,
            'form' => $form,
            'title' => 'Nouveau niveau d\'étude',
        ]);
    }

    #[Route('/{id}', name: 'app_niveau_etude_show', methods: ['GET'])]
    public function show(NiveauEtude $niveauEtude): Response
    {
        return $this->render('niveau_etude/form.html.twig', [
            'niveau_etude' => $niveauEtude,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_niveau_etude_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, NiveauEtude $niveauEtude, EntityManagerInterface $entityManager, MetierRepository $metierRepository): Response
    {
        // Récupérer tous les métiers pour les passer au formulaire
        $metiers = $metierRepository->findAll();
        
        $form = $this->createForm(NiveauEtudeType::class, $niveauEtude, [
            'metiers' => $metiers
        ]);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // La collection de métiers sera mise à jour automatiquement grâce à 'by_reference' => false
            $entityManager->flush();
            
            $this->addFlash('success', 'Le niveau d\'étude a été mis à jour avec succès.');
            return $this->redirectToRoute('app_niveau_etude_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('niveau_etude/_form.html.twig', [
            'niveau_etude' => $niveauEtude,
            'form' => $form,
            'title' => 'Modifier le niveau d\'étude',
        ]);
    }

    #[Route('/{id}', name: 'app_niveau_etude_delete', methods: ['POST'])]
    public function delete(Request $request, NiveauEtude $niveauEtude, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$niveauEtude->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($niveauEtude);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_niveau_etude_index', [], Response::HTTP_SEE_OTHER);
    }
}
