<?php

namespace App\Form;

use App\Entity\Candidature;
use App\Entity\EvaluationCandidature;
use App\Entity\GrilleEvaluation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class EvaluationCandidatureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Gestion du champ candidature
        if (isset($options['candidature']) && $options['candidature']) {
            // Si on a une candidature, on l'affiche en lecture seule
            $builder->add('candidature', null, [
                'disabled' => true,
                'mapped' => false,
                'data' => $options['candidature'],
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3'],
                'label' => 'Candidat',
                'choice_label' => 'nomComplet'
            ]);
        } else {
            // Sinon, on affiche la liste déroulante normale
            $builder->add('candidature', EntityType::class, [
                'class' => Candidature::class,
                'choice_label' => 'nomComplet',
                'attr' => ['class' => 'form-select'],
                'row_attr' => ['class' => 'mb-3'],
                'placeholder' => 'Sélectionnez une candidature'
            ]);
        }

        // Ajout du champ libelle
        $builder
            ->add('libelle', ChoiceType::class, [
                'choices' => [
                    'ETUDE DE DOSSIER' => 'etudeDossier',
                    'ENTRETIEN MOTIVATION' => 'entretienMotivation',
                    'TEST TECHNIQUE' => 'testTechnique',
                    'VISITE MEDICALE' => 'visiteMedicale'
                ],
                'attr' => [
                    'class' => 'form-select',
                ],
                'row_attr' => ['class' => 'mb-3'],
                'label' => 'Type d\'évaluation',
                'required' => true,
                'placeholder' => 'Sélectionnez un type d\'évaluation'
            ])
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'Acceptée' => 'acceptee',
                    'Rejetée' => 'rejetee'
                ],
                'attr' => ['class' => 'form-select'],
                'row_attr' => ['class' => 'mb-3'],
                'label' => 'Statut de l\'évaluation',
                'placeholder' => 'Sélectionnez un statut',
                'required' => true
            ])
            ->add('commentaire', null, [
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3
                ],
                'row_attr' => ['class' => 'mb-3'],
                'label' => 'Commentaire',
                'required' => false
            ]);

        // Ajout des autres champs du formulaire
        $builder

            ->add('grilleEvaluation', EntityType::class, [
                'class' => GrilleEvaluation::class,
                'choice_label' => 'nom',
                'attr' => [
                    'class' => 'form-select',
                    'required' => false
                ],
                'row_attr' => [
                    'class' => 'mb-3',
                    'style' => 'display: none;' // Caché par défaut
                ],
                'placeholder' => 'Sélectionnez une grille d\'évaluation',
                'required' => false // Rendre le champ optionnel
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EvaluationCandidature::class,
            'allow_extra_fields' => true,
            'candidature' => null, // On ajoute l'option candidature
        ]);
    }
}
