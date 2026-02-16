<?php

namespace App\Controller;

use App\Entity\ProspectionMetier;
use App\Entity\Prospection;
use App\Entity\User;
use App\Form\ProspectionMetierType;
use App\Repository\ProspectionMetierRepository;
use App\Repository\ProspectionRepository;
use App\Repository\MetierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/prospection-metier')]
final class ProspectionMetierController extends AbstractController
{
    #[Route('/api/prospection-metiers', name: 'app_api_prospection_metier_create', methods: ['POST'])]
    public function apiCreate(
        Request $request,
        ProspectionRepository $prospectionRepository,
        MetierRepository $metierRepository,
        ProspectionMetierRepository $prospectionMetierRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return new JsonResponse(['message' => 'Utilisateur non authentifié.'], Response::HTTP_FORBIDDEN);
        }

        $cfa = $user->getCfaEtablissement();
        if (!$cfa) {
            return new JsonResponse(['message' => 'Aucun CFA associé à cet utilisateur.'], Response::HTTP_FORBIDDEN);
        }

        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload)) {
            return new JsonResponse(['message' => 'Payload JSON invalide.'], Response::HTTP_BAD_REQUEST);
        }

        $prospectionId = $payload['prospectionId'] ?? null;
        $metierId = $payload['metierId'] ?? null;
        $nombrePostes = $payload['nombrePostes'] ?? null;

        if (!$prospectionId || !is_scalar($prospectionId)) {
            return new JsonResponse(['message' => 'Le champ "prospectionId" est obligatoire.'], Response::HTTP_BAD_REQUEST);
        }
        if (!$metierId || !is_scalar($metierId)) {
            return new JsonResponse(['message' => 'Le champ "metierId" est obligatoire.'], Response::HTTP_BAD_REQUEST);
        }
        if ($nombrePostes === null || !is_scalar($nombrePostes) || (int) $nombrePostes < 1) {
            return new JsonResponse(['message' => 'Le champ "nombrePostes" est obligatoire et doit être >= 1.'], Response::HTTP_BAD_REQUEST);
        }

        $prospection = $prospectionRepository->find((int) $prospectionId);
        if (!$prospection) {
            return new JsonResponse(['message' => 'Prospection introuvable.'], Response::HTTP_BAD_REQUEST);
        }

        if ($prospection->getCfaEtablissement()?->getId() !== $cfa->getId()) {
            return new JsonResponse(['message' => 'Vous n\'êtes pas autorisé à modifier cette prospection.'], Response::HTTP_FORBIDDEN);
        }

        $metier = $metierRepository->find((int) $metierId);
        if (!$metier) {
            return new JsonResponse(['message' => 'Métier introuvable.'], Response::HTTP_BAD_REQUEST);
        }

        // Vérifier que le métier est bien lié à une filière du CFA
        $isMetierValid = false;
        foreach ($metier->getFilieres() as $filiere) {
            if ($cfa->getFilieres()->contains($filiere)) {
                $isMetierValid = true;
                break;
            }
        }

        if (!$isMetierValid) {
            return new JsonResponse(['message' => 'Ce métier n\'appartient pas à une filière de votre CFA.'], Response::HTTP_FORBIDDEN);
        }

        // Vérifier que la somme des postes ne dépasse pas l'effectif du CFA
        $effectifCfa = $cfa->getEffectifs() ?? 0;
        $sommePosterActuelle = $prospectionMetierRepository->getSumPostesByProspection((int) $prospectionId);
        $nouvellesSomme = $sommePosterActuelle + (int) $nombrePostes;

        if ($nouvellesSomme > $effectifCfa) {
            $postesRestants = $effectifCfa - $sommePosterActuelle;
            return new JsonResponse([
                'message' => sprintf(
                    'Le nombre de postes dépasse l\'effectif disponible du CFA (%d). Postes déjà prospectés : %d. Postes restants : %d.',
                    $effectifCfa,
                    $sommePosterActuelle,
                    max(0, $postesRestants)
                )
            ], Response::HTTP_BAD_REQUEST);
        }

        $prospectionMetier = new ProspectionMetier();
        $prospectionMetier->setProspection($prospection);
        $prospectionMetier->setMetier($metier);
        $prospectionMetier->setNombrePostes((int) $nombrePostes);

        $entityManager->persist($prospectionMetier);
        $entityManager->flush();

        return new JsonResponse([
            'prospectionMetierId' => $prospectionMetier->getId(),
        ], Response::HTTP_CREATED);
    }

    #[Route(name: 'app_prospection_metier_index', methods: ['GET'])]
    public function index(ProspectionMetierRepository $prospectionMetierRepository): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $cfa = $user->getCfaEtablissement();
        return $this->render('prospection_metier/index.html.twig', [
            'prospection_metiers' => $cfa ? $prospectionMetierRepository->findByCfaEtablissement($cfa->getId()) : [],
        ]);
    }

    #[Route('/new', name: 'app_prospection_metier_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        ProspectionRepository $prospectionRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $cfa = $user->getCfaEtablissement();
        if (!$cfa) {
            throw $this->createAccessDeniedException();
        }

        $prospectionMetier = new ProspectionMetier();
        $prospectionId = $request->query->get('prospectionId');
        if (is_numeric($prospectionId)) {
            $prospection = $prospectionRepository->find((int) $prospectionId);
            if ($prospection && $prospection->getCfaEtablissement()?->getId() === $cfa->getId()) {
                $prospectionMetier->setProspection($prospection);
            }
        }

        $form = $this->createForm(ProspectionMetierType::class, $prospectionMetier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($prospectionMetier);
            $entityManager->flush();

            return $this->redirectToRoute('app_prospection_metier_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('prospection_metier/form.html.twig', [
            'prospection_metier' => $prospectionMetier,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_prospection_metier_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(
        Request $request, 
        ProspectionMetier $prospectionMetier, 
        ProspectionMetierRepository $prospectionMetierRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $cfa = $user->getCfaEtablissement();
        if (!$cfa || $prospectionMetier->getProspection()?->getCfaEtablissement()?->getId() !== $cfa->getId()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(ProspectionMetierType::class, $prospectionMetier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier que la somme des postes ne dépasse pas l'effectif du CFA
            $prospection = $prospectionMetier->getProspection();
            if ($prospection) {
                $effectifCfa = $cfa->getEffectifs() ?? 0;
                $sommePosterActuelle = $prospectionMetierRepository->getSumPostesByProspection(
                    $prospection->getId(),
                    $prospectionMetier->getId() // Exclure l'enregistrement en cours de modification
                );
                $nouvellesSomme = $sommePosterActuelle + $prospectionMetier->getNombrePostes();

                if ($nouvellesSomme > $effectifCfa) {
                    $postesRestants = $effectifCfa - $sommePosterActuelle;
                    $this->addFlash('error', sprintf(
                        'Le nombre de postes dépasse l\'effectif disponible du CFA (%d). Postes déjà prospectés : %d. Postes restants : %d.',
                        $effectifCfa,
                        $sommePosterActuelle,
                        max(0, $postesRestants)
                    ));

                    return $this->render('prospection_metier/form.html.twig', [
                        'prospection_metier' => $prospectionMetier,
                        'form' => $form,
                    ]);
                }
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_prospection_metier_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('prospection_metier/form.html.twig', [
            'prospection_metier' => $prospectionMetier,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_prospection_metier_delete', methods: ['DELETE', 'POST'])]
    public function delete(Request $request, ProspectionMetier $prospectionMetier, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $cfa = $user->getCfaEtablissement();
        if (!$cfa || $prospectionMetier->getProspection()?->getCfaEtablissement()?->getId() !== $cfa->getId()) {
            throw $this->createAccessDeniedException();
        }

        $token = $request->request->get('_token') ?? $request->getPayload()->getString('_token');

        if ($this->isCsrfTokenValid('delete' . $prospectionMetier->getId(), $token)) {
            $entityManager->remove($prospectionMetier);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_prospection_metier_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/metiers/{id}', name: 'app_prospection_metier_metiers', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function metiersByProspection(Prospection $prospection): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return new JsonResponse([], Response::HTTP_FORBIDDEN);
        }

        $cfa = $prospection->getCfaEtablissement();

        if (!$cfa || $user->getCfaEtablissement()?->getId() !== $cfa->getId()) {
            return new JsonResponse([], Response::HTTP_FORBIDDEN);
        }

        $filieres = $cfa->getFilieres();

        $data = [];
        foreach ($filieres as $filiere) {
            foreach ($filiere->getMetier() as $metier) {
                $data[] = [
                    'id' => $metier->getId(),
                    'nom' => $metier->getNom(),
                ];
            }
        }

        // Supprimer les doublons
        $uniqueData = [];
        $seenIds = [];
        foreach ($data as $item) {
            if (!in_array($item['id'], $seenIds)) {
                $uniqueData[] = $item;
                $seenIds[] = $item['id'];
            }
        }

        return new JsonResponse($uniqueData);
    }
}
