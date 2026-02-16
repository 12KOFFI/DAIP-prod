<?php

namespace App\Form;

use App\Entity\Candidature;
use App\Entity\Formation;
use App\Entity\Recrutement;
use App\Entity\Vae;
use App\Entity\Metier;
use App\Entity\NiveauEtude;
use App\Entity\Diplome;
use App\Service\NationaliteService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use App\Form\PieceJointeType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class CandidatureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $contextType = $options['context_type'] ?? null;

        $this->addPersonalInformationFields($builder);
        $this->addEducationFields($builder, $options);
        $this->addCfaSelectionFields($builder);

        if ($contextType !== 'recrutement') {
            $this->addProfessionalSituationFields($builder);
        }

        $this->addDocumentFields($builder);
        $this->addApplicationTypeFields($builder);
    }

    private function addPersonalInformationFields(FormBuilderInterface $builder): void
    {
        $builder
            ->add('nom', null, [
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom est obligatoire']),
                ]
            ])
            ->add('prenom', null, [
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le prénom est obligatoire']),
                ]
            ])
            ->add('sexe', ChoiceType::class, [
                'choices' => [
                    'Masculin' => 'MASCULIN',
                    'Féminin' => 'FEMININ',
                ],
                'placeholder' => 'Choisissez votre sexe',
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le sexe est obligatoire']),
                ]
            ])
            ->add('dateNaissance', null, [
                'label' => 'Date de naissance',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La date de naissance est obligatoire']),
                ]
            ])
            ->add('lieuNaissance', null, [
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('situationMatrimoniale', ChoiceType::class, [
                'choices' => [
                    'Célibataire' => 'CELIBATAIRE',
                    'Marié(e)' => 'MARIE',
                    'Divorcé(e)' => 'DIVORCE',
                    'Veuf/Veuve' => 'VEUF',
                ],
                'placeholder' => 'Choisissez votre situation',
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('nomJeuneFille', null, [
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('adresse', null, [
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('contacts', null, [
                'label' => 'Contact 1',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Numéro de téléphone principal'
                ],
                'row_attr' => ['class' => 'mb-3'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le contact principal est obligatoire']),
                ]
            ])
            ->add('contact2', null, [
                'label' => 'Contact 2',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Numéro de téléphone secondaire'
                ],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('contactUrgence', null, [
                'label' => 'Contact personne d\'urgence',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Numéro de la personne à contacter en cas d\'urgence'
                ],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('nomPrenomUrgence', null, [
                'label' => 'Nom & prénom personne d\'urgence',
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('nationalite', ChoiceType::class, [
                'label' => 'Nationalité',
                'choices' => [
                    'Ivoirienne' => 'CI',
                ],
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\NotBlank(['message' => 'La nationalité est obligatoire.']),
                ],
                'attr' => [
                    'class' => 'form-select',
                    'data-controller' => 'select2'
                ],
                'row_attr' => ['class' => 'mb-3'],
                'required' => true
            ])
            ->add('disponibilite', ChoiceType::class, [
                'label' => 'Disponibilité',
                'choices' => [
                    'Oui' => 'OUI',
                    'Non plus tard' => 'NON_PLUS_TARD'
                ],
                'placeholder' => 'Sélectionnez une option',
                'attr' => ['class' => 'form-select'],
                'row_attr' => ['class' => 'mb-3']
            ])
            // Les champs numPiece et numCmu ont été masqués
        ;
    }

    private function addEducationFields(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('niveauEtude', EntityType::class, [
                'class' => NiveauEtude::class,
                'choice_label' => 'libelle',
                'placeholder' => 'Choisissez votre niveau d\'études',
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3']
            ])
            // Le champ diplome a été masqué
            ->add('metier', EntityType::class, [
                'class' => Metier::class,
                'choices' => $options['metiers'] ?? [],
                'choice_label' => 'nom',
                'placeholder' => 'Sélectionnez un métier',
                'label' => 'Métier',
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le métier est obligatoire']),
                ]
            ]);
    }

    private function addCfaSelectionFields(FormBuilderInterface $builder): void
    {
        $builder
            ->add('secteur', EntityType::class, [
                'class' => \App\Entity\Secteur::class,
                'choice_label' => 'nom',
                'placeholder' => 'Sélectionnez un secteur',
                'label' => 'Secteur d\'activité',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'id' => 'candidature_secteur'
                ],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('cfaEtablissement', EntityType::class, [
                'class' => \App\Entity\CfaEtablissement::class,
                'choice_label' => 'nomEtablissement',
                'placeholder' => 'Sélectionnez d\'abord un secteur et un métier',
                'label' => 'Établissement CFA',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'id' => 'candidature_cfaEtablissement'
                ],
                'row_attr' => ['class' => 'mb-3']
            ]);
    }

    private function addProfessionalSituationFields(FormBuilderInterface $builder): void
    {
        $builder
            ->add('situationpro', ChoiceType::class, [
                'label' => 'Situation professionnelle',
                'choices' => [
                    'En activité' => 'EN_ACTIVITE',
                    'En recherche d\'emploi' => 'RECHERCHE_EMPLOI',
                    'Étudiant' => 'ETUDIANT',
                    'Autre' => 'AUTRE'
                ],
                'placeholder' => 'Sélectionnez votre situation',
                'attr' => ['class' => 'form-select'],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('experience', ChoiceType::class, [
                'label' => 'Nombre d\'années d\'expérience',
                'choices' => [
                    'Moins d\'un an' => 'MOINS_D_UN_AN',
                    '1 à 3 ans' => 'UN_A_TROIS_ANS',
                    '3 à 5 ans' => 'TROIS_A_CINQ_ANS',
                    '5 à 10 ans' => 'CINQ_A_DIX_ANS',
                    'Plus de 10 ans' => 'PLUS_DE_DIX_ANS',
                    'Aucune expérience' => 'AUCUNE_EXPERIENCE'
                ],
                'placeholder' => 'Sélectionnez votre expérience',
                'attr' => ['class' => 'form-select'],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('titrepro', null, [
                'label' => 'Titre professionnel',
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('entreprise', null, [
                'label' => 'Entreprise, organisme ou Administration d\'attache',
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('direction', null, [
                'label' => 'Direction/Service',
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('fonction', null, [
                'label' => 'Poste de travail',
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('contrat', null, [
                'label' => 'Référence du contrat de travail',
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('lieuentreprise', null, [
                'label' => 'Lieu d\'exercice (Région, département, ville)',
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('refentreprise', null, [
                'label' => 'Référence entreprise',
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('apprentiforme', null, [
                'label' => 'Nombre d\'apprentis formés en tant que Maître-Artisan',
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('apprentirecrute', null, [
                'label' => 'Nombre d\'apprentis formés pour le compte du ministère de l\'enseignement technique',
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3']
            ]);
    }

    private function addDocumentFields(FormBuilderInterface $builder): void
    {
        $builder
            ->add('medias_piece', FileType::class, [
                'label' => 'CNI ou Attestation d\'Identité',
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control',
                    'accept' => 'application/pdf,image/jpeg,image/png'
                ],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('medias_extrait', FileType::class, [
                'label' => 'Extrait de naissance',
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control',
                    'accept' => 'application/pdf,image/jpeg,image/png'
                ],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('medias_niveau', FileType::class, [
                'label' => 'Document justificatif du niveau',
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control',
                    'accept' => 'application/pdf,image/jpeg,image/png'
                ],
                'row_attr' => ['class' => 'mb-3'],
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\NotBlank([
                        'message' => 'Le diplôme ou l\'attestation est obligatoire.'
                    ])
                ],
                'required' => true
            ])
            ->add('medias_cmu', FileType::class, [
                'label' => 'Carte CMU',
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control',
                    'accept' => 'application/pdf,image/jpeg,image/png'
                ],
                'row_attr' => ['class' => 'mb-3'],
                'required' => false
            ])
            ->add('medias_photo', FileType::class, [
                'label' => 'Photo d\'identité',
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control',
                    'accept' => 'image/jpeg,image/png'
                ],
                'row_attr' => ['class' => 'mb-3']
            ]);
    }

    private function addApplicationTypeFields(FormBuilderInterface $builder): void
    {
        $builder
            ->add('dateCandidature', DateType::class, [
                'label' => 'Date de candidature',
                'widget' => 'single_text',
                'html5' => true,
                'required' => false,
                'data' => new \DateTime(),
                'attr' => ['class' => 'form-control d-none'],
                'row_attr' => ['class' => 'mb-3 d-none']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Candidature::class,
            'context_type' => null,
            'metiers' => [],
            'is_edit' => false,
            'is_admin' => false,
        ]);
    }
}
