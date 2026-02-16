<?php

namespace App\Entity;

use App\Repository\NotationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NotationRepository::class)]
class Notation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $note = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $commentaire = null;


    #[ORM\ManyToOne(inversedBy: 'notation')]
    private ?Critere $critere = null;



    #[ORM\ManyToOne(inversedBy: 'notations')]
    private ?EvaluationCandidature $evaluation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNote(): ?float
    {
        return $this->note;
    }

    public function setNote(float $note): static
    {
        $this->note = $note;

        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): static
    {
        $this->commentaire = $commentaire;

        return $this;
    }


    public function getCritere(): ?Critere
    {
        return $this->critere;
    }

    public function setCritere(?Critere $critere): static
    {
        $this->critere = $critere;

        return $this;
    }




    public function getEvaluation(): ?EvaluationCandidature
    {
        return $this->evaluation;
    }

    public function setEvaluation(?EvaluationCandidature $evaluation): static
    {
        $this->evaluation = $evaluation;

        return $this;
    }
}