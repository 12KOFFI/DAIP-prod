<?php

namespace App\Service;

use App\Entity\EvaluationCandidature;
use App\Entity\Candidature;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class CandidatureStatusUpdater
{
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    public function updateFromEvaluation(EvaluationCandidature $evaluation): void
    {
        $candidature = $evaluation->getCandidature();
        if (!$candidature) {
            $this->logger->warning('Évaluation sans candidature associée', [
                'evaluation_id' => $evaluation->getId()
            ]);
            return;
        }

        $this->logger->debug('Mise à jour du statut depuis l\'évaluation', [
            'candidature_id' => $candidature->getId(),
            'evaluation_statut' => $evaluation->getStatut(),
            'evaluation_libelle' => $evaluation->getLibelle(),
            'candidature_statut_actuel' => $candidature->getStatut()
        ]);

        // Vérifier si c'est une évaluation d'étude de dossier
        if (!$this->isEtudeDossierEvaluation($evaluation)) {
            $this->logger->debug('Évaluation non pertinente pour mise à jour statut', [
                'type' => 'not_etude_dossier'
            ]);
            return;
        }

        // Si le dossier est déjà marqué comme incomplet, on ne fait rien
        if ($this->isCandidatureIncomplete($candidature)) {
            $this->logger->debug('Candidature déjà incomplète, pas de mise à jour');
            return;
        }

        // Mise à jour du statut en fonction du résultat de l'évaluation
        $this->updateCandidatureStatus($candidature, $evaluation->getStatut());
    }

    private function isEtudeDossierEvaluation(EvaluationCandidature $evaluation): bool
    {
        $libelle = strtolower($evaluation->getLibelle() ?? '');
        
        // Vérifier si le libellé contient "étude" et "dossier"
        $hasEtude = strpos($libelle, 'étude') !== false || strpos($libelle, 'etude') !== false;
        $hasDossier = strpos($libelle, 'dossier') !== false;
        
        return $hasEtude && $hasDossier;
    }

    private function isCandidatureIncomplete(Candidature $candidature): bool
    {
        $currentStatus = strtolower($candidature->getStatut() ?? '');
        return $currentStatus === 'incomplete' || $currentStatus === 'incomplet';
    }

    private function updateCandidatureStatus(Candidature $candidature, string $evaluationStatus): void
    {
        // Utiliser directement les valeurs du formulaire
        switch ($evaluationStatus) {
            case 'acceptee':
                $candidature->setStatut('dossier validé');
                $this->logger->info('Statut candidature mis à jour: dossier validé', [
                    'candidature_id' => $candidature->getId(),
                    'ancien_statut' => $candidature->getStatut(),
                    'nouveau_statut' => 'dossier validé'
                ]);
                break;
                
            case 'rejetee':
                $candidature->setStatut('dossier rejeté');
                $this->logger->info('Statut candidature mis à jour: dossier refusé', [
                    'candidature_id' => $candidature->getId(),
                    'ancien_statut' => $candidature->getStatut(),
                    'nouveau_statut' => 'dossier refusé'
                ]);
                break;
                
            default:
                $this->logger->warning('Statut d\'évaluation non géré', [
                    'candidature_id' => $candidature->getId(),
                    'statut_non_gere' => $evaluationStatus
                ]);
                return; // Ne pas persister/flusher si statut non géré
        }

        $this->entityManager->persist($candidature);
        $this->entityManager->flush();
    }
}