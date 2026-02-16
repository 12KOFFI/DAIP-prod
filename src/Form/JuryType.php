<?php

namespace App\Form;

use App\Entity\Centre;
use App\Entity\Formation;
use App\Entity\Jury;
use App\Entity\Recrutement;
use App\Entity\User;
use App\Entity\Vae;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JuryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class)
            ->add('prenom', TextType::class)
            ->add('fonction', TextType::class, [
                'required' => false,
            ])
            ->add('organisation', TextType::class, [
                'required' => false,
            ])
            ->add('centre', EntityType::class, [
                'class' => Centre::class,
                'required' => false,
                'placeholder' => 'Sélectionnez un centre',
                'choice_label' => 'nom',
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'placeholder' => 'Sélectionnez un utilisateur',
                'choice_label' => 'email',
                'query_builder' => function (UserRepository $userRepository) {
                    return $userRepository
                        ->createQueryBuilder('u')
                        ->andWhere('u.roles LIKE :roleAdmin OR u.roles LIKE :roleJury')
                        ->setParameter('roleAdmin', '%"ROLE_ADMIN"%')
                        ->setParameter('roleJury', '%"ROLE_JURY"%')
                        ->orderBy('u.email', 'ASC');
                },
            ])
            ->add('recrutements', EntityType::class, [
                'class' => Recrutement::class,
                'required' => false,
                'multiple' => true,
                'by_reference' => false,
                'choice_label' => 'libelle',
            ])
            ->add('formations', EntityType::class, [
                'class' => Formation::class,
                'required' => false,
                'multiple' => true,
                'by_reference' => false,
                'choice_label' => 'libelle',
            ])
            ->add('vaes', EntityType::class, [
                'class' => Vae::class,
                'required' => false,
                'multiple' => true,
                'by_reference' => false,
                'choice_label' => 'nom',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Jury::class,
        ]);
    }
}
