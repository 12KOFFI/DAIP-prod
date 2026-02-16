<?php

namespace App\Entity;

use App\Repository\GrilleEvaluationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GrilleEvaluationRepository::class)]
class GrilleEvaluation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;



    /**
     * @var Collection<int, EvaluationCandidature>
     */
    #[ORM\OneToMany(mappedBy: 'grilleEvaluation', targetEntity: EvaluationCandidature::class)]
    private Collection $evaluations;

    /**
     * @var Collection<int, Critere>
     */
    #[ORM\OneToMany(targetEntity: Critere::class, mappedBy: 'grilleEvaluation')]
    private Collection $id_critere;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;


    public function __construct()
    {
        $this->evaluations = new ArrayCollection();
        $this->id_critere = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }




    /**
     * @return Collection<int, EvaluationCandidature>
     */
    public function getEvaluations(): Collection
    {
        return $this->evaluations;
    }

    public function addEvaluation(EvaluationCandidature $evaluation): static
    {
        if (!$this->evaluations->contains($evaluation)) {
            $this->evaluations->add($evaluation);
            $evaluation->setGrilleEvaluation($this);
        }

        return $this;
    }

    public function removeEvaluation(EvaluationCandidature $evaluation): static
    {
        if ($this->evaluations->removeElement($evaluation)) {
            if ($evaluation->getGrilleEvaluation() === $this) {
                $evaluation->setGrilleEvaluation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Critere>
     */
    public function getIdCritere(): Collection
    {
        return $this->id_critere;
    }

    public function addIdCritere(Critere $idCritere): static
    {
        if (!$this->id_critere->contains($idCritere)) {
            $this->id_critere->add($idCritere);
            $idCritere->setGrilleEvaluation($this);
        }

        return $this;
    }

    public function removeIdCritere(Critere $idCritere): static
    {
        if ($this->id_critere->removeElement($idCritere)) {
            // set the owning side to null (unless already changed)
            if ($idCritere->getGrilleEvaluation() === $this) {
                $idCritere->setGrilleEvaluation(null);
            }
        }

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }
}