<?php

namespace App\Entity;

use App\Repository\TypeEvaluationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TypeEvaluationRepository::class)]
class TypeEvaluation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\OneToMany(mappedBy: 'typeEvaluation', targetEntity: EvaluationCandidature::class)]
    private Collection $evaluationCandidatures;

    public function __construct()
    {
        $this->evaluationCandidatures = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function __toString(): string
    {
        return $this->libelle ?? '';
    }

    /**
     * @return Collection<int, EvaluationCandidature>
     */
    public function getEvaluationCandidatures(): Collection
    {
        return $this->evaluationCandidatures;
    }

    public function addEvaluationCandidature(EvaluationCandidature $evaluationCandidature): static
    {
        if (!$this->evaluationCandidatures->contains($evaluationCandidature)) {
            $this->evaluationCandidatures->add($evaluationCandidature);
            $evaluationCandidature->setTypeEvaluation($this);
        }

        return $this;
    }

    public function removeEvaluationCandidature(EvaluationCandidature $evaluationCandidature): static
    {
        if ($this->evaluationCandidatures->removeElement($evaluationCandidature)) {
            // set the owning side to null (unless already changed)
            if ($evaluationCandidature->getTypeEvaluation() === $this) {
                $evaluationCandidature->setTypeEvaluation(null);
            }
        }

        return $this;
    }
}
