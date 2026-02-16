<?php

namespace App\Entity;

use App\Repository\PieceJointeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PieceJointeRepository::class)]
class PieceJointe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $chemin = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $type = null;

    #[ORM\ManyToOne(inversedBy: 'piece_jointe')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?Candidature $candidature = null;

    #[ORM\Column(length: 255)]
    private ?string $numPieceJointe = null; // Ce champ ne peut pas être null

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numCandidature = null;

    #[ORM\Column(nullable: true)]
    private ?int $tailleFichier = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateModification = null;

    public function __construct()
    {
        $this->dateCreation = new \DateTime();
        $this->numPieceJointe = 'PJ_' . uniqid(); // Génération automatique
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getChemin(): ?string
    {
        return $this->chemin;
    }

    public function setChemin(?string $chemin): static
    {
        $this->chemin = $chemin;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getCandidature(): ?Candidature
    {
        return $this->candidature;
    }

    public function setCandidature(?Candidature $candidature): static
    {
        $this->candidature = $candidature;
        return $this;
    }

    public function getNumPieceJointe(): ?string
    {
        return $this->numPieceJointe;
    }

    public function setNumPieceJointe(string $numPieceJointe): static
    {
        $this->numPieceJointe = $numPieceJointe;
        return $this;
    }

    public function getNumCandidature(): ?string
    {
        return $this->numCandidature;
    }

    public function setNumCandidature(?string $numCandidature): static
    {
        $this->numCandidature = $numCandidature;
        return $this;
    }

    public function getTailleFichier(): ?int
    {
        return $this->tailleFichier;
    }

    public function setTailleFichier(?int $tailleFichier): static
    {
        $this->tailleFichier = $tailleFichier;
        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): static
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function getDateModification(): ?\DateTimeInterface
    {
        return $this->dateModification;
    }

    public function setDateModification(?\DateTimeInterface $dateModification): static
    {
        $this->dateModification = $dateModification;
        return $this;
    }
}