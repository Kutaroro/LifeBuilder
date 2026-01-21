<?php

namespace App\Form;

use App\Entity\Apparence;
use App\Entity\Personnage;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Image;

class ApparenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre')
            ->add('images', FileType::class, [
                'label' => 'Ajouter des photos (Galerie)',
                'mapped' => false, 
                'multiple' => true, 
                'required' => false,
                'constraints' => [
                    new Count(['max' => 5, 'maxMessage' => 'Maximum 5 images à la fois']),
                    new All([
                        new Image(['maxSize' => '2M'])
                    ])
                ]
            ])
            ->add('description')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Apparence::class,
        ]);
    }
}
