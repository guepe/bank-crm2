<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', TextType::class, [
                'mapped' => false,
                'label' => 'Prénom',
                'constraints' => [
                    new NotBlank(),
                    new Length(max: 100),
                ],
            ])
            ->add('lastname', TextType::class, [
                'mapped' => false,
                'label' => 'Nom',
                'constraints' => [
                    new NotBlank(),
                    new Length(max: 100),
                ],
            ])
            ->add('username', TextType::class, [
                'label' => "Nom d'utilisateur",
                'constraints' => [
                    new NotBlank(),
                    new Length(min: 3, max: 180),
                ],
            ])
            ->add('email', EmailType::class, [
                'required' => true,
                'label' => 'Email',
                'constraints' => [
                    new NotBlank(),
                    new Email(),
                    new Length(max: 180),
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'first_options' => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Confirmation du mot de passe'],
                'constraints' => [
                    new NotBlank(),
                    new Length(min: 8, max: 255),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
