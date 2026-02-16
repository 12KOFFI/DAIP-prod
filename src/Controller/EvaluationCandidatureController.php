<?php

namespace App\Controller;

use App\Entity\Critere;
use App\Entity\EvaluationCandidature;
use App\Entity\Notation;
use App\Entity\User;
use App\Form\EvaluationCandidatureType;
use App\Repository\EvaluationCandidatureRepository;
use App\Repository\CandidatureRepository;
use App\Repository\CritereRepository;
use App\Service\CandidatureStatusUpdater;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/evaluation/candidature')]
final class EvaluationCandidatureController extends AbstractController
{
    #[Route(name: 'app_evaluation_candidature_index', methods: ['GET'])]
    public function index(EvaluationCandidatureRepository $evaluationRepository): Response
    {
        $user = $this->getUser();

        if ($this->isGranted('ROLE_ADMIN')) {
            $evaluations = $evaluationRepository->findAllWithRelations();
        } elseif ($this->isGranted('ROLE_JURY') && $user instanceof User) {
            $evaluations = $evaluationRepository->findBy(['user' => $user]);
        } else {
            $evaluations = [];
        }

        return $this->render('evaluationCandidature/index.html.twig', [
            'evaluations' => $evaluations,
        ]);
    }

    #[Route('/new', name: 'app_evaluation_candidature_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, EvaluationCandidatureRepository $evaluationRepository): Response
    {
        $evaluation = new EvaluationCandidature();
        $evaluation->setDateEvaluation(new \DateTime());
        $evaluation->setStatut('en_cours'); // Statut par défaut

        $form = $this->createForm(EvaluationCandidatureType::class, $evaluation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier si une évaluation avec le même type existe déjà pour cette candidature
            $candidature = $evaluation->getCandidature();
            $typeEvaluation = $evaluation->getTypeEvaluation();

            if ($candidature && $typeEvaluation) {
                $existingEvaluation = $evaluationRepository->findOneBy([
                    'candidature' => $candidature,
                    'typeEvaluation' => $typeEvaluation
                ]);

                if ($existingEvaluation) {
                    $libelle = $typeEvaluation->getLibelle();
                    $this->addFlash('error', 'Une évaluation de type "' . $libelle . '" existe déjà pour cette candidature.');
                    return $this->render('evaluationCandidature/form.html.twig', [
                        'evaluation' => $evaluation,
                        'form' => $form,
                        'title' => 'Nouvelle evaluation',
                    ]);
                }
            }

            $entityManager->persist($evaluation);
            $entityManager->flush();

            $this->addFlash('success', 'Évaluation créée avec succès.');
            return $this->redirectToRoute('app_evaluation_candidature_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('evaluationCandidature/form.html.twig', [
            'evaluation' => $evaluation,
            'form' => $form,
            'title' => 'Nouvelle evaluation',
        ]);
    }

    #[Route('/show/{id}', name: 'app_evaluation_candidature_show', methods: ['GET'])]
    public function show(EvaluationCandidature $evaluation): Response
    {
        return $this->render('evaluationCandidature/show.html.twig', [
            'evaluation' => $evaluation,
        ]);
    }

    #[Route('/traitement', name: 'app_evaluation_candidature_traitement', methods: ['GET', 'POST'])]
    public function traitement(Request $request, CandidatureRepository $candidatureRepository, EntityManagerInterface $entityManager, EvaluationCandidatureRepository $evaluationRepository): Response
    {
        $candidature = null;
        $evaluation = null;
        $formEvaluation = null;
        $error = null;

        $numCandidature = $request->get('num_candidature');

        if ($numCandidature) {
            $candidature = $candidatureRepository->findOneBy(['numCandidature' => $numCandidature]);

            if (!$candidature) {
                $error = 'Aucune candidature trouvée avec ce numéro';
            } else {
                // Récupérer les évaluations existantes pour cette candidature
                $evaluationsExistantes = $evaluationRepository->findBy(['candidature' => $candidature]);

                $evaluation = new EvaluationCandidature();
                $evaluation->setCandidature($candidature);
                $evaluation->setDateEvaluation(new \DateTime());
                $evaluation->setStatut('en_cours');

                $formEvaluation = $this->createForm(EvaluationCandidatureType::class, $evaluation, [
                    'candidature' => $candidature
                ]);
                $formEvaluation->handleRequest($request);

                if ($formEvaluation->isSubmitted() && $formEvaluation->isValid()) {
                    // Vérifier si une évaluation avec le même type existe déjà
                    $typeEvaluation = $evaluation->getTypeEvaluation();
                    $existingEvaluation = $evaluationRepository->findOneBy([
                        'candidature' => $candidature,
                        'typeEvaluation' => $typeEvaluation
                    ]);

                    if ($existingEvaluation) {
                        $libelle = $typeEvaluation ? $typeEvaluation->getLibelle() : 'Inconnu';
                        $error = 'Une évaluation de type "' . $libelle . '" existe déjà pour cette candidature.';
                    } else {
                        // Récupérer les notations du formulaire
                        $notationsData = $request->request->all()['notations'] ?? [];
                        $totalPoints = 0;

                        // Vérifier si des critères sont attendus
                        $grille = $evaluation->getGrilleEvaluation();
                        if ($grille && !empty($notationsData)) {
                            // Créer les notations pour chaque critère et calculer le total
                            foreach ($notationsData as $critereId => $notationData) {
                                $critere = $entityManager->getRepository(Critere::class)->find($critereId);

                                if ($critere) {
                                    $note = (int)($notationData['note'] ?? 0);
                                    $totalPoints += $note;

                                    $notation = new Notation();
                                    $notation->setEvaluation($evaluation);
                                    $notation->setCritere($critere);
                                    $notation->setNote($note);
                                    $notation->setCommentaire($notationData['commentaire'] ?? null);

                                    $entityManager->persist($notation);
                                }
                            }

                            // Mettre à jour la note totale de l'évaluation
                            $evaluation->setNoteTotale($totalPoints);
                        }

                        $entityManager->persist($evaluation);
                        $entityManager->flush();

                        $this->addFlash('success', 'Évaluation enregistrée avec succès.');
                        return $this->redirectToRoute('app_evaluation_candidature_traitement');
                    }
                }
            }
        }

        return $this->render('evaluationCandidature/traitement.html.twig', [
            'candidature' => $candidature,
            'num_candidature' => $numCandidature,
            'error' => $error,
            'formEvaluation' => $formEvaluation ? $formEvaluation->createView() : null,
            'evaluationsExistantes' => $evaluationsExistantes ?? []
        ]);
    }

