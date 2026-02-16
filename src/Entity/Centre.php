<?php

namespace App\Entity;

use App\Repository\CentreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CentreRepository::class)]
class Centre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(length: 255)]
    private ?string $region = null;

    #[ORM\Column(length: 255)]
    private ?string $departement = null;

    #[ORM\Column(length: 255)]
    private ?string $adresse = null;

    #[ORM\Column]
    private ?int $contacts = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $numCentre = null;

    /**
     * @var Collection<int, Metier>
     */
    #[ORM\ManyToMany(targetEntity: Metier::class, inversedBy: 'centres')]
    private Collection $metiers;

    /**
     * @var Collection<int, Recrutement>
     */
    #[ORM\OneToMany(targetEntity: Recrutement::class, mappedBy: 'centre')]
    private Collection $recrutements;

    /**
     * @var Collection<int, Vae>
     */
    #[ORM\ManyToMany(targetEntity: Vae::class, mappedBy: 'centres')]
    private Collection $vaes;

    /**
     * @var Collection<int, Jury>
     */
    #[ORM\OneToMany(targetEntity: Jury::class, mappedBy: 'centre')]
    private Collection $jury;

    public function __construct()
    {
        $this->generateNumCentre();
        $this->metiers = new ArrayCollection();
        $this->vaes = new ArrayCollection();
        $this->recrutements = new ArrayCollection();
        $this->jury = new ArrayCollection();
    }

    /**
     * @return Collection<int, Vae>
     */
    public function getVaes(): Collection
    {
        return $this->vaes;
    }

    public function addVae(Vae $vae): static
    {
        if (!$this->vaes->contains($vae)) {
            $this->vaes->add($vae);
            $vae->addCentre($this);
        }

        return $this;
    }



    public function removeVae(Vae $vae): static
    {
        if ($this->vaes->removeElement($vae)) {
            $vae->removeCentre($this);
        }

        return $this;
    }

    /**
     * Génère automatiquement un numéro de centre au format CENTRE-XXX
     */
    public function generateNumCentre(): void
    {
        if (null === $this->numCentre) {
            $this->numCentre = 'CENTRE-' . strtoupper(substr(uniqid(), -6));
        }
    }

    /**
     * Cette méthode est appelée avant la persistance et la mise à jour de l'entité
     */
    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function updateTimestamps(): void
    {
        $this->generateNumCentre();
    }

    /**
     * @return Collection<int, Metier>
     */
    public function getMetiers(): Collection
    {
        return $this->metiers;
    }

    public function addMetier(Metier $metier): static
    {
        if (!$this->metiers->contains($metier)) {
            $this->metiers->add($metier);
        }

        return $this;
    }

    public function removeMetier(Metier $metier): static
    {
        $this->metiers->removeElement($metier);
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(string $region): static
    {
        $this->region = $region;

        return $this;
    }

    public function getDepartement(): ?string
    {
        return $this->departement;
    }

    public function setDepartement(string $departement): static
    {
        $this->departement = $departement;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getContacts(): ?int
    {
        return $this->contacts;
    }

    public function setContacts(int $contacts): static
    {
        $this->contacts = $contacts;

        return $this;
    }



    public function getNumCentre(): ?string
    {
        return $this->numCentre;
    }

    public function setNumCentre(string $numCentre): static
    {
        $this->numCentre = $numCentre;

        return $this;
    }

    /**
     * @return Collection<int, jury>
     */
    public function getJury(): Collection
    {
        return $this->jury;
    }

    public function addJury(jury $jury): static
    {
        if (!$this->jury->contains($jury)) {
            $this->jury->add($jury);
            $jury->setCentre($this);
        }

        return $this;
    }

    public function removeJury(jury $jury): static
    {
        if ($this->jury->removeElement($jury)) {
            // set the owning side to null (unless already changed)
            if ($jury->getCentre() === $this) {
                $jury->setCentre(null);
            }
        }

        return $this;
    }
}
