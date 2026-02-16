<?php

namespace App\Entity;

use App\Repository\SecteurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SecteurRepository::class)]
class Secteur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(nullable: true)]
    private ?int $dureeFormation = null;

    /**
     * @var Collection<int, Metier>
     */
    #[ORM\OneToMany(targetEntity: Metier::class, mappedBy: 'secteur')]
    private Collection $metiers;


    public function __construct()
    {
        $this->metiers = new ArrayCollection();
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

    public function getDureeFormation(): ?int
    {
        return $this->dureeFormation;
    }

    public function setDureeFormation(?int $dureeFormation): static
    {
        $this->dureeFormation = $dureeFormation;

        return $this;
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
            $metier->setSecteur($this);
        }

        return $this;
    }

    public function removeMetier(Metier $metier): static
    {
        if ($this->metiers->removeElement($metier)) {
            if ($metier->getSecteur() === $this) {
                $metier->setSecteur(null);
            }
        }

        return $this;
    }

    

   
    public function __toString(): string
    {
        return $this->nom ?? '';
    }
}
