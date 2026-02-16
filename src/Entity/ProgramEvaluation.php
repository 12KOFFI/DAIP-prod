<?php

namespace App\Entity;

use App\Repository\ProgramEvaluationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProgramEvaluationRepository::class)]
class ProgramEvaluation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'programEvaluations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?TypeEvaluation $typeEvaluation = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Centre $centre = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Metier $metier = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateLancement = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    private ?string $statut = null;

    #[ORM\ManyToOne(inversedBy: 'programEvaluations')]
    private ?Recrutement $recrutement = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypeEvaluation(): ?TypeEvaluation
    {
        return $this->typeEvaluation;
    }

    public function setTypeEvaluation(?TypeEvaluation $typeEvaluation): static
    {
        $this->typeEvaluation = $typeEvaluation;

        return $this;
    }

    public function getCentre(): ?Centre
    {
        return $this->centre;
    }

    public function setCentre(?Centre $centre): static
    {
        $this->centre = $centre;

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

    public function getDateLancement(): ?\DateTimeInterface
    {
        return $this->dateLancement;
    }

    public function setDateLancement(\DateTimeInterface $dateLancement): static
    {
        $this->dateLancement = $dateLancement;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getRecrutement(): ?Recrutement
    {
        return $this->recrutement;
    }

    public function setRecrutement(?Recrutement $recrutement): static
    {
        $this->recrutement = $recrutement;

        return $this;
    }
}
