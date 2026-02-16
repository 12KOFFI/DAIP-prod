<?php

namespace App\Entity;

use App\Repository\EvaluationCandidatureRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: EvaluationCandidatureRepository::class)]
#[UniqueEntity(
    fields: ['candidature', 'typeEvaluation'],
    message: 'Une évaluation avec ce type existe déjà pour cette candidature.'
)]
class EvaluationCandidature
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $libelle = null;

    #[ORM\Column]
    private ?\DateTime $dateEvaluation = null;

    #[ORM\ManyToOne(inversedBy: 'evaluation')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?Candidature $candidature = null;

    #[ORM\ManyToOne(inversedBy: 'evaluations')]
    private ?GrilleEvaluation $grilleEvaluation = null;

    #[ORM\ManyToOne(inversedBy: 'Evaluation')]
    private ?User $user = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $commentaire = null;


    #[ORM\Column(nullable: true)]
    private ?int $note_totale = null;

    /**
     * @var Collection<int, Notation>
     */
    #[ORM\OneToMany(targetEntity: Notation::class, mappedBy: 'evaluation')]
    private Collection $notations;

    #[ORM\ManyToOne(inversedBy: 'evaluationCandidatures')]
    #[ORM\JoinColumn(nullable: false)]
    private ?TypeEvaluation $typeEvaluation = null;

    #[ORM\Column(length: 255)]
    private ?string $statut = null;

    public function __construct()
    {
        $this->notations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }




    public function getDateEvaluation(): ?\DateTime
    {
        return $this->dateEvaluation;
    }

    public function setDateEvaluation(\DateTime $dateEvaluation): static
    {
        $this->dateEvaluation = $dateEvaluation;

        return $this;
    }

    public function getCandidature(): ?Candidature
    {
        return $this->candidature;
    }

    public function setCandidature(?Candidature $candidature): static
    {
        $this->candidature = $candidature;

        return $this;
    }

    public function getGrilleEvaluation(): ?GrilleEvaluation
    {
        return $this->grilleEvaluation;
    }

    public function setGrilleEvaluation(?GrilleEvaluation $grilleEvaluation): static
    {
        $this->grilleEvaluation = $grilleEvaluation;

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





    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): static
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    public function getNoteTotale(): ?int
    {
        return $this->note_totale;
    }

    public function setNoteTotale(?int $note_totale): static
    {
        $this->note_totale = $note_totale;

        return $this;
    }

    /**
     * @return Collection<int, Notation>
     */
    public function getNotations(): Collection
    {
        return $this->notations;
    }

    public function addNotation(Notation $notation): static
    {
        if (!$this->notations->contains($notation)) {
            $this->notations->add($notation);
            $notation->setEvaluation($this);
        }

        return $this;
    }

    public function removeNotation(Notation $notation): static
    {
        if ($this->notations->removeElement($notation)) {
            // set the owning side to null (unless already changed)
            if ($notation->getEvaluation() === $this) {
                $notation->setEvaluation(null);
            }
        }

        return $this;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle ?? $this->typeEvaluation?->getLibelle();
    }

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;

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

    public function getTypeEvaluation(): ?TypeEvaluation
    {
        return $this->typeEvaluation;
    }

    public function setTypeEvaluation(?TypeEvaluation $typeEvaluation): static
    {
        $this->typeEvaluation = $typeEvaluation;

        return $this;
    }
}