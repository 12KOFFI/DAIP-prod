<?php

namespace App\Controller;

use App\Entity\Prospection;
use App\Entity\User;
use App\Form\ProspectionType;
use App\Repository\ProspectionRepository;
use App\Repository\StructureAccueilRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/prospection')]
final class ProspectionController extends AbstractController
{
    #[Route('/api/prospections', name: 'app_api_prospection_create', methods: ['POST'])]
    public function apiCreate(
        Request $request,
        StructureAccueilRepository $structureAccueilRepository,
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

        $structureAccueilId = $payload['structureAccueilId'] ?? null;
        $dateString = $payload['date'] ?? null;

        if (!$structureAccueilId || !is_scalar($structureAccueilId)) {
            return new JsonResponse(['message' => 'Le champ "structureAccueilId" est obligatoire.'], Response::HTTP_BAD_REQUEST);
        }
        if (!$dateString || !is_string($dateString)) {
            return new JsonResponse(['message' => 'Le champ "date" est obligatoire (format YYYY-MM-DD).'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $date = new \DateTime($dateString);
        } catch (\Throwable) {
            return new JsonResponse(['message' => 'Date invalide. Utilisez le format YYYY-MM-DD.'], Response::HTTP_BAD_REQUEST);
        }

        $structureAccueil = $structureAccueilRepository->find((int) $structureAccueilId);
        if (!$structureAccueil) {
            return new JsonResponse(['message' => 'Structure d\'accueil introuvable.'], Response::HTTP_BAD_REQUEST);
        }

        $prospection = new Prospection();
        $prospection->setStructureAcceuil($structureAccueil);
        $prospection->setDate($date);
        $prospection->setCfaEtablissement($cfa);
        $prospection->setUser($user);

        $entityManager->persist($prospection);
        $entityManager->flush();

        return new JsonResponse([
            'prospectionId' => $prospection->getId(),
        ], Response::HTTP_CREATED);
    }

    #[Route(name: 'app_prospection_index', methods: ['GET'])]
    public function index(ProspectionRepository $prospectionRepository): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $cfa = $user->getCfaEtablissement();
        return $this->render('propection/index.html.twig', [
            'prospections' => $cfa ? $prospectionRepository->findBy(['cfaEtablissement' => $cfa]) : [],
        ]);
    }

    #[Route('/new', name: 'app_prospection_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        return $this->redirectToRoute('app_structure_accueil_new', ['flow' => 'prospection'], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/edit', name: 'app_prospection_edit', methods: ['GET', 'POST'], requirements: ['id' => '\\d+'])]
    public function edit(Request $request, Prospection $prospection, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $cfa = $user->getCfaEtablissement();
        if (!$cfa || $prospection->getCfaEtablissement()?->getId() !== $cfa->getId()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(ProspectionType::class, $prospection);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_prospection_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('propection/form.html.twig', [
            'prospection' => $prospection,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_prospection_delete', methods: ['DELETE', 'POST'])]
    public function delete(Request $request, Prospection $prospection, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $cfa = $user->getCfaEtablissement();
        if (!$cfa || $prospection->getCfaEtablissement()?->getId() !== $cfa->getId()) {
            throw $this->createAccessDeniedException();
        }

        $token = $request->request->get('_token') ?? $request->getPayload()->getString('_token');

        if ($this->isCsrfTokenValid('delete' . $prospection->getId(), $token)) {
            $entityManager->remove($prospection);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_prospection_index', [], Response::HTTP_SEE_OTHER);
    }
}
