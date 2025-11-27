<?php

namespace App\Form;

use App\Entity\Personnage;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PersonnageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('image')
            ->add('isPublic')
            ->add('persoLies', EntityType::class, [
                'class' => Personnage::class,
                'choice_label' => 'id',
                'multiple' => true,
                'required' => false,
            ])
            ->add('personnagesLies', EntityType::class, [
                'class' => Personnage::class,
                'choice_label' => 'id',
                'multiple' => true,
                'required' => false,
            ])
           
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Personnage::class,
        ]);
    }
}
