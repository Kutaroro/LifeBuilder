<?php

namespace App\Form;

use App\Entity\ModStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType; // Ne pas oublier cet import
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ModStatusType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('status', ChoiceType::class, [
                'label' => 'Statut de la sanction',
                'choices' => [
                    'Pas de sanction en cours' => 'Pas de sanction en cours',
                    'Avertissement'           => 'Avertissement',
                    'Bannissement temporaire' => 'Bannissement temporaire',
                    'Bannissement définitif'  => 'Bannissement définitif',
                ],
            ])
            ->add('type')
            ->add('dateFin')
            ->add('description')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ModStatus::class,
        ]);
    }
}