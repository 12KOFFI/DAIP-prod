<?php

namespace App\Controller;

use App\Entity\Projet;
use App\Form\ProjetType;
use App\Repository\ProjetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

/**
 * @Route("/projet")
 */
#[Route('/projet')]
class ProjetController extends AbstractController
{

    #[Route(name: 'app_projet_index', methods: ['GET'])]
    public function index(ProjetRepository $projetRepository): Response
    {
        return $this->render('projet/index.html.twig', [
            'projets' => $projetRepository->findAll(),
        ]);
    }

    #[Route("/new", name: 'app_projet_new', methods: ['GET', "POST"])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $projet = new Projet();
        $form = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion du logo
            $logoFile = $form->has('logo') ? $form->get('logo')->getData() : null;
            if ($logoFile) {
                $originalFilename = pathinfo($logoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = 'logo_' . $safeFilename . '-' . uniqid() . '.' . $logoFile->guessExtension();

                try {
                    // stocker le logo dans le dossier projet/logo
                    $logoFile->move(
                        $this->getParameter('upload_directory') . '/projet/logo/',
                        $newFilename
                    );
                    $projet->setLogo($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement du logo.');
                }
            }

            // Gestion de l'image illustrative
            $imageFile = $form->has('image') ? $form->get('image')->getData() : null;
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = 'image_' . $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    // stocker l'image dans le dossier projet/images
                    $imageFile->move(
                        $this->getParameter('upload_directory') . '/projet/images/',
                        $newFilename
                    );
                    $projet->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement de l\'image illustrative.');
                }
            }

            $entityManager->persist($projet);
            $entityManager->flush();

            $this->addFlash('success', 'Le projet a été créé avec succès.');
            return $this->redirectToRoute('app_projet_index');
        }

        return $this->render('projet/form.html.twig', [
            'projet' => $projet,
            'form' => $form->createView(),
        ]);
    }

    #[Route("/{id}", name: 'app_projet_show', methods: ['GET'])]
    public function show(Projet $projet): Response
    {
        return $this->render('projet/show.html.twig', [
            'projet' => $projet,
        ]);
    }


    #[Route("/{id}/edit", name: 'app_projet_edit', methods: ['GET', "POST"])]
    public function edit(Request $request, Projet $projet, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion du logo
            $logoFile = $form->has('logo') ? $form->get('logo')->getData() : null;
            if ($logoFile) {
                $originalFilename = pathinfo($logoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = 'logo_' . $safeFilename . '-' . uniqid() . '.' . $logoFile->guessExtension();

                try {
                    // Supprimer l'ancien logo s'il existe (chemin vers projet/logo)
                    $oldLogo = $this->getParameter('upload_directory') . '/projet/logo/' . $projet->getLogo();
                    if ($projet->getLogo() && file_exists($oldLogo)) {
                        unlink($oldLogo);
                    }

                    // déplacer le nouveau logo dans projet/logo
                    $logoFile->move(
                        $this->getParameter('upload_directory') . '/projet/logo/',
                        $newFilename
                    );
                    $projet->setLogo($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement du logo.');
                }
            }

            // Gestion de l'image illustrative
            $imageFile = $form->has('image') ? $form->get('image')->getData() : null;
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = 'image_' . $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    // Supprimer l'ancienne image s'il existe
                    $oldImage = $this->getParameter('upload_directory') . '/projet/images/' . $projet->getImage();
                    if ($projet->getImage() && file_exists($oldImage)) {
                        unlink($oldImage);
                    }

                    // déplacer la nouvelle image dans projet/images
                    $imageFile->move(
                        $this->getParameter('upload_directory') . '/projet/images/',
                        $newFilename
                    );
                    $projet->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement de l\'image illustrative.');
                }
            }

            $entityManager->flush();
            $this->addFlash('success', 'Le projet a été mis à jour avec succès.');
            return $this->redirectToRoute('app_projet_index');
        }

        return $this->render('projet/form.html.twig', [
            'projet' => $projet,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="app_projet_delete", methods={"POST"})
     */ #[Route("/{id}", name: 'app_projet_delete', methods: ["POST"])]
    public function delete(Request $request, Projet $projet, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $projet->getId(), $request->request->get('_token'))) {
            // Supprimer le logo s'il existe (chemin: upload_directory/projet/logo/)
            if ($projet->getLogo()) {
                $logoPath = $this->getParameter('upload_directory') . '/projet/logo/' . $projet->getLogo();
                if (file_exists($logoPath)) {
                    unlink($logoPath);
                }
            }

            // Supprimer l'image illustrative s'il existe
            if ($projet->getImage()) {
                $imagePath = $this->getParameter('upload_directory') . '/projet/images/' . $projet->getImage();
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            $entityManager->remove($projet);
            $entityManager->flush();
            $this->addFlash('success', 'Le projet a été supprimé avec succès.');
        }

        return $this->redirectToRoute('app_projet_index');
    }
}
