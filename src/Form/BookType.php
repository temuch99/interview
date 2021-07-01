<?php

namespace App\Form;

use App\Entity\Book;
use App\Entity\Author;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class BookType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Название'
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Описание'
            ])
            ->add('public_at', DateType::class, [
                'label' => 'Дата публикации',
                'widget' => 'single_text',
                // 'input'  => 'datetime_immutable',
                'placeholder' => [
                    'year' => 'Год', 'month' => 'Месяц', 'day' => 'День',
                ]
            ])
            ->add('authors', EntityType::class, [
                'class' => Author::class,
                'multiple' => true
            ])
            ->add('picture', FileType::class, [
                'label' => 'Обложка',

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload the PDF file
                // every time you edit the Product details
                'required' => $options['require_picture'],

                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new Image([])
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        // $resolver->setDefaults([
        //     'data_class' => Book::class,
        // ]);
        $resolver->setDefaults([
            'data_class' => Book::class,
            'require_picture' => false,
            'csrf_token_id'   => 'book',
        ]);

        // $resolver->setAllowedTypes('require_picture', 'bool');
    }
}
