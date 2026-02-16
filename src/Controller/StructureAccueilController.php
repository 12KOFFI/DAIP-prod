<?php

namespace App\Controller;

use App\Entity\Prospection;
use App\Entity\StructureAccueil;
use App\Entity\User;
use App\Form\StructureAccueilType;
use App\Repository\StructureAccueilRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/structure-accueil')]
final class StructureAccueilController extends AbstractController
{
    #[Route(name: 'app_structure_accueil_index', methods: ['GET'])]
    public function index(StructureAccueilRepository $structureAccueilRepository): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $cfa = $user->getCfaEtablissement();

        return $this->render('structure_accueil/index.html.twig', [
            'structures_accueil' => $cfa ? $structureAccueilRepository->findByCfaEtablissement($cfa->getId()) : [],
        ]);
    }

  #[Route('/new', name: 'app_structure_accueil_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $structureAccueil = new StructureAccueil();
        $form = $this->createForm(StructureAccueilType::class, $structureAccueil);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($structureAccueil);
            $entityManager->flush();

            // Vérifier que l'utilisateur est connecté et a un CFA
            $user = $this->getUser();
            if (!$user instanceof User) {
                throw $this->createAccessDeniedException('Vous devez être connecté.');
            }
            
            $cfaEtablissement = $user->getCfaEtablissement();
            if (!$cfaEtablissement) {
                $this->addFlash('warning', 'Aucun CFA associé à votre compte.');
                return $this->redirectToRoute('app_structure_accueil_index');
            }

            // Création AUTOMATIQUE de la prospection
            $prospectionDateValue = $request->request->get('prospection_date');
            $prospectionDate = new \DateTime();
            if (is_string($prospectionDateValue) && $prospectionDateValue !== '') {
                try {
                    $prospectionDate = new \DateTime($prospectionDateValue);
                } catch (\Throwable) {
                    $prospectionDate = new \DateTime();
                }
            }

            $prospection = new Prospection();
            $prospection->setStructureAcceuil($structureAccueil);
            $prospection->setDate($prospectionDate);
            $prospection->setCfaEtablissement($cfaEtablissement);
            $prospection->setUser($user);

            $entityManager->persist($prospection);
            $entityManager->flush();

            $this->addFlash('success', 'Structure d\'accueil créée avec succès.');

            if ($request->request->get('next_step') === 'prospection') {
                return $this->redirectToRoute('app_prospection_metier_new', [
                    'prospectionId' => $prospection->getId(),
                ], Response::HTTP_SEE_OTHER);
            }

            return $this->redirectToRoute('app_structure_accueil_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('structure_accueil/form.html.twig', [
            'structure_accueil' => $structureAccueil,
            'form' => $form,
        ]);
    }


      #[Route('/{id}/edit', name: 'app_structure_accueil_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, StructureAccueil $structureAccueil, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $cfa = $user->getCfaEtablissement();
        if (!$cfa) {
            throw $this->createAccessDeniedException();
        }

        $hasAccess = false;
        foreach ($structureAccueil->getProspections() as $prospection) {
            if ($prospection->getCfaEtablissement()?->getId() === $cfa->getId()) {
                $hasAccess = true;
                break;
            }
        }

        if (!$hasAccess) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(StructureAccueilType::class, $structureAccueil);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Structure d\'accueil modifiée avec succès.');
            return $this->redirectToRoute('app_structure_accueil_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('structure_accueil/form.html.twig', [
            'structure_accueil' => $structureAccueil,
            'form' => $form,
            'is_edit' => true,
        ]);
    }

    #[Route('/{id}', name: 'app_structure_accueil_delete', methods: ['DELETE', 'POST'])]
    public function delete(Request $request, StructureAccueil $structureAccueil, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $cfa = $user->getCfaEtablissement();
        if (!$cfa) {
            throw $this->createAccessDeniedException();
        }

        $hasAccess = false;
        foreach ($structureAccueil->getProspections() as $prospection) {
            if ($prospection->getCfaEtablissement()?->getId() === $cfa->getId()) {
                $hasAccess = true;
                break;
            }
        }

        if (!$hasAccess) {
            throw $this->createAccessDeniedException();
        }

        $token = $request->request->get('_token') ?? $request->getPayload()->getString('_token');

        if ($this->isCsrfTokenValid('delete' . $structureAccueil->getId(), $token)) {
            $entityManager->remove($structureAccueil);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_structure_accueil_index', [], Response::HTTP_SEE_OTHER);
    }
}
