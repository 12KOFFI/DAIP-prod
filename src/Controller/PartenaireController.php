<?php

namespace App\Controller;

use App\Entity\Partenaire;
use App\Form\PartenaireType;
use App\Repository\PartenaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/partenaire')]
#[IsGranted('ROLE_ADMIN')]
final class PartenaireController extends AbstractController
{
    #[Route(name: 'app_partenaire_index', methods: ['GET'])]
    public function index(PartenaireRepository $partenaireRepository): Response
    {
        return $this->render('partenaire/index.html.twig', [
            'partenaires' => $partenaireRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_partenaire_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, PartenaireRepository $partenaireRepository): Response
    {
        $isPopup = (bool) $request->query->get('_popup');

        $partenaire = new Partenaire();
        
        // Générer le numéro de partenaire
        $lastPartenaire = $partenaireRepository->findOneBy([], ['id' => 'DESC']);
        $nextId = $lastPartenaire ? $lastPartenaire->getId() + 1 : 1;
        $partenaire->setNumPartenaire('PART-' . str_pad($nextId, 4, '0', STR_PAD_LEFT));
        
        $form = $this->createForm(PartenaireType::class, $partenaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'upload du logo
            $logoFile = $form->get('logoFile')->getData();
            
            if ($logoFile) {
                $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/partenaire-logo';
                
                // Créer le répertoire s'il n'existe pas
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                // Générer un nom de fichier unique
                $newFilename = uniqid().'.'.$logoFile->guessExtension();
                
                // Déplacer le fichier dans le dossier d'upload
                try {
                    $logoFile->move(
                        $uploadDir,
                        $newFilename
                    );
                    
                    // Enregistrer le chemin relatif dans la base de données
                    $partenaire->setLogo('uploads/partenaire-logo/'.$newFilename);
                } catch (FileException $e) {
                    // Gérer l'erreur si le téléchargement échoue
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement du logo.');
                }
            }
            
            $entityManager->persist($partenaire);
            $entityManager->flush();

            if ($isPopup) {
                return new JsonResponse([
                    'success' => true,
                    'partenaire' => [
                        'id' => $partenaire->getId(),
                        'nom' => $partenaire->getNom(),
                    ],
                ]);
            }

            $this->addFlash('success', 'Le partenaire a été créé avec succès.');
            return $this->redirectToRoute('app_partenaire_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($isPopup) {
            return $this->render('partenaire/_form_modal.html.twig', [
                'partenaire' => $partenaire,
                'form' => $form,
                'title' => 'Nouveau partenaire',
                'button_label' => 'Save',
            ], new Response(null, $form->isSubmitted() ? 422 : 200));
        }

        return $this->render('partenaire/_form.html.twig', [
            'partenaire' => $partenaire,
            'form' => $form,
            'title' => 'Nouveau partenaire',
        ]);
    }

    #[Route('/{id}', name: 'app_partenaire_show', methods: ['GET'])]
    public function show(Partenaire $partenaire): Response
    {
        return $this->render('partenaire/form.html.twig', [
            'partenaire' => $partenaire,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_partenaire_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Partenaire $partenaire, EntityManagerInterface $entityManager): Response
    {
        $oldLogo = $partenaire->getLogo(); // Sauvegarder l'ancien logo
        
        $form = $this->createForm(PartenaireType::class, $partenaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'upload du logo
            $logoFile = $form->get('logoFile')->getData();
            
            if ($logoFile) {
                $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/partenaire-logo';
                
                // Créer le répertoire s'il n'existe pas
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                // Supprimer l'ancien logo s'il existe
                if ($oldLogo && file_exists($this->getParameter('kernel.project_dir') . '/public/' . $oldLogo)) {
                    unlink($this->getParameter('kernel.project_dir') . '/public/' . $oldLogo);
                }
                
                // Générer un nom de fichier unique
                $newFilename = uniqid().'.'.$logoFile->guessExtension();
                
                // Déplacer le fichier dans le dossier d'upload
                try {
                    $logoFile->move(
                        $uploadDir,
                        $newFilename
                    );
                    
                    // Enregistrer le chemin relatif dans la base de données
                    $partenaire->setLogo('uploads/partenaire-logo/'.$newFilename);
                } catch (FileException $e) {
                    // Gérer l'erreur si le téléchargement échoue
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement du logo.');
                }
            }
            
            $entityManager->flush();
            
            $this->addFlash('success', 'Le partenaire a été modifié avec succès.');
            return $this->redirectToRoute('app_partenaire_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('partenaire/_form.html.twig', [
            'partenaire' => $partenaire,
            'form' => $form,
            'title' => 'Modifier le partenaire',
        ]);
    }

    #[Route('/{id}', name: 'app_partenaire_delete', methods: ['POST'])]
    public function delete(Request $request, Partenaire $partenaire, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$partenaire->getId(), $request->getPayload()->getString('_token'))) {
            // Supprimer le fichier de logo s'il existe
            $logoPath = $this->getParameter('kernel.project_dir').'/public/'.$partenaire->getLogo();
            if ($partenaire->getLogo() && file_exists($logoPath)) {
                unlink($logoPath);
            }
            
            $entityManager->remove($partenaire);
            $entityManager->flush();
            
            $this->addFlash('success', 'Le partenaire a été supprimé avec succès.');
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('app_partenaire_index', [], Response::HTTP_SEE_OTHER);
    }
}
