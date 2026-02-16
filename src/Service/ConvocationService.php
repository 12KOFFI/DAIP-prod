<?php

namespace App\Service;

use App\Repository\CandidatureRepository;
use App\Repository\ConvocationRepository;
use App\Repository\JuryDateRepository;
use Doctrine\ORM\EntityManagerInterface;

class ConvocationService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CandidatureRepository $candidatureRepository,
        private readonly JuryDateRepository $juryDateRepository,
        private readonly ConvocationRepository $convocationRepository,
    ) {
    }

    public function generateForProgramEvaluation(int $programEvaluationId): array
    {
        $conn = $this->entityManager->getConnection();

        $created = 0;
        $skippedAlreadyExists = 0;
        $skippedNoSlots = 0;

        $conn->beginTransaction();

        try {
            $candidats = $this->candidatureRepository->findAdmissiblesEtudeDossierAcceptee();
            $slots = $this->juryDateRepository->findAvailableSlotsForProgramEvaluation($programEvaluationId);

            $slotIndex = 0;
            $remainingInSlot = isset($slots[0]) ? (int) $slots[0]['remaining_capacity'] : 0;

            foreach ($candidats as $candidat) {
                $candidatureId = (int) ($candidat['id'] ?? 0);
                if ($candidatureId <= 0) {
                    continue;
                }

                if ($this->convocationRepository->existsForCandidatureAndProgramEvaluation($candidatureId, $programEvaluationId)) {
                    $skippedAlreadyExists++;
                    continue;
                }

                while ($remainingInSlot <= 0) {
                    $slotIndex++;
                    if (!isset($slots[$slotIndex])) {
                        $skippedNoSlots++;
                        continue 2;
                    }
                    $remainingInSlot = (int) $slots[$slotIndex]['remaining_capacity'];
                }

                $juryDateId = (int) $slots[$slotIndex]['id'];
                $this->convocationRepository->insertConvocation(
                    candidatureId: $candidatureId,
                    programEvaluationId: $programEvaluationId,
                    juryDateId: $juryDateId,
                    statut: 'convoqué'
                );

                $created++;
                $remainingInSlot--;
            }

            $conn->commit();

            return [
                'created' => $created,
                'skipped_already_exists' => $skippedAlreadyExists,
                'skipped_no_slots' => $skippedNoSlots,
            ];
        } catch (\Throwable $e) {
            $conn->rollBack();
            throw $e;
        }
    }
}
