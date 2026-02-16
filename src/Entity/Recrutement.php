<?php

namespace App\Entity;

use App\Repository\RecrutementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecrutementRepository::class)]
class Recrutement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\Column]
    private ?\DateTime $dateDebut = null;

    #[ORM\Column]
    private ?\DateTime $dateFin = null;

    // Le champ 'cible' (Public cible) a été retiré

    #[ORM\Column(nullable: true)]
    private ?int $ageMin = null;

    #[ORM\Column(nullable: true)]
    private ?int $ageMax = null;

    #[ORM\Column(name: 'date_limite_age', type: 'date', nullable: true)]
    private ?\DateTimeInterface $dateLimiteAge = null;

    #[ORM\Column(name: 'nationalite', length: 255, nullable: true)]
    private ?string $nationalite = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $status = null;

    #[ORM\Column(length: 255)]
    private ?string $image = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logo = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $banniere = null;

    
    #[ORM\ManyToOne(inversedBy: 'recrutement')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?Projet $projet = null;

    /**
     * @var Collection<int, Candidature>
     */
    #[ORM\OneToMany(targetEntity: Candidature::class, mappedBy: 'recrutement', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $candidature;


    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numRecrutement = null;

    #[ORM\ManyToOne(inversedBy: 'Recrutement')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'recrutements')]
    private ?Centre $centre = null;

    /**
     * @var Collection<int, Metier>
     */
    #[ORM\ManyToMany(targetEntity: Metier::class, inversedBy: 'recrutements')]
    private Collection $metiers;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $texte_annonce = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $date_inscription = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $listeParcours = [];

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $conCandidature = [];

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $DosCandidature = [];

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $texteParcours = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageAnnonce = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $badgeText = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $badgeColor = '#1c3faa';

    /**
     * @var Collection<int, ProcessusEtape>
     */


    /**
     * @var Collection<int, ProgramEvaluation>
     */
    #[ORM\OneToMany(targetEntity: ProgramEvaluation::class, mappedBy: 'recrutement')]
    private Collection $programEvaluations;

    /**
     * @var Collection<int, Critere>
     */
    #[ORM\OneToMany(targetEntity: Critere::class, mappedBy: 'recrutement')]
    private Collection $criteres;

  
     
    

    public function __construct()
    {
        $this->candidature = new ArrayCollection();
        $this->metiers = new ArrayCollection();
        $this->programEvaluations = new ArrayCollection();
        $this->criteres = new ArrayCollection();
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

    public function getDateDebut(): ?\DateTime
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTime $dateDebut): static
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateFin(): ?\DateTime
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTime $dateFin): static
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    // Les méthodes getCible et setCible ont été retirées

    public function getAgeMin(): ?int
    {
        return $this->ageMin;
    }

    public function setAgeMin(?int $ageMin): static
    {
        $this->ageMin = $ageMin;

        return $this;
    }

    public function getAgeMax(): ?int
    {
        return $this->ageMax;
    }

    public function setAgeMax(?int $ageMax): static
    {
        $this->ageMax = $ageMax;

        return $this;
    }

    public function getDateLimiteAge(): ?\DateTimeInterface
    {
        return $this->dateLimiteAge;
    }

    public function setDateLimiteAge(?\DateTimeInterface $dateLimiteAge): static
    {
        $this->dateLimiteAge = $dateLimiteAge;

        return $this;
    }

    public function getNationalite(): ?string
    {
        return $this->nationalite;
    }

    public function setNationalite(?string $nationalite): static
    {
        $this->nationalite = $nationalite;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): static
    {
        $this->logo = $logo;

        return $this;
    }

    public function getTexteParcours(): ?string
    {
        return $this->texteParcours;
    }

    public function setTexteParcours(?string $texteParcours): static
    {
        $this->texteParcours = $texteParcours;

        return $this;
    }

    public function getBanniere(): ?string
    {
        return $this->banniere;
    }

    public function setBanniere(?string $banniere): static
    {
        $this->banniere = $banniere;

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
            $candidature->setRecrutement($this);
        }

        return $this;
    }

    public function removeCandidature(Candidature $candidature): static
    {
        if ($this->candidature->removeElement($candidature)) {
            // set the owning side to null (unless already changed)
            if ($candidature->getRecrutement() === $this) {
                $candidature->setRecrutement(null);
            }
        }

        return $this;
    }



    public function getNumRecrutement(): ?string
    {
        return $this->numRecrutement;
    }

    public function setNumRecrutement(string $numRecrutement): static
    {
        $this->numRecrutement = $numRecrutement;

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
     * @return Collection<int, Metier>
     */
    public function getMetiers(): Collection
    {
        return $this->metiers;
    }

    public function getTexteAnnonce(): ?string
    {
        return $this->texte_annonce;
    }

    public function setTexteAnnonce(?string $texte_annonce): static
    {
        $this->texte_annonce = $texte_annonce;
        return $this;
    }

    public function getDateInscription(): ?string
    {
        return $this->date_inscription;
    }

    public function setDateInscription(?string $date_inscription): static
    {
        $this->date_inscription = $date_inscription;

        return $this;
    }

    public function getListeParcours(): ?array
    {
        return $this->listeParcours;
    }

    public function setListeParcours(?array $listeParcours): static
    {
        $this->listeParcours = $listeParcours;

        return $this;
    }

    public function getConCandidature(): ?array
    {
        return $this->conCandidature;
    }

    public function setConCandidature(?array $conCandidature): static
    {
        $this->conCandidature = $conCandidature;

        return $this;
    }

    public function getDosCandidature(): ?array
    {
        return $this->DosCandidature;
    }

    public function setDosCandidature(?array $DosCandidature): static
    {
        $this->DosCandidature = $DosCandidature;

        return $this;
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

    public function getImageAnnonce(): ?string
    {
        return $this->imageAnnonce;
    }

    public function setImageAnnonce(?string $imageAnnonce): static
    {
        $this->imageAnnonce = $imageAnnonce;
        return $this;
    }

    public function getBadgeText(): ?string
    {
        return $this->badgeText;
    }

    public function setBadgeText(?string $badgeText): static
    {
        $this->badgeText = $badgeText;
        return $this;
    }

    public function getBadgeColor(): ?string
    {
        return $this->badgeColor;
    }

    public function setBadgeColor(?string $badgeColor): static
    {
        $this->badgeColor = $badgeColor;
        return $this;
    }


    /**
 * @return Collection<int, ProgramEvaluation>
 */
public function getProgramEvaluations(): Collection
{
    return $this->programEvaluations;
}

public function addProgramEvaluation(ProgramEvaluation $programEvaluation): static
{
    if (!$this->programEvaluations->contains($programEvaluation)) {
        $this->programEvaluations->add($programEvaluation);
        $programEvaluation->setRecrutement($this);
    }
    return $this;
}

public function removeProgramEvaluation(ProgramEvaluation $programEvaluation): static
{
    if ($this->programEvaluations->removeElement($programEvaluation)) {
        if ($programEvaluation->getRecrutement() === $this) {
            $programEvaluation->setRecrutement(null);
        }
    }
    return $this;
}


    /**
     * @return Collection<int, ProcessusEtape>
     */

    /**
     * @return Collection<int, Critere>
     */
    public function getCriteres(): Collection
    {
        return $this->criteres;
    }

    public function addCritere(Critere $critere): static
    {
        if (!$this->criteres->contains($critere)) {
            $this->criteres->add($critere);
            $critere->setRecrutement($this);
        }

        return $this;
    }

    public function removeCritere(Critere $critere): static
    {
        if ($this->criteres->removeElement($critere)) {
            // set the owning side to null (unless already changed)
            if ($critere->getRecrutement() === $this) {
                $critere->setRecrutement(null);
            }
        }

        return $this;
    }

    
}
