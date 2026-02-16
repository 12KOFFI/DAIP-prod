<?php

namespace App\Controller;

use App\Entity\Recrutement;
use App\Entity\Metier;
use App\Form\RecrutementType;
use App\Repository\RecrutementRepository;
use App\Repository\MetierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Service\MediaService;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/recrutement')]
final class RecrutementController extends AbstractController
{
    #[Route(name: 'app_recrutement_index', methods: ['GET'])]
    public function index(RecrutementRepository $recrutementRepository): Response
    {
        return $this->render('recrutement/index.html.twig', [
            'recrutements' => $recrutementRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_recrutement_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $entityManager, RecrutementRepository $recrutementRepository, MediaService $mediaService): Response
    {
        $recrutement = new Recrutement();

        // Générer le numéro de recrutement
        $lastRecrutement = $recrutementRepository->findOneBy([], ['id' => 'DESC']);
        $nextId = $lastRecrutement ? $lastRecrutement->getId() + 1 : 1;
        $recrutement->setNumRecrutement('RECRUT-' . str_pad($nextId, 4, '0', STR_PAD_LEFT));

        $form = $this->createForm(RecrutementType::class, $recrutement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'image
            $imageFile = $form->get('image')->getData();
            if ($imageFile instanceof UploadedFile) {
                $newFilename = $mediaService->uploadRecruitment($imageFile, 'image');
                $recrutement->setImage($newFilename);
            }

            // Gestion du logo
            $logoFile = $form->get('logo')->getData();
            if ($logoFile instanceof UploadedFile) {
                $newFilename = $mediaService->uploadRecruitment($logoFile, 'logo');
                $recrutement->setLogo($newFilename);
            }

            // Gestion de la bannière
            $banniereFile = $form->get('banniere')->getData();
            if ($banniereFile instanceof UploadedFile) {
                $newFilename = $mediaService->uploadRecruitment($banniereFile, 'banniere');
                $recrutement->setBanniere($newFilename);
            }

            // Gestion de l'image d'annonce
            $imageAnnonceFile = $form->get('imageAnnonce')->getData();
            if ($imageAnnonceFile instanceof UploadedFile) {
                $newFilename = $mediaService->uploadRecruitment($imageAnnonceFile, 'image_annonce');
                $recrutement->setImageAnnonce($newFilename);
            }

            $entityManager->persist($recrutement);
            $entityManager->flush();

            return $this->redirectToRoute('app_recrutement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('recrutement/_form.html.twig', [
            'recrutement' => $recrutement,
            'form' => $form,
            'title' => 'Nouveu recrutement',

        ]);
    }

    #[Route('/{id}', name: 'app_recrutement_show', methods: ['GET'])]
    public function show(Recrutement $recrutement, MetierRepository $metierRepository): Response
    {
        // Récupérer le logo du projet associé au recrutement
        $headerLogo = 'front/image/e2c.png'; // Logo par défaut
        
        if ($recrutement->getProjet() && $recrutement->getProjet()->getLogo()) {
            $headerLogo = 'uploads/projet/logo/' . $recrutement->getProjet()->getLogo();
        }
        
        return $this->render('recrutement/show.html.twig', [
            'recrutement' => $recrutement,
            'metiers' => $metierRepository->findAll(),
            'header_logo' => $headerLogo,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_recrutement_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, Recrutement $recrutement, EntityManagerInterface $entityManager, MediaService $mediaService): Response
    {
        $form = $this->createForm(RecrutementType::class, $recrutement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'image
            $imageFile = $form->get('image')->getData();
            if ($imageFile instanceof UploadedFile) {
                $newFilename = $mediaService->uploadRecruitment($imageFile, 'image');
                $recrutement->setImage($newFilename);
            }

            // Gestion du logo
            $logoFile = $form->get('logo')->getData();
            if ($logoFile instanceof UploadedFile) {
                $newFilename = $mediaService->uploadRecruitment($logoFile, 'logo');
                $recrutement->setLogo($newFilename);
            }

            // Gestion de la bannière
            $banniereFile = $form->get('banniere')->getData();
            if ($banniereFile instanceof UploadedFile) {
                $newFilename = $mediaService->uploadRecruitment($banniereFile, 'banniere');
                $recrutement->setBanniere($newFilename);
            }

            // Gestion de l'image d'annonce
            $imageAnnonceFile = $form->get('imageAnnonce')->getData();
            if ($imageAnnonceFile instanceof UploadedFile) {
                $newFilename = $mediaService->uploadRecruitment($imageAnnonceFile, 'image_annonce');
                $recrutement->setImageAnnonce($newFilename);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_recrutement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('recrutement/_form.html.twig', [
            'recrutement' => $recrutement,
            'form' => $form,
            'title' => 'Modifier recrutement',

        ]);
    }

    #[Route('/{id}', name: 'app_recrutement_delete', methods: ['DELETE', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Recrutement $recrutement, EntityManagerInterface $entityManager): Response
    {
        $token = $request->request->get('_token') ?? $request->getPayload()->getString('_token');
        
        if ($this->isCsrfTokenValid('delete'.$recrutement->getId(), $token)) {
            $entityManager->remove($recrutement);
            $entityManager->flush();
            $this->addFlash('success', 'Le recrutement a été supprimé avec succès.');
        }

        return $this->redirectToRoute('app_recrutement_index', [], Response::HTTP_SEE_OTHER);
    }
}
