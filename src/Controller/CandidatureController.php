<?php

namespace App\Controller;

use App\Entity\Candidature;
use App\Entity\User;
use App\Form\CandidatureType;
use App\Repository\CandidatureRepository;
use App\Repository\ConvocationRepository;
use App\Repository\DiplomeRepository;
use App\Repository\MetierRepository;
use App\Service\CandidatureFileManager;
use App\Service\CandidaturePersonalInfoService;
use App\Service\CandidatureFilterService;
use App\Service\CandidatureAuthorizationService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/candidature')]
final class CandidatureController extends AbstractController
{
    private const JURY_STATUS_FILTERS = [
        'acceptee' => 'Candidatures acceptées',
        'rejetee' => 'Candidatures rejetées',
        'incomplete' => 'Dossiers incomplets',
        'en_attente' => 'En attente',
    ];

    private const JURY_EVALUATION_TYPES = [
        'etudeDossier' => 'Étude de dossier',
        'entretienMotivation' => 'Entretien motivation',
        'testTechnique' => 'Test technique',
        'visiteMedicale' => 'Visite médicale',
    ];

    private MetierRepository $metierRepository;
    private DiplomeRepository $diplomeRepository;
    private CandidaturePersonalInfoService $personalInfoService;
    private CandidatureFilterService $filterService;
    private CandidatureAuthorizationService $authorizationService;
    private LoggerInterface $logger;

    public function __construct(
        MetierRepository $metierRepository,
        DiplomeRepository $diplomeRepository,
        CandidaturePersonalInfoService $personalInfoService,
        CandidatureFilterService $filterService,
        CandidatureAuthorizationService $authorizationService,
        LoggerInterface $logger
    ) {
        $this->metierRepository = $metierRepository;
        $this->diplomeRepository = $diplomeRepository;
        $this->personalInfoService = $personalInfoService;
        $this->filterService = $filterService;
        $this->authorizationService = $authorizationService;
        $this->logger = $logger;
    }

    #[Route('/', name: 'app_candidature_index', methods: ['GET'])]
    public function index(
        Request $request,
        CandidatureRepository $candidatureRepository
    ): Response {
        try {
            $user = $this->getUser();
            if (!$user instanceof User) {
                throw new AccessDeniedException('Utilisateur non authentifié.');
            }

            // Vérifier l'accès à la liste
            $this->authorizationService->checkViewAccess();

            // Récupérer les paramètres de filtrage
            $type = $request->query->get('type');
            $nom = $request->query->get('nom');
            $juryStatus = $request->query->get('jury_status');
            $juryEvaluation = $request->query->get('jury_evaluation');

            // Récupérer les candidatures selon le rôle
            if ($this->isGranted('ROLE_ADMIN')) {
                $candidatures = $this->filterService->filterForAdmin($type, $nom);
            } elseif ($this->isGranted('ROLE_JURY')) {
                $candidatures = $this->filterService->filterForJury($user, $type, $nom, $juryStatus, $juryEvaluation);
            } else {
                $candidatures = $this->filterService->filterForCandidate($user, $type, $nom);
            }

            // Récupérer les filtres disponibles
            $role = $this->getUserRole();
            $filters = $this->filterService->getAvailableFilters($role, $type, $candidatureRepository);

            return $this->render('candidature/index.html.twig', [
                'candidatures' => $candidatures,
                'recrutements' => $filters['recrutements'],
                'formations' => $filters['formations'],
                'vaes' => $filters['vaes'],
                'current_type' => $type,
                'current_nom' => $nom,
                'jury_status_options' => self::JURY_STATUS_FILTERS,
                'jury_evaluation_types' => self::JURY_EVALUATION_TYPES,
                'current_jury_status' => $juryStatus,
                'current_jury_evaluation' => $juryEvaluation,
                'is_jury' => $this->isGranted('ROLE_JURY'),
            ]);
        } catch (AccessDeniedException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_home');
        }
    }

