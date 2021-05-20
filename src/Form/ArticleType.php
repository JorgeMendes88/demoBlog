<?php

namespace App\Form;

use App\Entity\Article;

use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('titre', TextType::class, [
                'attr' => [
                    'maxlenght' => false,
                ],
                'required' => false
            ])
            // On définit un champ qui permet de choisir la catégorie de l'article
            // C'est un champ qui provient d'une entité : Category
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'titre' // le contenu de la liste déroulante sera le titre des catégories
            ])
            ->add('contenu', CKEditorType::class, [
                'attr' => [
                    'maxlenght' => false,
                ],
                'required' => false
            ])
            ->add('image', FileType::class, [
                'mapped' => true, // siginfie que le champ est associé à une propriété et qu'il sera inséré en BDD
                'required' => false,
                'data_class' => null,
                'constraints' => [
                    new File([
                        'maxSize' => '5M', // taille maximum d'upload d'image
                        'mimeTypes' => [ // format accepté
                            'image/jpeg',
                            'image/png',
                            'image/jpg'
                        ],
                        'mimeTypesMessage' => 'Formats autorisés : jpg/jpeg/png'
                    ])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