    #[Route('/load-criteres', name: 'evaluation_candidature_load_criteres', methods: ['GET'])]
    public function loadCriteres(Request $request, CritereRepository $critereRepository): Response
    {
        $grilleId = $request->query->get('grille_id');

        if (!$grilleId) {
            return new Response('Grille non spécifiée', 400);
        }

        $criteres = $critereRepository->findBy(['grilleEvaluation' => $grilleId]);

        $totalBareme = array_reduce($criteres, function ($total, $critere) {
            return $total + ($critere->getBareme() ?? 0);
        }, 0);

        return $this->render('evaluationCandidature/criteres_section.html.twig', [
            'criteres' => $criteres,
            'totalBareme' => $totalBareme
        ]);
    }

    #[Route('/{id}/edit', name: 'app_evaluation_candidature_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        EvaluationCandidature $evaluation,
        EntityManagerInterface $entityManager,
        EvaluationCandidatureRepository $evaluationRepository,
        CandidatureStatusUpdater $statusUpdater
    ): Response {
        $form = $this->createForm(EvaluationCandidatureType::class, $evaluation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier si une autre évaluation avec le même type existe déjà pour cette candidature
            $candidature = $evaluation->getCandidature();
            $typeEval = $evaluation->getTypeEvaluation();

            if ($candidature && $typeEval) {
                $existingEvaluation = $evaluationRepository->findOneBy([
                    'candidature' => $candidature,
                    'typeEvaluation' => $typeEval
                ]);

                // Si on trouve une évaluation avec le même type et que ce n'est pas la même
                if ($existingEvaluation && $existingEvaluation->getId() !== $evaluation->getId()) {
                    $this->addFlash('error', 'Une évaluation avec le type "' . $typeEval->getLibelle() . '" existe déjà pour cette candidature.');
                    return $this->render('evaluationCandidature/form.html.twig', [
                        'evaluation' => $evaluation,
                        'form' => $form,
                        'title' => 'Modifier l\'evaluation',
                    ]);
                }
            }

            $evaluation->setDateEvaluation(new \DateTime());

            // Mettre à jour le statut de la candidature
            $statusUpdater->updateFromEvaluation($evaluation);

            $entityManager->flush();

            $this->addFlash('success', 'Évaluation mise à jour avec succès.');
            return $this->redirectToRoute('app_evaluation_candidature_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('evaluationCandidature/form.html.twig', [
            'evaluation' => $evaluation,
            'form' => $form,
            'title' => 'Modifier l\'evaluation',
        ]);
    }

    #[Route('/{id}/update-statut', name: 'app_evaluation_candidature_update_statut', methods: ['POST'])]
    public function updateStatut(Request $request, EvaluationCandidature $evaluation, EntityManagerInterface $entityManager): Response
    {
        $statut = $request->request->get('statut');

        if (in_array($statut, ['en_cours', 'termine', 'annule'])) {
            $evaluation->setStatut($statut);
            $entityManager->flush();
            $this->addFlash('success', 'Statut de l\'évaluation mis à jour.');
        } else {
            $this->addFlash('error', 'Statut invalide.');
        }

        return $this->redirectToRoute('app_evaluation_candidature_show', ['id' => $evaluation->getId()]);
    }
}
