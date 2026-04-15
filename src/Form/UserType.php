<?php

namespace App\Form;

use App\Entity\Contact;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username')
            ->add('email', null, ['required' => false])
            ->add('enabled', CheckboxType::class, ['required' => false])
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Utilisateur interne' => 'ROLE_USER',
                    'Administrateur' => 'ROLE_ADMIN',
                    'Client portail' => 'ROLE_CLIENT',
                ],
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('contact', EntityType::class, [
                'class' => Contact::class,
                'choice_label' => static fn(Contact $contact): string => (string) $contact,
                'required' => false,
                'placeholder' => 'Aucun contact lie',
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'required' => !$options['is_edit'],
                'first_options' => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Confirmation'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_edit' => false,
        ]);
    }
}
