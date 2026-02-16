<?php

namespace App\Service;

use App\Entity\Candidature;
use App\Entity\User;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CandidatureAuthorizationService
{
    private AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function checkViewAccess(?Candidature $candidature = null, ?User $user = null): void
    {
        if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            return;
        }

        if ($this->authorizationChecker->isGranted('ROLE_JURY')) {
            if ($candidature && !$this->isJuryAssignedToCandidature($candidature, $user)) {
                throw new AccessDeniedException('Vous n\'avez pas accès à cette candidature.');
            }
            return;
        }

        if ($this->authorizationChecker->isGranted('ROLE_CANDIDAT')) {
            if ($candidature && $candidature->getUser() !== $user) {
                throw new AccessDeniedException('Vous ne pouvez voir que vos propres candidatures.');
            }
            return;
        }

        throw new AccessDeniedException('Accès refusé.');
    }

    public function checkEditAccess(Candidature $candidature, User $user): void
    {
        if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            if (!$this->isEditableByAdmin($candidature)) {
                throw new AccessDeniedException('Cette candidature ne peut plus être modifiée.');
            }
            return;
        }

        if ($this->authorizationChecker->isGranted('ROLE_CANDIDAT')) {
            if ($candidature->getUser() !== $user) {
                throw new AccessDeniedException('Vous ne pouvez modifier que vos propres candidatures.');
            }

            if (!$this->isEditableByCandidate($candidature)) {
                throw new AccessDeniedException('Cette candidature ne peut plus être modifiée.');
            }
            return;
        }

        // Les jurys ne peuvent pas modifier les candidatures
        if ($this->authorizationChecker->isGranted('ROLE_JURY')) {
            throw new AccessDeniedException('Les jurys ne peuvent pas modifier les candidatures.');
        }

        throw new AccessDeniedException('Accès refusé.');
    }

    // Dans CandidatureAuthorizationService
    public function checkDeleteAccess(Candidature $candidature, User $user): void
    {
        if (!$this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException('Seuls les administrateurs peuvent supprimer.');
        }

        if ($candidature->getStatut() === 'acceptee') {
            throw new AccessDeniedException('Impossible de supprimer une candidature acceptée.');
        }
    }

    private function isJuryAssignedToCandidature(Candidature $candidature, User $user): bool
    {
        if (!$user->getJury()) {
            return false;
        }

        $jury = $user->getJury();

        // Vérifier si le jury est assigné au recrutement, formation ou VAE de la candidature
        if ($candidature->getRecrutement() && $jury->getRecrutements()->contains($candidature->getRecrutement())) {
            return true;
        }

        if ($candidature->getFormation() && $jury->getFormations()->contains($candidature->getFormation())) {
            return true;
        }

        if ($candidature->getVae() && $jury->getVaes()->contains($candidature->getVae())) {
            return true;
        }

        return false;
    }

    private function isEditableByAdmin(Candidature $candidature): bool
    {
        // Les admins peuvent modifier tant que ce n'est pas accepté/refusé
        return !in_array($candidature->getStatut(), ['acceptee', 'refusee']);
    }

    private function isEditableByCandidate(Candidature $candidature): bool
    {
        // Les candidats ne peuvent modifier que les candidatures en attente ou incomplètes
        return in_array($candidature->getStatut(), ['en_attente', 'incomplete']);
    }

    private function isDeletable(Candidature $candidature): bool
    {
        // Ne pas supprimer les candidatures acceptées
        return $candidature->getStatut() !== 'acceptee';
    }


    public function canEdit(Candidature $candidature, User $user): bool
    {
        try {
            $this->checkEditAccess($candidature, $user);
            return true;
        } catch (AccessDeniedException $e) {
            return false;
        }
    }
}
