<?php

namespace App\Entity;

use App\Repository\CfaEtablissementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CfaEtablissementRepository::class)]
class CfaEtablissement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nomEtablissement = null;

    #[ORM\Column(length: 255)]
    private ?string $nomChefEtablissement = null;

    #[ORM\OneToMany(mappedBy: 'cfa_etablissement', targetEntity: Formation::class)]
    private Collection $formations;

    #[ORM\ManyToMany(targetEntity: Filiere::class, inversedBy: 'cfaEtablissements')]
    private Collection $filieres;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $numcfaEtablissement = null;

    #[ORM\Column(length: 255)]
    private ?string $region = null;

    /**
     * @var Collection<int, Prospection>
     */
    #[ORM\OneToMany(targetEntity: Prospection::class, mappedBy: 'cfaEtablissement')]
    private Collection $prospections;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'cfaEtablissement')]
    private Collection $users;


    /**
     * @var Collection<int, CfaMetier>
     */
    #[ORM\OneToMany(targetEntity: CfaMetier::class, mappedBy: 'cfaEtablissement', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $cfaMetiers;

    public function __construct()
    {
        $this->formations = new ArrayCollection();
        $this->filieres = new ArrayCollection();
        $this->prospections = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->cfaMetiers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomEtablissement(): ?string
    {
        return $this->nomEtablissement;
    }

    public function setNomEtablissement(string $nomEtablissement): static
    {
        $this->nomEtablissement = $nomEtablissement;

        return $this;
    }

    public function getNomChefEtablissement(): ?string
    {
        return $this->nomChefEtablissement;
    }

    public function setNomChefEtablissement(string $nomChefEtablissement): static
    {
        $this->nomChefEtablissement = $nomChefEtablissement;

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
            $formation->setCfaEtablissement($this);
        }

        return $this;
    }

    public function removeFormation(Formation $formation): static
    {
        if ($this->formations->removeElement($formation)) {
            // set the owning side to null (unless already changed)
            if ($formation->getCfaEtablissement() === $this) {
                $formation->setCfaEtablissement(null);
            }
        }

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
            $filiere->addCfaEtablissement($this);
        }

        return $this;
    }

    public function removeFiliere(Filiere $filiere): static
    {
        if ($this->filieres->removeElement($filiere)) {
            $filiere->removeCfaEtablissement($this);
        }

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getNumcfaEtablissement(): ?string
    {
        return $this->numcfaEtablissement;
    }

    public function setNumcfaEtablissement(string $numcfaEtablissement): static
    {
        $this->numcfaEtablissement = $numcfaEtablissement;

        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(?string $region): static
    {
        $this->region = $region;

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
            $prospection->setCfaEtablissement($this);
        }

        return $this;
    }

    public function removeProspection(Prospection $prospection): static
    {
        if ($this->prospections->removeElement($prospection)) {
            // set the owning side to null (unless already changed)
            if ($prospection->getCfaEtablissement() === $this) {
                $prospection->setCfaEtablissement(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setCfaEtablissement($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getCfaEtablissement() === $this) {
                $user->setCfaEtablissement(null);
            }
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
            $cfaMetier->setCfaEtablissement($this);
        }

        return $this;
    }

    public function removeCfaMetier(CfaMetier $cfaMetier): static
    {
        if ($this->cfaMetiers->removeElement($cfaMetier)) {
            if ($cfaMetier->getCfaEtablissement() === $this) {
                $cfaMetier->setCfaEtablissement(null);
            }
        }

        return $this;
    }
}