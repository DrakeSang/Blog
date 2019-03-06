<?php

namespace BlogBundle\Form;

use BlogBundle\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ArticleType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class,
                [
                    'constraints' =>
                        [
                            new NotBlank(),
                            new Length([
                                'min'=> 5,
                                'minMessage'=> "Title must be at least 5 characters long"
                            ])
                        ]
                ])
            ->add('content', TextType::class,
                [
                    'constraints' =>
                        [
                            new NotBlank(),
                            new Length([
                                'min'=> 10,
                                'minMessage' => "Content must be at least 10 characters long"
                            ])
                        ]
                ])
            ->add('image',FileType::class,
                ['data' => null,
                    'constraints' =>
                        [
                            new NotBlank(),
                            new File([
                                'mimeTypes' =>
                                    [
                                        "image/jpeg",
                                        "image/jpg",
                                        "image/png",
                                    ]
                            ])
                        ]
                ])
            ->add('category', EntityType::class,
                [
                    'class' => Category::class,
                    'choice_label' => 'name'
                ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'BlogBundle\Entity\Article'
        ));
    }
}
