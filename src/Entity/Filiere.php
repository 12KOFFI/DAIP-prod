<?php

namespace App\Entity;

use App\Repository\FiliereRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Formation;

#[ORM\Entity(repositoryClass: FiliereRepository::class)]
class Filiere
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    /**
     * @var Collection<int, CfaEtablissement>
     */
    #[ORM\ManyToMany(targetEntity: CfaEtablissement::class, mappedBy: 'filieres')]
    private Collection $cfaEtablissements;

    /**
     * @var Collection<int, Diplome>
     */
    #[ORM\OneToMany(targetEntity: Diplome::class, mappedBy: 'filiere')]
    private Collection $diplome;

    /**
     * @var Collection<int, Metier>
     */
    #[ORM\ManyToMany(targetEntity: Metier::class, inversedBy: 'filieres')]
    private Collection $metier;

    #[ORM\ManyToOne(inversedBy: 'Filiere')]
    private ?Candidature $candidature = null;

    
    
    public function __construct()
    {
        $this->cfaEtablissements = new ArrayCollection();
        $this->diplome = new ArrayCollection();
        $this->metier = new ArrayCollection();
    }

    
    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @return Collection<int, CfaEtablissement>
     */
    public function getCfaEtablissements(): Collection
    {
        return $this->cfaEtablissements;
    }

    public function addCfaEtablissement(CfaEtablissement $cfaEtablissement): static
    {
        if (!$this->cfaEtablissements->contains($cfaEtablissement)) {
            $this->cfaEtablissements->add($cfaEtablissement);
            $cfaEtablissement->addFiliere($this);
        }

        return $this;
    }

    public function removeCfaEtablissement(CfaEtablissement $cfaEtablissement): static
    {
        if ($this->cfaEtablissements->removeElement($cfaEtablissement)) {
            $cfaEtablissement->removeFiliere($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Diplome>
     */
    public function getDiplome(): Collection
    {
        return $this->diplome;
    }

    public function addDiplome(Diplome $diplome): static
    {
        if (!$this->diplome->contains($diplome)) {
            $this->diplome->add($diplome);
            $diplome->setFiliere($this);
        }

        return $this;
    }

    public function removeDiplome(Diplome $diplome): static
    {
        if ($this->diplome->removeElement($diplome)) {
            // set the owning side to null (unless already changed)
            if ($diplome->getFiliere() === $this) {
                $diplome->setFiliere(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Metier>
     */
    public function getMetier(): Collection
    {
        return $this->metier;
    }

    public function addMetier(Metier $metier): static
    {
        if (!$this->metier->contains($metier)) {
            $this->metier->add($metier);
        }

        return $this;
    }

    public function removeMetier(Metier $metier): static
    {
        $this->metier->removeElement($metier);

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


}
