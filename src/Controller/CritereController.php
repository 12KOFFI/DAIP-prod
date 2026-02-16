<?php

namespace App\Controller;

use App\Entity\Critere;
use App\Entity\User;
use App\Form\CritereType;
use App\Repository\CritereRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/critere')]
final class CritereController extends AbstractController
{
    #[Route(name: 'app_critere_index', methods: ['GET'])]
    public function index(CritereRepository $critereRepository): Response
    {
        $user = $this->getUser();

        if ($this->isGranted('ROLE_ADMIN')) {
            // Admin voit tout
            $criteres = $critereRepository->findAll();
        } elseif ($this->isGranted('ROLE_JURY')) {
            // Jury voit seulement ses critères
            $criteres = $critereRepository->findByUser($user);
        } else {
            // Autres rôles n'ont pas accès
            throw new AccessDeniedException();
        }

        return $this->render('critere/index.html.twig', [
            'criteres' => $criteres,
            'is_admin' => $this->isGranted('ROLE_ADMIN'),
            'is_jury' => $this->isGranted('ROLE_JURY'),
        ]);
    }

    #[Route('/new', name: 'app_critere_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Seuls admin et jury peuvent créer
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_JURY')) {
            throw new AccessDeniedException();
        }

        $critere = new Critere();
        $user = $this->getUser();

        if ($user instanceof User) {
            $critere->setUser($user);
        }

        $form = $this->createForm(CritereType::class, $critere, [
            'is_jury' => $this->isGranted('ROLE_JURY'),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($critere);
            $entityManager->flush();

            $this->addFlash('success', 'Critère créé avec succès.');
            return $this->redirectToRoute('app_critere_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('critere/_form.html.twig', [
            'critere' => $critere,
            'form' => $form,
            'title' => 'Nouveau critère',
            'is_jury' => $this->isGranted('ROLE_JURY'),
        ]);
    }

    #[Route('/{id}', name: 'app_critere_show', methods: ['GET'])]
    public function show(Critere $critere): Response
    {
        $user = $this->getUser();

        // Vérifier l'accès
        $this->checkAccess($critere, $user, false);

        $form = $this->createForm(CritereType::class, $critere, [
            'disabled' => true,
            'is_jury' => $this->isGranted('ROLE_JURY'),
        ]);

        return $this->render('critere/_form.html.twig', [
            'critere' => $critere,
            'form' => $form->createView(),
            'title' => 'Détails du critère',
            'show_mode' => true,
            'can_edit' => $this->canEdit($critere, $user),
            'can_delete' => $this->canDelete($critere, $user),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_critere_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Critere $critere, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // Vérifier l'accès en modification
        $this->checkAccess($critere, $user, true);

        $form = $this->createForm(CritereType::class, $critere, [
            'is_jury' => $this->isGranted('ROLE_JURY'),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Critère modifié avec succès.');
            return $this->redirectToRoute('app_critere_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('critere/_form.html.twig', [
            'critere' => $critere,
            'form' => $form,
            'title' => 'Modifier le critère',
        ]);
    }

    #[Route('/{id}', name: 'app_critere_delete', methods: ['POST'])]
    public function delete(Request $request, Critere $critere, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // Vérifier l'accès en suppression
        $this->checkAccess($critere, $user, true);

        if ($this->isCsrfTokenValid('delete' . $critere->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($critere);
            $entityManager->flush();

            $this->addFlash('success', 'Critère supprimé avec succès.');
        } else {
            $this->addFlash('error', 'Token de sécurité invalide.');
        }

        return $this->redirectToRoute('app_critere_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Vérifie l'accès à un critère
     */
    private function checkAccess(Critere $critere, ?User $user, bool $requireOwnership): void
    {
        if (!$user instanceof User) {
            throw new AccessDeniedException('Utilisateur non authentifié.');
        }

        // Admin a toujours accès
        if ($this->isGranted('ROLE_ADMIN')) {
            return;
        }

        // Jury : seulement ses propres critères
        if ($this->isGranted('ROLE_JURY')) {
            if ($critere->getUser() === $user) {
                return;
            }

            if ($requireOwnership) {
                throw new AccessDeniedException('Vous ne pouvez modifier/supprimer que vos propres critères.');
            } else {
                // Pour la visualisation, on peut autoriser à voir plus ?
                // Ici, on autorise la visualisation seulement
                return;
            }
        }

        throw new AccessDeniedException('Accès refusé.');
    }

    /**
     * Vérifie si l'utilisateur peut éditer le critère
     */
    private function canEdit(Critere $critere, User $user): bool
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return true;
        }

        if ($this->isGranted('ROLE_JURY') && $critere->getUser() === $user) {
            return true;
        }

        return false;
    }

    /**
     * Vérifie si l'utilisateur peut supprimer le critère
     */
    private function canDelete(Critere $critere, User $user): bool
    {
        return $this->canEdit($critere, $user);
    }
}
