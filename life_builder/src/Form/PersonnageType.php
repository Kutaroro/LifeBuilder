<?php

namespace App\Form;

use App\Entity\Personnage;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Image;

class PersonnageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('image', FileType::class, [
                'label' => 'Image Principale',
                'mapped' => false, 
                'required' => false,
                'constraints' => [
                    new Image(['maxSize' => '2M'])
                ]
            ])
            ->add('imagesSecondaires', FileType::class, [
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
            ->add('isPublic')
            ->add('description')

           
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Personnage::class,
        ]);
    }
}
