<?php

namespace App\Form;

use App\Entity\Centre;
use App\Entity\Metier;
use App\Entity\ProgramEvaluation;
use App\Entity\Recrutement;
use App\Entity\TypeEvaluation;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bundle\SecurityBundle\Security;

class ProgramEvaluationType extends AbstractType
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('typeEvaluation', EntityType::class, [
                'class' => TypeEvaluation::class,
                'choice_label' => 'nom',
                'placeholder' => 'Sélectionnez le type d\'évaluation',
                'required' => true,
                'label' => 'Type d\'évaluation',
                'attr' => ['class' => 'form-control']
            ])
            ->add('centre', EntityType::class, [
                'class' => Centre::class,
                'choice_label' => 'nom',
                'placeholder' => 'Sélectionnez le centre',
                'required' => true,
                'label' => 'Centre',
                'attr' => ['class' => 'form-control']
            ])
            ->add('metier', EntityType::class, [
                'class' => Metier::class,
                'choice_label' => 'nom',
                'placeholder' => 'Sélectionnez le métier',
                'required' => true,
                'label' => 'Métier',
                'attr' => ['class' => 'form-control']
            ])
            ->add('dateLancement', DateType::class, [
                'widget' => 'single_text',
                'required' => true,
                'label' => 'Date de lancement',
                'attr' => ['class' => 'form-control']
            ])
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'Ouvert' => 'OUVERT',
                    'En cours' => 'EN_COURS',
                    'Terminé' => 'TERMINE',
                    'Annulé' => 'ANNULE'
                ],
                'required' => true,
                'label' => 'Statut',
                'attr' => ['class' => 'form-control']
            ])
            ->add('recrutement', EntityType::class, [
                'class' => Recrutement::class,
                'choice_label' => 'libelle',
                'placeholder' => 'Associer à un recrutement (optionnel)',
                'required' => false,
                'label' => 'Recrutement associé',
                'attr' => ['class' => 'form-control']
            ]);

        // Si l'utilisateur est un administrateur, lui permettre de choisir l'utilisateur associé
        if ($this->security->isGranted('ROLE_ADMIN')) {
            $builder->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
                'placeholder' => 'Sélectionnez l\'utilisateur responsable',
                'required' => true,
                'label' => 'Responsable',
                'attr' => ['class' => 'form-control']
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProgramEvaluation::class,
        ]);
    }
}
