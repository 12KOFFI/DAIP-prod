<?php

namespace App\Entity;

use App\Repository\ProjetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjetRepository::class)]
class Projet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 50)]
    private ?string $status = null;

    #[ORM\Column]
    private ?\DateTime $dateDebut = null;

    #[ORM\Column]
    private ?\DateTime $dateFin = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logo = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

   

    #[ORM\Column(length: 20, unique: true)]
    private ?string $numProjet = null;

    /**
     * @var Collection<int, Partenaire>
     */
    #[ORM\ManyToMany(targetEntity: Partenaire::class, inversedBy: 'projets')]
    private Collection $id_partenaire;

    /**
     * @var Collection<int, Recrutement>
     */
    #[ORM\OneToMany(targetEntity: Recrutement::class, mappedBy: 'projet', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $recrutement;

    /**
     * @var Collection<int, Formation>
     */
    #[ORM\OneToMany(targetEntity: Formation::class, mappedBy: 'projet', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $formation;

    /**
     * @var Collection<int, Vae>
     */
    #[ORM\OneToMany(targetEntity: Vae::class, mappedBy: 'projet', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $vae;

    #[ORM\ManyToOne(inversedBy: 'Projet')]
    private ?User $user = null;

    public function __construct()
    {
        $this->id_partenaire = new ArrayCollection();
        $this->recrutement = new ArrayCollection();
        $this->generateNumProjet();
        $this->formation = new ArrayCollection();
        $this->vae = new ArrayCollection();
    }

    /**
     * Génère un numéro de projet unique au format PROJ-YYYYMM-XXXX
     */
    public function generateNumProjet(): void
    {
        $this->numProjet = 'PROJ-' . date('Ym') . '-' . strtoupper(substr(uniqid(), -4));
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;
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

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): static
    {
        $this->logo = $logo;
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

    
    /**
     * @return Collection<int, Partenaire>
     */
    public function getIdPartenaire(): Collection
    {
        return $this->id_partenaire;
    }

    public function addIdPartenaire(Partenaire $idPartenaire): static
    {
        if (!$this->id_partenaire->contains($idPartenaire)) {
            $this->id_partenaire->add($idPartenaire);
        }

        return $this;
    }

    public function removeIdPartenaire(Partenaire $idPartenaire): static
    {
        $this->id_partenaire->removeElement($idPartenaire);

        return $this;
    }

    /**
     * @return Collection<int, Recrutement>
     */
    public function getRecrutement(): Collection
    {
        return $this->recrutement;
    }

    public function addRecrutement(Recrutement $recrutement): static
    {
        if (!$this->recrutement->contains($recrutement)) {
            $this->recrutement->add($recrutement);
            $recrutement->setProjet($this);
        }

        return $this;
    }

    public function removeRecrutement(Recrutement $recrutement): static
    {
        if ($this->recrutement->removeElement($recrutement)) {
            // set the owning side to null (unless already changed)
            if ($recrutement->getProjet() === $this) {
                $recrutement->setProjet(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Formation>
     */
    public function getFormation(): Collection
    {
        return $this->formation;
    }

    public function addFormation(Formation $formation): static
    {
        if (!$this->formation->contains($formation)) {
            $this->formation->add($formation);
            $formation->setProjet($this);
        }

        return $this;
    }

    public function removeFormation(Formation $formation): static
    {
        if ($this->formation->removeElement($formation)) {
            // set the owning side to null (unless already changed)
            if ($formation->getProjet() === $this) {
                $formation->setProjet(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Vae>
     */
    public function getVae(): Collection
    {
        return $this->vae;
    }

    public function addVae(Vae $vae): static
    {
        if (!$this->vae->contains($vae)) {
            $this->vae->add($vae);
            $vae->setProjet($this);
        }

        return $this;
    }

    public function removeVae(Vae $vae): static
    {
        if ($this->vae->removeElement($vae)) {
            // set the owning side to null (unless already changed)
            if ($vae->getProjet() === $this) {
                $vae->setProjet(null);
            }
        }

        return $this;
    }

    public function getNumProjet(): ?string
    {
        return $this->numProjet;
    }

    public function setNumProjet(string $numProjet): static
    {
        $this->numProjet = $numProjet;
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
