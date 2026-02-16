<?php

namespace App\Entity;

use App\Repository\CritereRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CritereRepository::class)]
class Critere
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\ManyToOne(inversedBy: 'id_critere')]
    private ?GrilleEvaluation $grilleEvaluation = null;

    /**
     * @var Collection<int, Notation>
     */
    #[ORM\OneToMany(targetEntity: Notation::class, mappedBy: 'critere')]
    private Collection $notation;

    #[ORM\Column(nullable: true)]
    private ?int $bareme = null;

    #[ORM\ManyToOne(inversedBy: 'criteres')]
    private ?Recrutement $recrutement = null;

    #[ORM\ManyToOne(inversedBy: 'criteres')]
    private ?User $user = null;

   

    public function __construct()
    {
        $this->notation = new ArrayCollection();
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

    public function getGrilleEvaluation(): ?GrilleEvaluation
    {
        return $this->grilleEvaluation;
    }

    public function setGrilleEvaluation(?GrilleEvaluation $grilleEvaluation): static
    {
        $this->grilleEvaluation = $grilleEvaluation;

        return $this;
    }

    /**
     * @return Collection<int, Notation>
     */
    public function getNotation(): Collection
    {
        return $this->notation;
    }

    public function addNotation(Notation $notation): static
    {
        if (!$this->notation->contains($notation)) {
            $this->notation->add($notation);
            $notation->setCritere($this);
        }

        return $this;
    }

    public function removeNotation(Notation $notation): static
    {
        if ($this->notation->removeElement($notation)) {
            // set the owning side to null (unless already changed)
            if ($notation->getCritere() === $this) {
                $notation->setCritere(null);
            }
        }

        return $this;
    }

    public function getBareme(): ?int
    {
        return $this->bareme;
    }

    public function setBareme(?int $bareme): static
    {
        $this->bareme = $bareme;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    
}
