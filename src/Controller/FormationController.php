<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Form\FormationType;
use App\Repository\FormationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use App\Service\MediaService;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/formation')]
#[IsGranted('ROLE_ADMIN')]
final class FormationController extends AbstractController
{
    #[Route(name: 'app_formation_index', methods: ['GET'])]
    public function index(FormationRepository $formationRepository): Response
    {
        return $this->render('formation/index.html.twig', [
            'formations' => $formationRepository->findAll(),
        ]);
    }

    private MediaService $mediaService;

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    #[Route('/new', name: 'app_formation_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request, 
        EntityManagerInterface $entityManager, 
        FormationRepository $formationRepository
    ): Response {
        $formation = new Formation();
        
        // Générer le numéro de formation
        $lastFormation = $formationRepository->findOneBy([], ['id' => 'DESC']);
        $nextId = $lastFormation ? $lastFormation->getId() + 1 : 1;
        $formation->setNumformation('FORM-' . str_pad($nextId, 4, '0', STR_PAD_LEFT));
        
        $form = $this->createForm(FormationType::class, $formation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion du téléchargement des fichiers
            $this->handleFileUpload($form, $formation);
            
            // Définir le statut par défaut si non défini
            if (!$formation->getStatut()) {
                $formation->setStatut('Brouillon');
            }
            
            $entityManager->persist($formation);
            $entityManager->flush();

            $this->addFlash('success', 'La formation a été créée avec succès.');
            return $this->redirectToRoute('app_formation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('formation/form.html.twig', [
            'formation' => $formation,
            'form' => $form,
            'title' => 'Nouvelle formation',
        ]);
    }

    private function handleFileUpload($form, $formation): void
    {
        $fileFields = [
            'image' => 'image',
            'banniere' => 'banniere',
            'logo' => 'logo'
        ];

        foreach ($fileFields as $field => $setter) {
            $file = $form->get($field)->getData();
            
            if ($file instanceof UploadedFile) {
                // Supprimer l'ancien fichier s'il existe
                $oldFile = $formation->{'get' . ucfirst($setter)}();
                if ($oldFile) {
                    $this->mediaService->deleteImage('formation', $oldFile);
                }
                
                // Utiliser MediaService pour gérer l'upload
                $filename = $this->mediaService->uploadImage($file, 'formation', $setter);
                $method = 'set' . ucfirst($setter);
                $formation->$method($filename);
            }
        }
    }

    #[Route('/{id}', name: 'app_formation_show', methods: ['GET'])]
    public function show(Formation $formation): Response
    {
        return $this->render('formation/form.html.twig', [
            'formation' => $formation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_formation_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request, 
        Formation $formation, 
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(FormationType::class, $formation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion du téléchargement des fichiers
            $this->handleFileUpload($form, $formation);
            
            $entityManager->flush();
            $this->addFlash('success', 'La formation a été mise à jour avec succès.');
            
            return $this->redirectToRoute('app_formation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('formation/form.html.twig', [
            'formation' => $formation,
            'form' => $form,
            'title' => 'Modifier la formation',
        ]);
    }

    #[Route('/{id}', name: 'app_formation_delete', methods: ['POST'])]
    public function delete(
        Request $request, 
        Formation $formation, 
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $formation->getId(), $request->getPayload()->getString('_token'))) {
            // Supprimer les fichiers associés
            $filesToDelete = [
                'image' => $formation->getImage(),
                'banniere' => $formation->getBanniere(),
                'logo' => $formation->getLogo()
            ];
            
            foreach ($filesToDelete as $type => $file) {
                if ($file) {
                    $this->mediaService->deleteImage('formation', $file);
                }
            }
            
            $entityManager->remove($formation);
            $entityManager->flush();
            
            $this->addFlash('success', 'La formation a été supprimée avec succès.');
        }

        return $this->redirectToRoute('app_formation_index', [], Response::HTTP_SEE_OTHER);
    }
}
