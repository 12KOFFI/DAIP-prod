<?php

namespace App\Entity;

use App\Repository\StructureAccueilRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StructureAccueilRepository::class)]
class StructureAccueil
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom_structure = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(length: 255)]
    private ?string $localite = null;

    #[ORM\Column(length: 20)]
    private ?string $telephone_structure = null;

    #[ORM\Column(length: 255)]
    private ?string $secteur_activite = null;

    #[ORM\Column(length: 255)]
    private ?string $nom_responsable = null;

    #[ORM\Column(length: 20)]
    private ?string $telephone_responsable = null;

    /**
     * @var Collection<int, Prospection>
     */
    #[ORM\OneToMany(targetEntity: Prospection::class, mappedBy: 'structureAcceuil')]
    private Collection $prospections;

    public function __construct()
    {
        $this->prospections = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomStructure(): ?string
    {
        return $this->nom_structure;
    }

    public function setNomStructure(string $nom_structure): static
    {
        $this->nom_structure = $nom_structure;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getLocalite(): ?string
    {
        return $this->localite;
    }

    public function setLocalite(string $localite): static
    {
        $this->localite = $localite;

        return $this;
    }

    public function getTelephoneStructure(): ?string
    {
        return $this->telephone_structure;
    }

    public function setTelephoneStructure(string $telephone_structure): static
    {
        $this->telephone_structure = $telephone_structure;

        return $this;
    }

    public function getSecteurActivite(): ?string
    {
        return $this->secteur_activite;
    }

    public function setSecteurActivite(string $secteur_activite): static
    {
        $this->secteur_activite = $secteur_activite;

        return $this;
    }

    public function getNomResponsable(): ?string
    {
        return $this->nom_responsable;
    }

    public function setNomResponsable(string $nom_responsable): static
    {
        $this->nom_responsable = $nom_responsable;

        return $this;
    }

    public function getTelephoneResponsable(): ?string
    {
        return $this->telephone_responsable;
    }

    public function setTelephoneResponsable(string $telephone_responsable): static
    {
        $this->telephone_responsable = $telephone_responsable;

        return $this;
    }

    /**
     * @return Collection<int, Prospection>
     */
    public function getProspections(): Collection
    {
        return $this->prospections;
    }

    public function addProspection(Prospection $prospection): static
    {
        if (!$this->prospections->contains($prospection)) {
            $this->prospections->add($prospection);
            $prospection->setStructureAcceuil($this);
        }

        return $this;
    }

    public function removeProspection(Prospection $prospection): static
    {
        if ($this->prospections->removeElement($prospection)) {
            // set the owning side to null (unless already changed)
            if ($prospection->getStructureAcceuil() === $this) {
                $prospection->setStructureAcceuil(null);
            }
        }

        return $this;
    }
}
