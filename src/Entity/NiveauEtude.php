<?php

namespace App\Entity;

use App\Repository\NiveauEtudeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NiveauEtudeRepository::class)]
class NiveauEtude
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'NiveauEtude')]
    private Collection $users;

    /**
     * @var Collection<int, Candidature>
     */
    #[ORM\OneToMany(targetEntity: Candidature::class, mappedBy: 'niveauEtude')]
    private Collection $candidatures;

    /**
     * @var Collection<int, Metier>
     */
    #[ORM\OneToMany(targetEntity: Metier::class, mappedBy: 'niveauEtude')]
    private Collection $metier;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->candidatures = new ArrayCollection();
        $this->metier = new ArrayCollection();
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
            $user->setNiveauEtude($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getNiveauEtude() === $this) {
                $user->setNiveauEtude(null);
            }
        }

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
            $candidature->setNiveauEtude($this);
        }

        return $this;
    }

    public function removeCandidature(Candidature $candidature): static
    {
        if ($this->candidatures->removeElement($candidature)) {
            // set the owning side to null (unless already changed)
            if ($candidature->getNiveauEtude() === $this) {
                $candidature->setNiveauEtude(null);
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
            $metier->setNiveauEtude($this);
        }

        return $this;
    }

    public function removeMetier(Metier $metier): static
    {
        if ($this->metier->removeElement($metier)) {
            // set the owning side to null (unless already changed)
            if ($metier->getNiveauEtude() === $this) {
                $metier->setNiveauEtude(null);
            }
        }

        return $this;
    }
}
