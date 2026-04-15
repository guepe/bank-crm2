<?php

namespace App\Form;

use App\Entity\Account;
use App\Entity\Contact;
use App\Entity\MetaProduct;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, ['label' => 'Nom'])
            ->add('companyStatut', ChoiceType::class, [
                'label' => 'Statut',
                'required' => false,
                'choices' => [
                    'Personne physique' => 'Personne physique',
                    'S.C.' => 'S.C.',
                    'SPRL' => 'SPRL',
                    'SA' => 'SA',
                    'ASS de fait' => 'ASS de fait',
                    'Indivision' => 'Indivision',
                    'Administration' => 'Administration',
                ],
            ])
            ->add('type', ChoiceType::class, [
                'required' => false,
                'choices' => [
                    'Core' => 'Core',
                    'Potentiel' => 'Potentiel',
                    'Standard' => 'Standard',
                ],
            ])
            ->add('startingDate', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('streetNum', null, ['label' => 'Rue et numero', 'required' => false])
            ->add('zip', null, ['label' => 'Code postal', 'required' => false])
            ->add('city', null, ['label' => 'Ville', 'required' => false])
            ->add('country', CountryType::class, [
                'label' => 'Pays',
                'required' => false,
                'preferred_choices' => ['BE'],
            ])
            ->add('otherBank', null, ['label' => 'Autre banque', 'required' => false])
            ->add('notes', TextareaType::class, ['required' => false])
            ->add('contacts', EntityType::class, [
                'class' => Contact::class,
                'choice_label' => static fn(Contact $contact): string => (string) $contact,
                'multiple' => true,
                'required' => false,
                'by_reference' => false,
            ])
            ->add('products', EntityType::class, [
                'class' => MetaProduct::class,
                'choice_label' => static fn(MetaProduct $product): string => (string) $product,
                'multiple' => true,
                'required' => false,
                'by_reference' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Account::class,
        ]);
    }
}
