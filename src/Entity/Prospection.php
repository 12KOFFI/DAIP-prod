<?php

namespace App\Entity;

use App\Repository\ProspectionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProspectionRepository::class)]
class Prospection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'prospections')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?StructureAccueil $structureAcceuil = null;

    #[ORM\ManyToOne(inversedBy: 'prospections')]
    private ?CfaEtablissement $cfaEtablissement = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $date = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $commentaire = null;

    #[ORM\ManyToOne]
    private ?User $user = null;

    /**
     * @var Collection<int, ProspectionMetier>
     */
    #[ORM\OneToMany(targetEntity: ProspectionMetier::class, mappedBy: 'prospection')]
    private Collection $prospectionMetiers;

    public function __construct()
    {
        $this->prospectionMetiers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStructureAcceuil(): ?StructureAccueil
    {
        return $this->structureAcceuil;
    }

    public function setStructureAcceuil(?StructureAccueil $structureAcceuil): static
    {
        $this->structureAcceuil = $structureAcceuil;

        return $this;
    }

    public function getCfaEtablissement(): ?CfaEtablissement
    {
        return $this->cfaEtablissement;
    }

    public function setCfaEtablissement(?CfaEtablissement $cfaEtablissement): static
    {
        $this->cfaEtablissement = $cfaEtablissement;

        return $this;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): static
    {
        $this->commentaire = $commentaire;

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

    /**
     * @return Collection<int, ProspectionMetier>
     */
    public function getProspectionMetiers(): Collection
    {
        return $this->prospectionMetiers;
    }

    public function addProspectionMetier(ProspectionMetier $prospectionMetier): static
    {
        if (!$this->prospectionMetiers->contains($prospectionMetier)) {
            $this->prospectionMetiers->add($prospectionMetier);
            $prospectionMetier->setProspection($this);
        }

        return $this;
    }

    public function removeProspectionMetier(ProspectionMetier $prospectionMetier): static
    {
        if ($this->prospectionMetiers->removeElement($prospectionMetier)) {
            // set the owning side to null (unless already changed)
            if ($prospectionMetier->getProspection() === $this) {
                $prospectionMetier->setProspection(null);
            }
        }

        return $this;
    }
}
