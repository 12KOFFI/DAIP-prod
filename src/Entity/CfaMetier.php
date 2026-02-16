<?php

namespace App\Entity;

use App\Repository\CfaMetierRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: CfaMetierRepository::class)]
#[ORM\Table(name: 'cfa_metier')]
#[ORM\UniqueConstraint(name: 'cfa_metier_unique', columns: ['cfa_etablissement_id', 'metier_id'])]
#[UniqueEntity(
    fields: ['cfaEtablissement', 'metier'],
    message: 'Ce métier est déjà associé à cet établissement.'
)]
class CfaMetier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'cfaMetiers')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?CfaEtablissement $cfaEtablissement = null;

    #[ORM\ManyToOne(inversedBy: 'cfaMetiers')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Metier $metier = null;

    #[ORM\Column]
    private ?int $effectif = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCfaEtablissement(): ?CfaEtablissement
    {
        return $this->cfaEtablissement;
    }

    public function setCfaEtablissement(?CfaEtablissement $cfaEtablissement): static
    {
        $this->cfaEtablissement = $cfaEtablissement;

        return $this;
    }

    public function getMetier(): ?Metier
    {
        return $this->metier;
    }

    public function setMetier(?Metier $metier): static
    {
        $this->metier = $metier;

        return $this;
    }

    public function getEffectif(): ?int
    {
        return $this->effectif;
    }

    public function setEffectif(int $effectif): static
    {
        $this->effectif = $effectif;

        return $this;
    }
}
