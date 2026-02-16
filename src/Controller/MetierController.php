<?php

namespace App\Controller;

use App\Entity\Metier;
use App\Form\MetierType;
use App\Repository\MetierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/metier')]
#[IsGranted('ROLE_ADMIN')]
final class MetierController extends AbstractController
{
    #[Route(name: 'app_metier_index', methods: ['GET'])]
    public function index(MetierRepository $metierRepository): Response
    {
        return $this->render('metier/index.html.twig', [
            'metiers' => $metierRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_metier_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $metier = new Metier();
        $form = $this->createForm(MetierType::class, $metier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleImageUpload($form, $metier, $slugger);
            
            $entityManager->persist($metier);
            $entityManager->flush();

            $this->addFlash('success', 'Le métier a été créé avec succès.');
            return $this->redirectToRoute('app_metier_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('metier/_form.html.twig', [
            'metier' => $metier,
            'form' => $form,
            'title' => 'Nouveau métier',
        ]);
    }

    #[Route('/{id}', name: 'app_metier_show', methods: ['GET'])]
    public function show(Metier $metier): Response
    {
        return $this->render('metier/form.html.twig', [
            'metier' => $metier,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_metier_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Metier $metier, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(MetierType::class, $metier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleImageUpload($form, $metier, $slugger);
            
            $entityManager->flush();
            $this->addFlash('success', 'Le métier a été mis à jour avec succès.');

            return $this->redirectToRoute('app_metier_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('metier/_form.html.twig', [
            'metier' => $metier,
            'form' => $form,
            'title' => 'Modifier le métier',
        ]);
    }

    #[Route('/{id}', name: 'app_metier_delete', methods: ['POST'])]
    public function delete(Request $request, Metier $metier, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$metier->getId(), $request->request->get('_token'))) {
            // Supprimer l'image associée si elle existe
            if ($metier->getImage()) {
                $imagePath = $this->getParameter('metier_images_directory').'/'.$metier->getImage();
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            
            $entityManager->remove($metier);
            $entityManager->flush();
            $this->addFlash('success', 'Le métier a été supprimé avec succès.');
        }

        return $this->redirectToRoute('app_metier_index', [], Response::HTTP_SEE_OTHER);
    }
    
    /**
     * Gère le téléchargement du fichier image
     */
    private function handleImageUpload($form, $metier, $slugger): void
    {
        /** @var UploadedFile $imageFile */
        $imageFile = $form->get('imageFile')->getData();
        
        if ($imageFile) {
            $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

            try {
                // Supprimer l'ancienne image si elle existe
                if ($metier->getImage()) {
                    $oldImage = $this->getParameter('metier_images_directory').'/'.$metier->getImage();
                    if (file_exists($oldImage)) {
                        unlink($oldImage);
                    }
                }
                
                // Créer le répertoire s'il n'existe pas
                $uploadDir = $this->getParameter('metier_images_directory');
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $imageFile->move(
                    $uploadDir,
                    $newFilename
                );
                
                $metier->setImage($newFilename);
            } catch (FileException $e) {
                $this->addFlash('error', 'Une erreur est survenue lors du téléchargement de l\'image.');
            }
        }
    }
}
