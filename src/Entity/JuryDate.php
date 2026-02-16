<?php

namespace App\Entity;

use App\Repository\JuryDateRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: JuryDateRepository::class)]
class JuryDate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_passage = null;

    #[ORM\Column]
    private ?int $capacite_max = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTimeInterface $created_at = null;

    #[ORM\ManyToOne(inversedBy: 'juryDates')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Jury $jury = null;


    public function __construct()
    {
        $this->created_at = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDatePassage(): ?\DateTimeInterface
    {
        return $this->date_passage;
    }

    public function setDatePassage(\DateTimeInterface $date_passage): static
    {
        $this->date_passage = $date_passage;

        return $this;
    }

    public function getCapaciteMax(): ?int
    {
        return $this->capacite_max;
    }

    public function setCapaciteMax(int $capacite_max): static
    {
        $this->capacite_max = $capacite_max;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getJury(): ?Jury
    {
        return $this->jury;
    }

    public function setJury(?Jury $jury): static
    {
        $this->jury = $jury;

        return $this;
    }
}