    #[Route('/new', name: 'app_candidature_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        CandidatureFileManager $fileManager,
        CandidatureRepository $candidatureRepository
    ): Response {
        try {
            // Empêcher l'accès aux administrateurs et jurys
            if ($this->isGranted('ROLE_ADMIN')) {
                $this->addFlash('error', 'Les administrateurs ne peuvent pas créer de nouvelles candidatures.');
                return $this->redirectToRoute('app_candidature_index');
            }

            if ($this->isGranted('ROLE_JURY')) {
                $this->addFlash('error', 'Les jurys ne peuvent pas créer de candidatures.');
                return $this->redirectToRoute('app_candidature_index');
            }

            $user = $this->getUser();
            if (!$user instanceof User) {
                $this->addFlash('error', 'Veuillez vous connecter pour soumettre une candidature. Si vous êtes nouveau, veuillez d\'abord créer un compte (s\'inscrire).');
                if ($request->hasSession()) {
                    $request->getSession()->set('_security.main.target_path', $request->getUri());
                }
                return $this->redirectToRoute('app_login');
            }

            $candidature = new Candidature();
            $type = $request->query->get('type');
            $recrutementId = $request->query->get('recrutementId');

            if (!$recrutementId) {
                $this->addFlash('error', 'Aucun recrutement spécifié.');
                return $this->redirectToRoute('app_candidature_index');
            }

            $recrutement = $entityManager->getRepository(\App\Entity\Recrutement::class)->find($recrutementId);
            if (!$recrutement) {
                $this->addFlash('error', 'Recrutement introuvable.');
                return $this->redirectToRoute('app_candidature_index');
            }

            $candidature->setRecrutement($recrutement);
            $metiers = $this->metierRepository->findByRecrutement($recrutement);
            $diplomes = $this->diplomeRepository->findAll();
            $hidePersonalInfoSection = false;

            $candidature->setUser($user);
            $candidature->setEmail($user->getUserIdentifier());

            // Récupérer la dernière candidature pour pré-remplir les infos
            $previousCandidature = $candidatureRepository->findLatestByUser($user);
            if ($previousCandidature instanceof Candidature) {
                $hidePersonalInfoSection = true;
                $this->personalInfoService->copyPersonalInformation($candidature, $previousCandidature);
            }

            $form = $this->createForm(CandidatureType::class, $candidature, [
                'context_type' => $type,
                'metiers' => $metiers,
            ]);

            $form->handleRequest($request);

            $projetNom = $request->query->get('projet');
            $title = $projetNom ? 'Candidature - ' . $projetNom : 'Nouvelle candidature';

            if ($form->isSubmitted() && $form->isValid()) {
                // Garantir l'association
                if (!$candidature->getRecrutement()) {
                    $candidature->setRecrutement($recrutement);
                }
                $candidature->setUser($user);
                $candidature->setEmail($user->getUserIdentifier());

                // Sauvegarder avec numéro auto
                $candidatureRepository->saveWithAutoNumber($candidature);

                // Traiter les fichiers
                $fileManager->processCandidatureFiles($form, $candidature);
                $entityManager->flush();

                $this->logger->info('Nouvelle candidature créée', [
                    'id' => $candidature->getId(),
                    'user' => $user->getId(),
                    'recrutement' => $recrutement->getId(),
                ]);

                $this->addFlash('success', 'Candidature créée avec succès.');
                return $this->redirectToRoute('app_candidature_index', [], Response::HTTP_SEE_OTHER);
            }

            return $this->render('candidature/form.html.twig', [
                'candidature' => $candidature,
                'recrutement' => $recrutement,
                'form' => $form,
                'title' => $title,
                'projet_nom' => $projetNom,
                'metiers' => $metiers,
                'type' => $type,
                'hide_personal_info' => $hidePersonalInfoSection,
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Erreur création candidature', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->addFlash('error', 'Une erreur est survenue lors de la création de la candidature.');
            return $this->redirectToRoute('app_candidature_index');
        }
    }

    #[Route('/voir/{id}', name: 'app_candidature_show', methods: ['GET'])]
    public function show(Candidature $candidature, ConvocationRepository $convocationRepository): Response
    {
        try {
            $user = $this->getUser();
            if (!$user instanceof User) {
                throw new AccessDeniedException('Utilisateur non authentifié.');
            }

            // Vérifier l'accès
            $this->authorizationService->checkViewAccess($candidature, $user);

            $convocation = $convocationRepository->findOneBy([
                'candidature' => $candidature,
            ], [
                'createdAt' => 'DESC',
            ]);

            return $this->render('candidature/show.html.twig', [
                'candidature' => $candidature,
                'convocation' => $convocation,
                'can_edit' => $this->authorizationService->canEdit($candidature, $user),
                'is_jury' => $this->isGranted('ROLE_JURY'),
            ]);
        } catch (AccessDeniedException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_candidature_index');
        }
    }

    #[Route('/modifier/{id}', name: 'app_candidature_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Candidature $candidature,
        EntityManagerInterface $entityManager,
        CandidatureFileManager $fileManager
    ): Response {
        try {
            $user = $this->getUser();
            if (!$user instanceof User) {
                throw new AccessDeniedException('Utilisateur non authentifié.');
            }

            // Vérifier les droits de modification
            $this->authorizationService->checkEditAccess($candidature, $user);

            // Sauvegarder les relations originales
            $originalRecrutement = $candidature->getRecrutement();
            $originalFormation = $candidature->getFormation();
            $originalVae = $candidature->getVae();

            // Déterminer le type et les métiers
            $type = null;
            $metiers = [];

            if ($recrutement = $candidature->getRecrutement()) {
                $type = 'recrutement';
                $metiers = $recrutement->getMetiers();
            } elseif ($candidature->getFormation()) {
                $type = 'formation';
            } elseif ($candidature->getVae()) {
                $type = 'vae';
            }

            $diplomes = $this->diplomeRepository->findAll();

            $form = $this->createForm(CandidatureType::class, $candidature, [
                'context_type' => $type,
                'metiers' => $metiers,
                'diplomes' => $this->formatDiplomesForChoice($diplomes),
                'is_edit' => true,
                'is_admin' => $this->isGranted('ROLE_ADMIN'),
            ]);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // Réassocier les relations si perdues
                if (!$candidature->getRecrutement() && $originalRecrutement) {
                    $candidature->setRecrutement($originalRecrutement);
                }
                if (!$candidature->getFormation() && $originalFormation) {
                    $candidature->setFormation($originalFormation);
                }
                if (!$candidature->getVae() && $originalVae) {
                    $candidature->setVae($originalVae);
                }

                // Traiter les fichiers
                $fileManager->processCandidatureFiles($form, $candidature);

                $entityManager->flush();

                $this->logger->info('Candidature modifiée', [
                    'id' => $candidature->getId(),
                    'user' => $user->getId(),
                    'role' => $this->getUserRole(),
                ]);

                $this->addFlash('success', 'Candidature modifiée avec succès.');
                return $this->redirectToRoute('app_candidature_index', [], Response::HTTP_SEE_OTHER);
            }

            return $this->render('candidature/form.html.twig', [
                'candidature' => $candidature,
                'form' => $form,
                'title' => 'Modifier la candidature',
                'type' => $type,
                'metiers' => $metiers,
                'projet_nom' => $candidature->getRecrutement()?->getProjet()?->getNom(),
                'is_edit' => true,
                'is_admin' => $this->isGranted('ROLE_ADMIN'),
            ]);
        } catch (AccessDeniedException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_candidature_show', ['id' => $candidature->getId()]);
        } catch (\Exception $e) {
            $this->logger->error('Erreur modification candidature', [
                'id' => $candidature->getId(),
                'error' => $e->getMessage(),
            ]);

            $this->addFlash('error', 'Une erreur est survenue lors de la modification.');
            return $this->redirectToRoute('app_candidature_show', ['id' => $candidature->getId()]);
        }
    }

    #[Route('/delete/{id}', name: 'app_candidature_delete', methods: ['DELETE'])]
    public function delete(
        Request $request,
        Candidature $candidature,
        EntityManagerInterface $entityManager,
        CandidatureFileManager $fileManager
    ): Response {
        try {
            // 1. Vérifier l'utilisateur
            $user = $this->getUser();
            if (!$user instanceof User) {
                throw new AccessDeniedException('Utilisateur non authentifié.');
            }

            // 2. Vérifier CSRF
            if (!$this->isCsrfTokenValid('delete' . $candidature->getId(), $request->getPayload()->getString('_token'))) {
                $this->addFlash('error', 'Token de sécurité invalide.');
                return $this->redirectToRoute('app_candidature_show', ['id' => $candidature->getId()]);
            }

            // 3. Vérifier les droits
            $this->authorizationService->checkDeleteAccess($candidature, $user);

            // 4. Supprimer
            $fileManager->removeCandidatureFiles($candidature);
            $entityManager->remove($candidature);
            $entityManager->flush();

            $this->addFlash('success', 'Candidature supprimée avec succès.');
        } catch (AccessDeniedException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_candidature_show', ['id' => $candidature->getId()]);
        } catch (\Exception $e) {
            $this->logger->error('Erreur suppression candidature', [
                'id' => $candidature->getId(),
                'error' => $e->getMessage()
            ]);

            $this->addFlash('error', 'Erreur lors de la suppression.');
            return $this->redirectToRoute('app_candidature_show', ['id' => $candidature->getId()]);
        }

        return $this->redirectToRoute('app_candidature_index', [], Response::HTTP_SEE_OTHER);
    }

    private function getUserRole(): string
    {
        $roles = $this->getUser()?->getRoles() ?? [];

        if (in_array('ROLE_ADMIN', $roles)) {
            return 'ROLE_ADMIN';
        }
        if (in_array('ROLE_JURY', $roles)) {
            return 'ROLE_JURY';
        }
        if (in_array('ROLE_CANDIDAT', $roles)) {
            return 'ROLE_CANDIDAT';
        }

        return 'ROLE_USER';
    }

    /**
     * Formate les diplômes pour le ChoiceType
     */
    private function formatDiplomesForChoice(array $diplomes): array
    {
        $choices = [];
        foreach ($diplomes as $diplome) {
            $choices[$diplome->getLibelle()] = $diplome->getId();
        }
        return $choices;
    }

    #[Route('/api/metiers-by-secteur/{secteurId}', name: 'app_candidature_metiers_by_secteur', methods: ['GET'])]
    public function getMetiersBySecteur(int $secteurId, EntityManagerInterface $entityManager): Response
    {
        $metiers = $entityManager->getRepository(\App\Entity\Metier::class)
            ->findBy(['secteur' => $secteurId], ['nom' => 'ASC']);

        $data = array_map(function($metier) {
            return [
                'id' => $metier->getId(),
                'nom' => $metier->getNom(),
                'description' => $metier->getDescription()
            ];
        }, $metiers);

        return $this->json($data);
    }

    #[Route('/api/cfa-by-metier/{metierId}', name: 'app_candidature_cfa_by_metier', methods: ['GET'])]
    public function getCfaByMetier(int $metierId, EntityManagerInterface $entityManager): Response
    {
        $cfaMetiers = $entityManager->getRepository(\App\Entity\CfaMetier::class)
            ->findBy(['metier' => $metierId]);

        $cfaList = [];
        foreach ($cfaMetiers as $cfaMetier) {
            $cfa = $cfaMetier->getCfaEtablissement();
            if ($cfa && !isset($cfaList[$cfa->getId()])) {
                $cfaList[$cfa->getId()] = [
                    'id' => $cfa->getId(),
                    'nom' => $cfa->getNomEtablissement(),
                    'region' => $cfa->getRegion(),
                    'email' => $cfa->getEmail()
                ];
            }
        }

        return $this->json(array_values($cfaList));
    }
}
