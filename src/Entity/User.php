<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: 'L\'email est obligatoire')]
    #[Assert\Email(message: 'Veuillez entrer un email valide')]
    #[Assert\Length(
        max: 180,
        maxMessage: 'L\'email ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    /**
     * @var string|null The plain password (not persisted)
     */
    #[Assert\NotBlank(message: 'Le mot de passe est obligatoire', groups: ['registration'])]
    #[Assert\Length(
        min: 6,
        max: 50,
        minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le mot de passe ne peut pas dépasser {{ limit }} caractères',
        groups: ['registration']
    )]
    private ?string $plainPassword = null;





    /**
     * @var Collection<int, Recrutement>
     */
    #[ORM\OneToMany(targetEntity: Recrutement::class, mappedBy: 'user')]
    private Collection $Recrutement;

    /**
     * @var Collection<int, Projet>
     */
    #[ORM\OneToMany(targetEntity: Projet::class, mappedBy: 'user')]
    private Collection $Projet;

    /**
     * @var Collection<int, Formation>
     */
    #[ORM\OneToMany(targetEntity: Formation::class, mappedBy: 'user')]
    private Collection $Formation;

    /**
     * @var Collection<int, Candidature>
     */
    #[ORM\OneToMany(targetEntity: Candidature::class, mappedBy: 'user')]
    private Collection $Candidature;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: Jury::class)]
    private ?Jury $jury = null;

    /**
     * @var Collection<int, EvaluationCandidature>
     */
    #[ORM\OneToMany(targetEntity: EvaluationCandidature::class, mappedBy: 'user')]
    private Collection $Evaluation;

    #[ORM\ManyToOne(inversedBy: 'users')]
    private ?NiveauEtude $NiveauEtude = null;


#[ORM\ManyToOne(inversedBy: 'users')]
private ?CfaEtablissement $cfaEtablissement = null;

    /**
     * @var Collection<int, Diplome>
     */
    #[ORM\ManyToMany(targetEntity: Diplome::class, inversedBy: 'users')]
    private Collection $Diplome;

    public function __construct()
    {
        $this->Recrutement = new ArrayCollection();
        $this->Projet = new ArrayCollection();
        $this->Formation = new ArrayCollection();
        $this->Candidature = new ArrayCollection();
        $this->Evaluation = new ArrayCollection();
        $this->Diplome = new ArrayCollection();
        
        // Définir le rôle CANDIDAT par défaut
        $this->roles = ['ROLE_CANDIDAT'];
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0" . self::class . "\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    /**
     * @return Collection<int, Recrutement>
     */
    public function getRecrutement(): Collection
    {
        return $this->Recrutement;
    }

    public function addRecrutement(Recrutement $recrutement): static
    {
        if (!$this->Recrutement->contains($recrutement)) {
            $this->Recrutement->add($recrutement);
            $recrutement->setUser($this);
        }

        return $this;
    }

    public function removeRecrutement(Recrutement $recrutement): static
    {
        if ($this->Recrutement->removeElement($recrutement)) {
            // set the owning side to null (unless already changed)
            if ($recrutement->getUser() === $this) {
                $recrutement->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Projet>
     */
    public function getProjet(): Collection
    {
        return $this->Projet;
    }

    public function addProjet(Projet $projet): static
    {
        if (!$this->Projet->contains($projet)) {
            $this->Projet->add($projet);
            $projet->setUser($this);
        }

        return $this;
    }

    public function removeProjet(Projet $projet): static
    {
        if ($this->Projet->removeElement($projet)) {
            // set the owning side to null (unless already changed)
            if ($projet->getUser() === $this) {
                $projet->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Formation>
     */
    public function getFormation(): Collection
    {
        return $this->Formation;
    }

    public function addFormation(Formation $formation): static
    {
        if (!$this->Formation->contains($formation)) {
            $this->Formation->add($formation);
            $formation->setUser($this);
        }

        return $this;
    }

    public function removeFormation(Formation $formation): static
    {
        if ($this->Formation->removeElement($formation)) {
            // set the owning side to null (unless already changed)
            if ($formation->getUser() === $this) {
                $formation->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Candidature>
     */
    public function getCandidature(): Collection
    {
        return $this->Candidature;
    }

    public function addCandidature(Candidature $candidature): static
    {
        if (!$this->Candidature->contains($candidature)) {
            $this->Candidature->add($candidature);
            $candidature->setUser($this);
        }

        return $this;
    }

    public function removeCandidature(Candidature $candidature): static
    {
        if ($this->Candidature->removeElement($candidature)) {
            // set the owning side to null (unless already changed)
            if ($candidature->getUser() === $this) {
                $candidature->setUser(null);
            }
        }

        return $this;
    }

    public function getJury(): ?Jury
    {
        return $this->jury;
    }

    public function setJury(?Jury $jury): static
    {
        // unset the owning side of the relation if necessary
        if ($jury === null && $this->jury !== null) {
            $this->jury->setUser(null);
        }

        // set the owning side of the relation if necessary
        if ($jury !== null && $jury->getUser() !== $this) {
            $jury->setUser($this);
        }

        $this->jury = $jury;
        return $this;
    }

    /**
     * @return Collection<int, EvaluationCandidature>
     */
    public function getEvaluation(): Collection
    {
        return $this->Evaluation;
    }

    public function addEvaluation(EvaluationCandidature $evaluation): static
    {
        if (!$this->Evaluation->contains($evaluation)) {
            $this->Evaluation->add($evaluation);
            $evaluation->setUser($this);
        }

        return $this;
    }

    public function removeEvaluation(EvaluationCandidature $evaluation): static
    {
        if ($this->Evaluation->removeElement($evaluation)) {
            // set the owning side to null (unless already changed)
            if ($evaluation->getUser() === $this) {
                $evaluation->setUser(null);
            }
        }

        return $this;
    }

    public function getNiveauEtude(): ?NiveauEtude
    {
        return $this->NiveauEtude;
    }

    public function setNiveauEtude(?NiveauEtude $NiveauEtude): static
    {
        $this->NiveauEtude = $NiveauEtude;

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
     * @return Collection<int, Diplome>
     */
    public function getDiplome(): Collection
    {
        return $this->Diplome;
    }

    public function addDiplome(Diplome $diplome): static
    {
        if (!$this->Diplome->contains($diplome)) {
            $this->Diplome->add($diplome);
        }

        return $this;
    }

    public function removeDiplome(Diplome $diplome): static
    {
        $this->Diplome->removeElement($diplome);

        return $this;
    }
}
