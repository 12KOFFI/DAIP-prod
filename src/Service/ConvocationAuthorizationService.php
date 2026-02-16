<?php

namespace App\Service;

use App\Entity\Convocation;
use App\Entity\User;
use App\Repository\ConvocationRepository;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ConvocationAuthorizationService
{
    public function __construct(
        private AuthorizationCheckerInterface $authorizationChecker
    ) {}

    public function checkViewAccess(Convocation $convocation, User $user): void
    {
        // Admin a accès total
        if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            return;
        }

        // Candidat : seulement ses convocations
        if ($this->authorizationChecker->isGranted('ROLE_CANDIDAT')) {
            $candidature = $convocation->getCandidature();
            if ($candidature && $candidature->getUser() === $user) {
                return;
            }
            throw new AccessDeniedException('Vous ne pouvez voir que vos propres convocations.');
        }

        // Jury : seulement ses convocations liées
        if ($this->authorizationChecker->isGranted('ROLE_JURY')) {
            if ($this->isJuryAssignedToConvocation($convocation, $user)) {
                return;
            }
            throw new AccessDeniedException('Vous n\'êtes pas assigné à cette convocation.');
        }

        throw new AccessDeniedException('Accès refusé.');
    }

    public function checkPrintAccess(Convocation $convocation, User $user): void
    {
        // Seuls les candidats et admins peuvent imprimer
        if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            return;
        }

        if ($this->authorizationChecker->isGranted('ROLE_CANDIDAT')) {
            $candidature = $convocation->getCandidature();
            if ($candidature && $candidature->getUser() === $user) {
                return;
            }
            throw new AccessDeniedException('Vous ne pouvez imprimer que vos propres convocations.');
        }

        throw new AccessDeniedException('Vous n\'avez pas le droit d\'imprimer cette convocation.');
    }

    public function checkDeleteAccess(): void
    {
        // Seuls les admins peuvent supprimer
        if (!$this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException('Seuls les administrateurs peuvent supprimer des convocations.');
        }
    }

    public function getConvocationsForUser(User $user, ConvocationRepository $repository): array
    {
        if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            return $repository->findAll();
        }

        if ($this->authorizationChecker->isGranted('ROLE_CANDIDAT')) {
            return $repository->findByUser($user);
        }

        if ($this->authorizationChecker->isGranted('ROLE_JURY')) {
            $jury = $user->getJury();
            if ($jury) {
                return $repository->findByJury($jury);
            }
            return [];
        }

        return [];
    }

    private function isJuryAssignedToConvocation(Convocation $convocation, User $user): bool
    {
        $jury = $user->getJury();
        if (!$jury) {
            return false;
        }

        // Vérifier via la date du jury (structure principale)
        $juryDate = $convocation->getJuryDate();
        if ($juryDate) {
            $dateJury = $juryDate->getJury();
            if ($dateJury && $this->juryMatches($dateJury, $jury)) {
                return true;
            }
        }

        return false;
    }

    private function juryMatches($jury1, $jury2): bool
    {
        if ($jury1 === $jury2) {
            return true;
        }
        if (method_exists($jury1, 'getId') && method_exists($jury2, 'getId')) {
            $id1 = $jury1->getId();
            $id2 = $jury2->getId();
            return $id1 !== null && $id2 !== null && $id1 === $id2;
        }
        return false;
    }
}
