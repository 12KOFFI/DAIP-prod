<?php

namespace App\Controller;

use App\Entity\JuryDate;
use App\Entity\Jury;
use App\Form\JuryDateType;
use App\Entity\User;
use App\Repository\JuryDateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/jury/date')]
final class JuryDateController extends AbstractController
{
    #[Route('/', name: 'app_jury_date_index', methods: ['GET'])]
    public function index(JuryDateRepository $juryDateRepository): Response
    {
        $this->checkAccess();

        if ($this->isGranted('ROLE_ADMIN')) {
            $juryDates = $juryDateRepository->findAll();
        } else {
            // ROLE_JURY : n'afficher que les dates liées au jury du user
            $jury = $this->getCurrentJury();
            $juryDates = $jury ? $juryDateRepository->findBy(['jury' => $jury]) : [];
        }

        return $this->render('jury_date/index.html.twig', [
            'jury_dates' => $juryDates,
        ]);
    }

    #[Route('/new', name: 'app_jury_date_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->checkAccess();

        $juryDate = new JuryDate();
        // Si user est jury, forcer l'association au jury du user pour éviter création pour un autre jury
        if (!$this->isGranted('ROLE_ADMIN')) {
            $jury = $this->getCurrentJury();
            if ($jury instanceof Jury) {
                $juryDate->setJury($jury);
            }
        }

        $form = $this->createForm(JuryDateType::class, $juryDate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Si user jury, s'assurer que la valeur jury du formulaire n'écrase pas l'association
            if (!$this->isGranted('ROLE_ADMIN')) {
                $jury = $this->getCurrentJury();
                if ($jury instanceof Jury) {
                    $juryDate->setJury($jury);
                }
            }

            $entityManager->persist($juryDate);
            $entityManager->flush();

            return $this->redirectToRoute('app_jury_date_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('jury_date/form.html.twig', [
            'jury_date' => $juryDate,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_jury_date_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, JuryDate $juryDate, EntityManagerInterface $entityManager): Response
    {
        $this->checkAccess();

        // Si user jury, vérifier qu'il édite uniquement ses propres JuryDate
        if (!$this->isGranted('ROLE_ADMIN')) {
            $jury = $this->getCurrentJury();
            if (!$jury || $juryDate->getJury()?->getId() !== $jury->getId()) {
                throw $this->createAccessDeniedException('Accès refusé.');
            }
        }

        $form = $this->createForm(JuryDateType::class, $juryDate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Si user jury, garantir que le jury reste le bon
            if (!$this->isGranted('ROLE_ADMIN')) {
                $jury = $this->getCurrentJury();
                if ($jury instanceof Jury) {
                    $juryDate->setJury($jury);
                }
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_jury_date_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('jury_date/form.html.twig', [
            'jury_date' => $juryDate,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_jury_date_delete', methods: ['POST'])]
    public function delete(Request $request, JuryDate $juryDate, EntityManagerInterface $entityManager): Response
    {
        $this->checkAccess();

        // Vérification ownership pour les jurys
        if (!$this->isGranted('ROLE_ADMIN')) {
            $jury = $this->getCurrentJury();
            if (!$jury || $juryDate->getJury()?->getId() !== $jury->getId()) {
                throw $this->createAccessDeniedException('Accès refusé.');
            }
        }

        // Correction : récupération du token CSRF depuis $request->request
        if ($this->isCsrfTokenValid('delete' . $juryDate->getId(), $request->request->get('_token'))) {
            $entityManager->remove($juryDate);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_jury_date_index', [], Response::HTTP_SEE_OTHER);
    }

    // Méthodes utilitaires
    private function checkAccess(): void
    {
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_JURY')) {
            throw $this->createAccessDeniedException('Accès réservé aux administrateurs et aux jurys.');
        }
    }

    private function getCurrentJury(): ?Jury
    {
        $user = $this->getUser();
        if (!$user) {
            return null;
        }

        // Cas 1 : l'entité User expose getJury()
        if ($user instanceof User) {
            return $user->getJury();
        }

        // Cas 2 : l'utilisateur est lui-même une entité Jury
        if ($user instanceof Jury) {
            return $user;
        }

        // Pas de relation détectée
        return null;
    }
}