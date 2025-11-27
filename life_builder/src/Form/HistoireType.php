<?php

namespace App\Form;

use App\Entity\Histoire;
use App\Entity\Personnage;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HistoireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            //->add('ordreAffichage')
            ->add('titre')
            ->add('categorie')
            ->add('description')
            // ->add('personnage', EntityType::class, [
            //     'class' => Personnage::class,
            //     'choice_label' => 'id',
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Histoire::class,
        ]);
    }
}
