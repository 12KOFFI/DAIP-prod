<?php

namespace App\Form;

use App\Entity\CfaEtablissement;
use App\Entity\Filiere;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class CfaEtablissementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomEtablissement', null, [
                'label' => 'Nom de l\'établissement',
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('nomChefEtablissement', null, [
                'label' => 'Nom du chef d\'établissement',
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('email', null, [
                'label' => 'Email',
                'attr' => [
                    'class' => 'form-control',
                    'type' => 'email'
                ],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('numcfaEtablissement', null, [
                'label' => 'Numéro etablissement',
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('region', null, [
                'label' => 'Région',
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('cfaMetiers', CollectionType::class, [
                'entry_type' => CfaMetierType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false,
                'attr' => ['class' => 'cfa-metiers-collection'],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('users', EntityType::class, [
                'class' => User::class,
                'choice_label' => function (User $user): string {
                    return $user->getEmail();
                },
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.roles LIKE :role')
                        ->setParameter('role', '%ROLE_USER_CENTRE%');
                },
                'multiple' => true,
                'expanded' => false,
                'by_reference' => false,
                'attr' => [
                    'class' => 'form-select',
                    'data-choices' => 'true',
                    'data-choices-removeItem' => 'true'
                ],
                'row_attr' => ['class' => 'mb-3'],
                'placeholder' => 'Sélectionnez un ou plusieurs utilisateurs',
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CfaEtablissement::class,
        ]);
    }
}
