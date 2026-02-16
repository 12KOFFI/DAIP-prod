<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Jury;
use App\Repository\CandidatureRepository;
use Symfony\Component\Security\Core\User\UserInterface;

class CandidatureFilterService
{
    private CandidatureRepository $candidatureRepository;

    public function __construct(CandidatureRepository $candidatureRepository)
    {
        $this->candidatureRepository = $candidatureRepository;
    }

    public function filterForAdmin(?string $type, ?string $nom): array
    {
        return $this->candidatureRepository->findWithFilters($type, $nom);
    }

    public function filterForJury(UserInterface $user, ?string $type, ?string $nom, ?string $juryStatus, ?string $juryEvaluation): array
    {
        if (!$user instanceof User || !$user->getJury()) {
            return [];
        }

        $jury = $user->getJury();

        $recrutementIds = array_filter(
            array_map(
                static fn($recrutement) => $recrutement?->getId(),
                $jury->getRecrutements()->toArray()
            )
        );

        $formationIds = array_filter(
            array_map(
                static fn($formation) => $formation?->getId(),
                $jury->getFormations()->toArray()
            )
        );

        $vaeIds = array_filter(
            array_map(
                static fn($vae) => $vae?->getId(),
                $jury->getVaes()->toArray()
            )
        );

        return $this->candidatureRepository->findByAssignments(
            $recrutementIds,
            $formationIds,
            $vaeIds,
            $type,
            $nom,
            $juryStatus,
            $juryEvaluation
        );
    }

    public function filterForCandidate(UserInterface $user, ?string $type, ?string $nom): array
    {
        if (!$user instanceof User) {
            return [];
        }

        return $this->candidatureRepository->findByUserWithFilters($user, $type, $nom);
    }

    public function getAvailableFilters(string $role, ?string $type, CandidatureRepository $candidatureRepository): array
    {
        $filters = [
            'recrutements' => [],
            'formations' => [],
            'vaes' => []
        ];

        if (in_array($role, ['ROLE_ADMIN', 'ROLE_JURY'])) {
            if (!$type || $type === 'recrutement') {
                $filters['recrutements'] = $candidatureRepository->findAllRecrutements();
            }
            if (!$type || $type === 'formation') {
                $filters['formations'] = $candidatureRepository->findAllFormations();
            }
            if (!$type || $type === 'vae') {
                $filters['vaes'] = $candidatureRepository->findAllVaes();
            }
        }

        return $filters;
    }
}
