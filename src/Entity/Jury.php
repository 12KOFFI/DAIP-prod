<?php

namespace App\Entity;

use App\Repository\JuryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: JuryRepository::class)]
class Jury
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $prenom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fonction = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $organisation = null;


    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'jury', cascade: ["persist"])]
    #[ORM\JoinColumn(nullable: false, unique: true, onDelete: "CASCADE")]
    private ?User $user = null;

    /**
     * @var Collection<int, Recrutement>
     */
    #[ORM\ManyToMany(targetEntity: Recrutement::class)]
    #[ORM\JoinTable(name: 'jury_recrutement')]
    private Collection $recrutements;

    /**
     * @var Collection<int, Formation>
     */
    #[ORM\ManyToMany(targetEntity: Formation::class)]
    #[ORM\JoinTable(name: 'jury_formation')]
    private Collection $formations;

    /**
     * @var Collection<int, Vae>
     */
    #[ORM\ManyToMany(targetEntity: Vae::class)]
    #[ORM\JoinTable(name: 'jury_vae')]
    private Collection $vaes;

    /**
     * @var Collection<int, JuryDate>
     */
    #[ORM\OneToMany(targetEntity: JuryDate::class, mappedBy: 'jury', orphanRemoval: true)]
    private Collection $juryDates;

    #[ORM\ManyToOne(inversedBy: 'jury')]
    private ?Centre $centre = null;

    public function __construct()
    {
        $this->recrutements = new ArrayCollection();
        $this->formations = new ArrayCollection();
        $this->vaes = new ArrayCollection();
        $this->juryDates = new ArrayCollection();
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
        }

        return $this;
    }

    public function removeRecrutement(Recrutement $recrutement): static
    {
        $this->recrutements->removeElement($recrutement);

        return $this;
    }

    /**
     * @return Collection<int, Formation>
     */
    public function getFormations(): Collection
    {
        return $this->formations;
    }

    public function addFormation(Formation $formation): static
    {
        if (!$this->formations->contains($formation)) {
            $this->formations->add($formation);
        }

        return $this;
    }

    public function removeFormation(Formation $formation): static
    {
        $this->formations->removeElement($formation);

        return $this;
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
        }

        return $this;
    }

    public function removeVae(Vae $vae): static
    {
        $this->vaes->removeElement($vae);

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): string
    {
        return trim(sprintf('%s %s', $this->prenom ?? '', $this->nom ?? ''));
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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getFonction(): ?string
    {
        return $this->fonction;
    }

    public function setFonction(?string $fonction): static
    {
        $this->fonction = $fonction;

        return $this;
    }

    public function getOrganisation(): ?string
    {
        return $this->organisation;
    }

    public function setOrganisation(?string $organisation): static
    {
        $this->organisation = $organisation;

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

    public function getNomComplet(): string
    {
        return trim(sprintf('%s %s', $this->prenom, $this->nom));
    }

    public function getCentre(): ?Centre
    {
        return $this->centre;
    }

    public function setCentre(?Centre $centre): static
    {
        $this->centre = $centre;

        return $this;
    }

    /**
 * @return Collection<int, JuryDate>
 */
public function getJuryDates(): Collection
{
    return $this->juryDates;
}

public function addJuryDate(JuryDate $juryDate): static
{
    if (!$this->juryDates->contains($juryDate)) {
        $this->juryDates->add($juryDate);
        $juryDate->setJury($this);
    }

    return $this;
}

public function removeJuryDate(JuryDate $juryDate): static
{
    if ($this->juryDates->removeElement($juryDate)) {
        // set the owning side to null (unless already changed)
        if ($juryDate->getJury() === $this) {
            $juryDate->setJury(null);
        }
    }

    return $this;
}
}
