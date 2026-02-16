<?php

namespace App\Entity;

use App\Repository\VaeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VaeRepository::class)]
class Vae
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    /**
     * @var Collection<int, Candidature>
     */
    #[ORM\OneToMany(targetEntity: Candidature::class, mappedBy: 'vae', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $candidature;

    #[ORM\ManyToOne(inversedBy: 'vae')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?Projet $projet = null;

    /**
     * @var Collection<int, Centre>
     */
    #[ORM\ManyToMany(targetEntity: Centre::class, inversedBy: 'vaes')]
    private Collection $centres;

    #[ORM\Column(length: 255)]
    private ?string $numVae = null;

    /**
     * @var Collection<int, ProcessusEtape>
     */
   

    public function __construct()
    {
        $this->candidature = new ArrayCollection();
        $this->centres = new ArrayCollection();
        $this->generateNumVae();
       
    }

    /**
     * Génère automatiquement un numéro de VAE au format VAE-XXX
     */
    public function generateNumVae(): void
    {
        if (null === $this->numVae) {
            $this->numVae = 'VAE-' . strtoupper(substr(uniqid(), -6));
        }
    }

    /**
     * @return Collection<int, Centre>
     */
    public function getCentres(): Collection
    {
        return $this->centres;
    }

    public function addCentre(Centre $centre): static
    {
        if (!$this->centres->contains($centre)) {
            $this->centres->add($centre);
            $centre->addVae($this);
        }

        return $this;
    }

    public function removeCentre(Centre $centre): static
    {
        if ($this->centres->removeElement($centre)) {
            $centre->removeVae($this);
        }

        return $this;
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
            $candidature->setVae($this);
        }

        return $this;
    }

    public function removeCandidature(Candidature $candidature): static
    {
        if ($this->candidature->removeElement($candidature)) {
            // set the owning side to null (unless already changed)
            if ($candidature->getVae() === $this) {
                $candidature->setVae(null);
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

    public function getNumVae(): ?string
    {
        return $this->numVae;
    }

    public function setNumVae(string $numVae): static
    {
        $this->numVae = $numVae;

        return $this;
    }

  

   
}
