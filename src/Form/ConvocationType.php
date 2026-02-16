<?php

namespace App\Form;

use App\Entity\Convocation;
use App\Entity\Candidature;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConvocationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('sujet', null, [
                'label' => 'Sujet',
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('contenu', null, [
                'label' => 'Contenu',
                'attr' => ['class' => 'form-control', 'rows' => 5],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('dateEnvoi', null, [
                'label' => 'Date d\'envoi',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('candidature', EntityType::class, [
                'label' => 'Candidature',
                'class' => Candidature::class,
                'choice_label' => 'id',
                'attr' => ['class' => 'form-select'],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('user', EntityType::class, [
                'label' => 'Utilisateur',
                'class' => User::class,
                'choice_label' => 'email',
                'attr' => ['class' => 'form-select'],
                'row_attr' => ['class' => 'mb-3']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Convocation::class,
        ]);
    }
}
