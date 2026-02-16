<?php

namespace App\Entity;

use App\Repository\FormationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FormationRepository::class)]
class Formation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column]
    private ?\DateTime $dateDebut = null;

    #[ORM\Column]
    private ?\DateTime $dateFin = null;

    /**
     * @var Collection<int, Candidature>
     */
    #[ORM\OneToMany(targetEntity: Candidature::class, mappedBy: 'formation', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $candidature;

    #[ORM\ManyToOne(inversedBy: 'formation')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?Projet $projet = null;

    #[ORM\ManyToOne(inversedBy: 'formations')]
    private ?CfaEtablissement $cfa_etablissement = null;

    #[ORM\Column(length: 255)]
    private ?string $numformation = null;

    #[ORM\ManyToOne(inversedBy: 'Formation')]
    private ?User $user = null;

    #[ORM\Column(name: 'age_minimum', nullable: true)]
    private ?int $ageMinimum = null;

    #[ORM\Column(name: 'age_maximum', nullable: true)]
    private ?int $ageMaximum = null;

    #[ORM\Column(name: 'public_cible', length: 255, nullable: true)]
    private ?string $publicCible = null;

    #[ORM\Column(length: 50)]
    private ?string $statut = 'Brouillon';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $banniere = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logo = null;


    public function __construct()
    {
        $this->candidature = new ArrayCollection();
        
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getDateDebut(): ?\DateTime
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTime $dateDebut): static
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    public function getDateFin(): ?\DateTime
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTime $dateFin): static
    {
        $this->dateFin = $dateFin;
        return $this;
    }

    /**
     * @return Collection<int, Candidature>
     */
    public function getCandidature(): Collection
    {
        return $this->candidature;
    }

    public function addCandidature(Candidature $candidature): static
    {
        if (!$this->candidature->contains($candidature)) {
            $this->candidature->add($candidature);
            $candidature->setFormation($this);
        }
        return $this;
    }

    public function removeCandidature(Candidature $candidature): static
    {
        if ($this->candidature->removeElement($candidature)) {
            if ($candidature->getFormation() === $this) {
                $candidature->setFormation(null);
            }
        }
        return $this;
    }

    public function getProjet(): ?Projet
    {
        return $this->projet;
    }

    public function setProjet(?Projet $projet): static
    {
        $this->projet = $projet;
        return $this;
    }

    public function getCfaEtablissement(): ?CfaEtablissement
    {
        return $this->cfa_etablissement;
    }

    public function setCfaEtablissement(?CfaEtablissement $cfa_etablissement): static
    {
        $this->cfa_etablissement = $cfa_etablissement;
        return $this;
    }

    public function getNumformation(): ?string
    {
        return $this->numformation;
    }

    public function setNumformation(string $numformation): static
    {
        $this->numformation = $numformation;
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

    public function getAgeMinimum(): ?int
    {
        return $this->ageMinimum;
    }

    public function setAgeMinimum(?int $ageMinimum): static
    {
        $this->ageMinimum = $ageMinimum;
        return $this;
    }

    public function getAgeMaximum(): ?int
    {
        return $this->ageMaximum;
    }

    public function setAgeMaximum(?int $ageMaximum): static
    {
        $this->ageMaximum = $ageMaximum;
        return $this;
    }

    public function getPublicCible(): ?string
    {
        return $this->publicCible;
    }

    public function setPublicCible(?string $publicCible): static
    {
        $this->publicCible = $publicCible;
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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;
        return $this;
    }

    public function getBanniere(): ?string
    {
        return $this->banniere;
    }

    public function setBanniere(?string $banniere): static
    {
        $this->banniere = $banniere;
        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): static
    {
        $this->logo = $logo;
        return $this;
    }

    /**
     * @return Collection<int, ProcessusEtape>
     */

   
}
