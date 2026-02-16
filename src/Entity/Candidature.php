<?php

namespace App\Entity;

use App\Repository\CandidatureRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CandidatureRepository::class)]
class Candidature
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $prenom = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column]
    private ?int $contacts = null;

    #[ORM\Column(type: 'date')]
    private ?\DateTimeInterface $dateNaissance = null;

    #[ORM\Column(length: 255)]
    private ?string $adresse = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $dateCandidature = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $statut = null;





    #[ORM\ManyToOne(inversedBy: 'candidature')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?Recrutement $recrutement = null;

    /**
     * @var Collection<int, PieceJointe>
     */
    #[ORM\OneToMany(targetEntity: PieceJointe::class, mappedBy: 'candidature', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $piece_jointe;

    #[ORM\ManyToOne(inversedBy: 'candidature')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?Formation $formation = null;

    /**
     * @var Collection<int, EvaluationCandidature>
     */
    #[ORM\OneToMany(targetEntity: EvaluationCandidature::class, mappedBy: 'candidature', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $evaluation;

    #[ORM\ManyToOne(inversedBy: 'candidature')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?Vae $vae = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numCandidature = null;

    /**
     * @var Collection<int, Filiere>
     */
    #[ORM\OneToMany(targetEntity: Filiere::class, mappedBy: 'candidature', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $Filiere;

    #[ORM\ManyToOne(inversedBy: 'Candidature')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'candidatures')]
    private ?Metier $metier = null;

    #[ORM\ManyToOne]
    private ?Secteur $secteur = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?CfaEtablissement $cfaEtablissement = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $sexe = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $lieuNaissance = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $situationMatrimoniale = null;

    public function getNomComplet(): string
    {
        return trim(sprintf('%s %s', $this->prenom ?? '', $this->nom ?? ''));
    }

    public function __toString(): string
    {
        return $this->getNomComplet();
    }

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $nomJeuneFille = null;

    #[ORM\ManyToOne(targetEntity: NiveauEtude::class, inversedBy: 'candidatures')]
    private ?NiveauEtude $niveauEtude = null;

    #[ORM\ManyToOne(targetEntity: Diplome::class, inversedBy: 'candidatures')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Diplome $diplome = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $disponibilite = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $numPiece = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $situationpro = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $experience = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $titrepro = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $entreprise = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $direction = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $fonction = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $contrat = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $lieuentreprise = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $refentreprise = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $apprentiforme = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $apprentirecrute = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numCmu = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $contact2 = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $nomPrenomUrgence = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $contactUrgence = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $nationalite = null;

    /**
     * @var Collection<int, StatutCandidature>
     */


    public function __construct()
    {
        $this->piece_jointe = new ArrayCollection();
        $this->evaluation = new ArrayCollection();
        $this->Filiere = new ArrayCollection();

        // Valeurs par défaut
        $this->dateCandidature = new \DateTime();
        $this->statut = 'EN_ATTENTE';
        $this->numCandidature = 'TEMP_' . uniqid(); // Valeur temporaire qui sera remplacée par le repository
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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;
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

    public function getContacts(): ?int
    {
        return $this->contacts;
    }

    public function setContacts(int $contacts): static
    {
        $this->contacts = $contacts;
        return $this;
    }

    public function getDateNaissance(): ?\DateTimeInterface
    {
        return $this->dateNaissance;
    }

    public function setDateNaissance(\DateTimeInterface $dateNaissance): static
    {
        $this->dateNaissance = $dateNaissance;
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

    public function getDateCandidature(): ?\DateTimeInterface
    {
        return $this->dateCandidature;
    }

    public function setDateCandidature(?\DateTimeInterface $dateCandidature): static
    {
        $this->dateCandidature = $dateCandidature;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function getRecrutement(): ?Recrutement
    {
        return $this->recrutement;
    }

    public function setRecrutement(?Recrutement $recrutement): static
    {
        $this->recrutement = $recrutement;
        return $this;
    }

    /**
     * @return Collection<int, PieceJointe>
     */
    public function getPieceJointe(): Collection
    {
        return $this->piece_jointe;
    }

    public function addPieceJointe(PieceJointe $pieceJointe): static
    {
        if (!$this->piece_jointe->contains($pieceJointe)) {
            $this->piece_jointe->add($pieceJointe);
            $pieceJointe->setCandidature($this);
        }
        return $this;
    }

    public function removePieceJointe(PieceJointe $pieceJointe): static
    {
        if ($this->piece_jointe->removeElement($pieceJointe)) {
            if ($pieceJointe->getCandidature() === $this) {
                $pieceJointe->setCandidature(null);
            }
        }
        return $this;
    }


    public function getFormation(): ?Formation
    {
        return $this->formation;
    }

    public function setFormation(?Formation $formation): static
    {
        $this->formation = $formation;
        return $this;
    }

    /**
     * @var Collection<int, EvaluationCandidature>
     */
    public function getEvaluation(): Collection
    {
        return $this->evaluation;
    }

    public function addEvaluation(EvaluationCandidature $evaluation): static
    {
        if (!$this->evaluation->contains($evaluation)) {
            $this->evaluation->add($evaluation);
            $evaluation->setCandidature($this);
        }
        return $this;
    }

    public function removeEvaluation(EvaluationCandidature $evaluation): static
    {
        if ($this->evaluation->removeElement($evaluation)) {
            if ($evaluation->getCandidature() === $this) {
                $evaluation->setCandidature(null);
            }
        }
        return $this;
    }

    public function getVae(): ?Vae
    {
        return $this->vae;
    }

    public function setVae(?Vae $vae): static
    {
        $this->vae = $vae;
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
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

    public function getSexe(): ?string
    {
        return $this->sexe;
    }

    public function setSexe(?string $sexe): static
    {
        $this->sexe = $sexe;
        return $this;
    }

    public function getLieuNaissance(): ?string
    {
        return $this->lieuNaissance;
    }

    public function setLieuNaissance(?string $lieuNaissance): static
    {
        $this->lieuNaissance = $lieuNaissance;
        return $this;
    }

    public function getSituationMatrimoniale(): ?string
    {
        return $this->situationMatrimoniale;
    }

    public function setSituationMatrimoniale(?string $situationMatrimoniale): static
    {
        $this->situationMatrimoniale = $situationMatrimoniale;
        return $this;
    }

    public function getNomJeuneFille(): ?string
    {
        return $this->nomJeuneFille;
    }

    public function setNomJeuneFille(?string $nomJeuneFille): static
    {
        $this->nomJeuneFille = $nomJeuneFille;
        return $this;
    }

    public function getNiveauEtude(): ?NiveauEtude
    {
        return $this->niveauEtude;
    }

    public function setNiveauEtude(?NiveauEtude $niveauEtude): static
    {
        $this->niveauEtude = $niveauEtude;
        return $this;
    }



    public function getDiplome(): ?Diplome
    {
        return $this->diplome;
    }

    public function setDiplome(?Diplome $diplome): static
    {
        $this->diplome = $diplome;
        return $this;
    }

    /**
     * @return Collection<int, Filiere>
     */
    public function getFiliere(): Collection
    {
        return $this->Filiere;
    }

    public function addFiliere(Filiere $filiere): static
    {
        if (!$this->Filiere->contains($filiere)) {
            $this->Filiere->add($filiere);
            $filiere->setCandidature($this);
        }
        return $this;
    }

    public function removeFiliere(Filiere $filiere): static
    {
        if ($this->Filiere->removeElement($filiere)) {
            // set the owning side to null (unless already changed)
            if ($filiere->getCandidature() === $this) {
                $filiere->setCandidature(null);
            }
        }
        return $this;
    }


    public function getDisponibilite(): ?string
    {
        return $this->disponibilite;
    }

    public function setDisponibilite(?string $disponibilite): static
    {
        $this->disponibilite = $disponibilite;
        return $this;
    }

    public function getNumPiece(): ?string
    {
        return $this->numPiece;
    }

    public function setNumPiece(?string $numPiece): static
    {
        $this->numPiece = $numPiece;
        return $this;
    }

    public function getSituationpro(): ?string
    {
        return $this->situationpro;
    }

    public function setSituationpro(?string $situationpro): static
    {
        $this->situationpro = $situationpro;
        return $this;
    }

    public function getExperience(): ?string
    {
        return $this->experience;
    }

    public function setExperience(?string $experience): static
    {
        $this->experience = $experience;
        return $this;
    }

    public function getTitrepro(): ?string
    {
        return $this->titrepro;
    }

    public function setTitrepro(?string $titrepro): static
    {
        $this->titrepro = $titrepro;
        return $this;
    }

    public function getEntreprise(): ?string
    {
        return $this->entreprise;
    }

    public function setEntreprise(?string $entreprise): static
    {
        $this->entreprise = $entreprise;
        return $this;
    }

    public function getDirection(): ?string
    {
        return $this->direction;
    }

    public function setDirection(?string $direction): static
    {
        $this->direction = $direction;
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

    public function getContrat(): ?string
    {
        return $this->contrat;
    }

    public function setContrat(?string $contrat): static
    {
        $this->contrat = $contrat;
        return $this;
    }

    public function getLieuentreprise(): ?string
    {
        return $this->lieuentreprise;
    }

    public function setLieuentreprise(?string $lieuentreprise): static
    {
        $this->lieuentreprise = $lieuentreprise;
        return $this;
    }

    public function getRefentreprise(): ?string
    {
        return $this->refentreprise;
    }

    public function setRefentreprise(?string $refentreprise): static
    {
        $this->refentreprise = $refentreprise;
        return $this;
    }

    public function getApprentiforme(): ?string
    {
        return $this->apprentiforme;
    }

    public function setApprentiforme(?string $apprentiforme): static
    {
        $this->apprentiforme = $apprentiforme;
        return $this;
    }

    public function getApprentirecrute(): ?string
    {
        return $this->apprentirecrute;
    }

    public function setApprentirecrute(?string $apprentirecrute): static
    {
        $this->apprentirecrute = $apprentirecrute;
        return $this;
    }

    public function getNumCmu(): ?string
    {
        return $this->numCmu;
    }

    public function setNumCmu(?string $numCmu): static
    {
        $this->numCmu = $numCmu;
        return $this;
    }

    public function getContact2(): ?string
    {
        return $this->contact2;
    }

    public function setContact2(?string $contact2): static
    {
        $this->contact2 = $contact2;
        return $this;
    }

    public function getNomPrenomUrgence(): ?string
    {
        return $this->nomPrenomUrgence;
    }

    public function setNomPrenomUrgence(?string $nomPrenomUrgence): static
    {
        $this->nomPrenomUrgence = $nomPrenomUrgence;
        return $this;
    }

    public function getContactUrgence(): ?string
    {
        return $this->contactUrgence;
    }

    public function setContactUrgence(?string $contactUrgence): static
    {
        $this->contactUrgence = $contactUrgence;
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

    public function getSecteur(): ?Secteur
    {
        return $this->secteur;
    }

    public function setSecteur(?Secteur $secteur): static
    {
        $this->secteur = $secteur;
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

    /**
     * @return Collection<int, StatutCandidature>
     */
}
