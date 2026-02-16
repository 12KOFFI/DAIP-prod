<?php

namespace App\Entity;

use App\Repository\ProspectionMetierRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProspectionMetierRepository::class)]
class ProspectionMetier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $nombre_postes = null;

    #[ORM\ManyToOne]
    private ?Metier $metier = null;

    #[ORM\ManyToOne(inversedBy: 'prospectionMetiers')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?Prospection $prospection = null;

    #[ORM\ManyToOne]
    private ?Filiere $filiere = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombrePostes(): ?int
    {
        return $this->nombre_postes;
    }

    public function setNombrePostes(int $nombre_postes): static
    {
        $this->nombre_postes = $nombre_postes;

        return $this;
    }

    public function getMetier(): ?Metier
    {
        return $this->metier;
    }

    public function setMetier(?Metier $metier): static
    {
        $this->metier = $metier;

        return $this;
    }

    public function getProspection(): ?Prospection
    {
        return $this->prospection;
    }

    public function setProspection(?Prospection $prospection): static
    {
        $this->prospection = $prospection;

        return $this;
    }

    public function getFiliere(): ?Filiere
    {
        return $this->filiere;
    }

    public function setFiliere(?Filiere $filiere): static
    {
        $this->filiere = $filiere;

        return $this;
    }
}
