<?php

// src/Form/ProductsFormType.php
namespace App\Form;

use App\Entity\Products;
use App\Entity\Categories;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType; // Correct importation du type FileType
use Symfony\Component\Validator\Constraints\File;



class ProductsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('description', TextareaType::class)
            ->add('price', IntegerType::class)
            ->add('stock', IntegerType::class)
            ->add('category', EntityType::class, [
                'class' => Categories::class,
                'choice_label' => 'name',
                'choices' => $options['categories'],  // Utilisation des catégories passées par le contrôleur
            ])
            
            ->add('image', FileType::class, [
                'label' => 'Image du produit',
                'mapped' => false, // Cette option indique que le champ 'image' n'est pas directement mappé à une propriété de l'entité.
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif'],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide.',
                    ])
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Products::class,
            'categories' => [],  // Valeur par défaut pour les catégories
        ]);
    }
}
