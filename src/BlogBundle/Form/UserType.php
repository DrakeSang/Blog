<?php

namespace BlogBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', TextType::class,
                [
                    'constraints' => [
                        new NotBlank(),
                        new Email(array('checkMX' => true))
                    ]
                ])
            ->add('fullName', TextType::class,
                [
                    'constraints' => [
                        new NotBlank(),
                        new Length([
                            'min'=> 8,
                            'minMessage'=> "Full name must be at least 8 characters long",
                            'max'=> 20,
                            'maxMessage'=> "Full name must not be longer than 20 characters"
                        ])
                    ]
                ])
            ->add('password', RepeatedType::class,
                [
                    'constraints' =>
                        [
                            new NotBlank()
                        ],
                    'type' => PasswordType::class,
                    'invalid_message' => 'The password fields must match.',
                    'required' => true,
                    'first_options' => array('label' => 'Password'),
                    'second_options' => array('label' => 'Repeat Password')
                ])
            ->add('image', FileType::class,
                [
                    'data' => null,
                    'constraints' => [
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
                ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'BlogBundle\Entity\User'
        ));
    }
}
