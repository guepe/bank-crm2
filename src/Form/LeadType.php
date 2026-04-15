<?php

namespace App\Form;

use App\Entity\Lead;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LeadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
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
            ->add('notes', TextareaType::class, ['required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lead::class,
        ]);
    }
}
