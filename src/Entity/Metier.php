<?php

namespace App\Entity;

use App\Repository\MetierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MetierRepository::class)]
class Metier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    /**
     * @var Collection<int, Diplome>
     */
    #[ORM\ManyToMany(targetEntity: Diplome::class, inversedBy: 'metiers')]
    private Collection $diplomes;

    #[ORM\Column(length: 255)]
    private ?string $numMetier = null;

    /**
     * @var Collection<int, Candidature>
     */
    #[ORM\OneToMany(targetEntity: Candidature::class, mappedBy: 'metier')]
    private Collection $candidatures;

    /**
     * @var Collection<int, Recrutement>
     */
    #[ORM\ManyToMany(targetEntity: Recrutement::class, mappedBy: 'metiers')]
    private Collection $recrutements;

    #[ORM\ManyToOne(inversedBy: 'metier')]
    private ?NiveauEtude $niveauEtude = null;

    /**
     * @var Collection<int, Centre>
     */
    #[ORM\ManyToMany(targetEntity: Centre::class, mappedBy: 'metiers')]
    private Collection $centres;

    /**
     * @var Collection<int, Filiere>
     */
    #[ORM\ManyToMany(targetEntity: Filiere::class, mappedBy: 'metier')]
    private Collection $filieres;

   

    /**
     * @var Collection<int, CfaMetier>
     */
    #[ORM\OneToMany(targetEntity: CfaMetier::class, mappedBy: 'metier', orphanRemoval: true)]
    private Collection $cfaMetiers;

    #[ORM\ManyToOne(inversedBy: 'metiers')]
    private ?Secteur $secteur = null;

    public function __construct()
    {
        $this->diplomes = new ArrayCollection();
        $this->candidatures = new ArrayCollection();
        $this->recrutements = new ArrayCollection();
        $this->centres = new ArrayCollection();
        $this->generateNumMetier();
        $this->filieres = new ArrayCollection();
        $this->cfaMetiers = new ArrayCollection();
    }

    /**
     * Génère automatiquement un numéro de métier au format MET-XXXXXX
     */
    public function getCentres(): Collection
    {
        return $this->centres;
    }

    public function addCentre(Centre $centre): static
    {
        if (!$this->centres->contains($centre)) {
            $this->centres->add($centre);
            $centre->addMetier($this);
        }

        return $this;
    }

    public function removeCentre(Centre $centre): static
    {
        if ($this->centres->removeElement($centre)) {
            $centre->removeMetier($this);
        }

        return $this;
    }

    public function generateNumMetier(): void
    {
        if (null === $this->numMetier) {
            $this->numMetier = 'MET-' . strtoupper(substr(uniqid(), -6));
        }
    }

    /**
     * Cette méthode est appelée avant la persistance et la mise à jour de l'entité
     */
    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function updateTimestamps(): void
    {
        $this->generateNumMetier();
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function __toString()
    {
        return $this->nom;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;
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

    /**
     * @return Collection<int, Diplome>
     */
    public function getDiplomes(): Collection
    {
        return $this->diplomes;
    }

    public function addDiplome(Diplome $diplome): self
    {
        if (!$this->diplomes->contains($diplome)) {
            $this->diplomes->add($diplome);
            $diplome->addMetier($this);
        }

        return $this;
    }

    public function removeDiplome(Diplome $diplome): self
    {
        if ($this->diplomes->removeElement($diplome)) {
            $diplome->removeMetier($this);
        }

        return $this;
    }

    public function getNumMetier(): ?string
    {
        return $this->numMetier;
    }

    public function setNumMetier(string $numMetier): static
    {
        $this->numMetier = $numMetier;

        return $this;
    }

    /**
     * @return Collection<int, Candidature>
     */
    public function getCandidatures(): Collection
    {
        return $this->candidatures;
    }

    public function addCandidature(Candidature $candidature): static
    {
        if (!$this->candidatures->contains($candidature)) {
            $this->candidatures->add($candidature);
            $candidature->setMetier($this);
        }

        return $this;
    }

    public function removeCandidature(Candidature $candidature): static
    {
        if ($this->candidatures->removeElement($candidature)) {
            // set the owning side to null (unless already changed)
            if ($candidature->getMetier() === $this) {
                $candidature->setMetier(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Recrutement>
     */
    public function getRecrutements(): Collection
    {
        return $this->recrutements;
    }

    public function addRecrutement(Recrutement $recrutement): static
    {
        if (!$this->recrutements->contains($recrutement)) {
            $this->recrutements->add($recrutement);
            $recrutement->addMetier($this);
        }

        return $this;
    }

    public function removeRecrutement(Recrutement $recrutement): static
    {
        if ($this->recrutements->removeElement($recrutement)) {
            $recrutement->removeMetier($this);
        }

        return $this;
    }

    public function getNiveauEtude(): ?NiveauEtude
    {
        return $this->niveauEtude;
    }

    public function setNiveauEtude(?NiveauEtude $niveauEtude): static
    {
        $this->niveauEtude = $niveauEtude;

        return $this;
    }

    /**
     * @return Collection<int, Filiere>
     */
    public function getFilieres(): Collection
    {
        return $this->filieres;
    }

    public function addFiliere(Filiere $filiere): static
    {
        if (!$this->filieres->contains($filiere)) {
            $this->filieres->add($filiere);
            $filiere->addMetier($this);
        }

        return $this;
    }

    public function removeFiliere(Filiere $filiere): static
    {
        if ($this->filieres->removeElement($filiere)) {
            $filiere->removeMetier($this);
        }

        return $this;
    }

   

    
    

    /**
     * @return Collection<int, CfaMetier>
     */
    public function getCfaMetiers(): Collection
    {
        return $this->cfaMetiers;
    }

    public function addCfaMetier(CfaMetier $cfaMetier): static
    {
        if (!$this->cfaMetiers->contains($cfaMetier)) {
            $this->cfaMetiers->add($cfaMetier);
            $cfaMetier->setMetier($this);
        }

        return $this;
    }

    public function removeCfaMetier(CfaMetier $cfaMetier): static
    {
        if ($this->cfaMetiers->removeElement($cfaMetier)) {
            if ($cfaMetier->getMetier() === $this) {
                $cfaMetier->setMetier(null);
            }
        }

        return $this;
    }

    public function getSecteur(): ?Secteur
    {
        return $this->secteur;
    }

    public function setSecteur(?Secteur $secteur): static
    {
        $this->secteur = $secteur;

        return $this;
    }
}
