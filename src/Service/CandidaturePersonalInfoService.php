<?php

namespace App\Service;

use App\Entity\Candidature;

/**
 * Service responsable de la copie et synchronisation des informations personnelles entre candidatures.
 */
class CandidaturePersonalInfoService
{
    /**
     * Copie les informations personnelles d'une candidature source vers une candidature cible.
     * Utilisé lors de la création d'une nouvelle candidature pour pré-remplir avec les données précédentes.
     *
     * @param Candidature $target Candidature destination (à pré-remplir)
     * @param Candidature $source Candidature source (à copier depuis)
     */
    public function copyPersonalInformation(Candidature $target, Candidature $source): void
    {
        $target
            ->setNom($source->getNom() ?? '')
            ->setPrenom($source->getPrenom() ?? '')
            ->setSexe($source->getSexe())
            ->setNomJeuneFille($source->getNomJeuneFille())
            ->setDateNaissance($source->getDateNaissance())
            ->setLieuNaissance($source->getLieuNaissance())
            ->setSituationMatrimoniale($source->getSituationMatrimoniale())
            ->setAdresse($source->getAdresse() ?? '')
            ->setNationalite($source->getNationalite())
            ->setDisponibilite($source->getDisponibilite())
            ->setNumCmu($source->getNumCmu())
            ->setNumPiece($source->getNumPiece())
            ->setNomPrenomUrgence($source->getNomPrenomUrgence())
            ->setContactUrgence($source->getContactUrgence())
            ->setContact2($source->getContact2());

        if (null !== $source->getContacts()) {
            $target->setContacts($source->getContacts());
        }
    }
}
