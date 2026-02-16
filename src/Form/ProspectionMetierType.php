<?php

namespace App\Form;

use App\Entity\ProspectionMetier;
use App\Entity\Prospection;
use App\Entity\Metier;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProspectionMetierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prospection', EntityType::class, [
                'label' => 'Prospection <span class="text-danger">*</span>',
                'label_html' => true,
                'class' => Prospection::class,
                'choice_label' => function (Prospection $prospection): string {
                    $parts = [];
                    if ($prospection->getDate()) {
                        $parts[] = $prospection->getDate()->format('d/m/Y');
                    }
                    if ($prospection->getStructureAcceuil()) {
                        $parts[] = $prospection->getStructureAcceuil()->getNomStructure();
                    }
                    if ($prospection->getCfaEtablissement()) {
                        $parts[] = $prospection->getCfaEtablissement()->getNomEtablissement();
                    }
                    $label = implode(' - ', $parts);
                    return $label !== '' ? $label : 'Prospection #' . $prospection->getId();
                },
                'placeholder' => 'Sélectionnez une prospection',
                'required' => true,
                'attr' => ['class' => 'form-select'],
                'row_attr' => ['class' => 'mb-3'],
            ])
            ->add('nombre_postes', null, [
                'label' => 'Nombre de postes <span class="text-danger">*</span>',
                'label_html' => true,
                'attr' => [
                    'class' => 'form-control',
                    'min' => 1
                ],
                'row_attr' => ['class' => 'mb-3']
            ])
        ;

        $formModifier = function (FormInterface $form, ?Prospection $prospection = null): void {
            $cfa = $prospection?->getCfaEtablissement();

            $form->add('metier', EntityType::class, [
                'label' => 'Métier <span class="text-danger">*</span>',
                'label_html' => true,
                'class' => Metier::class,
                'choice_label' => 'nom',
                'placeholder' => $cfa ? 'Sélectionnez un métier' : 'Sélectionnez d\'abord une prospection',
                'attr' => ['class' => 'form-select'],
                'row_attr' => ['class' => 'mb-3'],
                'required' => true,
                'query_builder' => function (EntityRepository $er) use ($cfa) {
                    $qb = $er->createQueryBuilder('m');

                    if ($cfa) {
                        $qb->innerJoin('m.filieres', 'f')
                            ->innerJoin('f.cfaEtablissements', 'c')
                            ->andWhere('c = :cfa')
                            ->setParameter('cfa', $cfa);
                    } else {
                        $qb->where('1 = 0');
                    }

                    return $qb->orderBy('m.nom', 'ASC');
                },
            ]);
        };

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($formModifier): void {
            /** @var ProspectionMetier|null $data */
            $data = $event->getData();
            $formModifier($event->getForm(), $data?->getProspection());
        });

        $builder->get('prospection')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($formModifier): void {
            /** @var Prospection|null $prospection */
            $prospection = $event->getForm()->getData();
            $formModifier($event->getForm()->getParent(), $prospection);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProspectionMetier::class,
        ]);
    }
}
