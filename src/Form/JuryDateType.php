<?php

namespace App\Form;

use App\Entity\Jury;
use App\Entity\JuryDate;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JuryDateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date_passage', null, [
                'widget' => 'single_text',
                'label' => 'Date de passage',
            ])
            ->add('capacite_max', null, [
                'label' => 'Capacité maximale',
            ])
            ->add('jury', EntityType::class, [
                'class' => Jury::class,
                'choice_label' => function (Jury $jury) {
                    return $jury->getNom() . ' ' . $jury->getPrenom();
                },
                'label' => 'Membre du Jury',
                'placeholder' => 'Choisir un membre du jury',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => JuryDate::class,
        ]);
    }
}
