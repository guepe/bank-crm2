<?php

namespace App\Form;

use App\Entity\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', null, ['label' => 'Prenom', 'required' => false])
            ->add('lastname', null, ['label' => 'Nom'])
            ->add('streetNum', null, ['label' => 'Rue et numero', 'required' => false])
            ->add('zip', null, ['label' => 'Code postal', 'required' => false])
            ->add('city', null, ['label' => 'Ville', 'required' => false])
            ->add('country', CountryType::class, [
                'label' => 'Pays',
                'required' => false,
                'preferred_choices' => ['BE'],
            ])
            ->add('email', null, ['required' => false])
            ->add('phone', null, ['label' => 'Telephone', 'required' => false])
            ->add('phone2', null, ['label' => 'Autre telephone', 'required' => false])
            ->add('gsm', null, ['required' => false])
            ->add('birthplace', null, ['label' => 'Lieu de naissance', 'required' => false])
            ->add('birthdate', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('eid', null, ['label' => "Numero de carte d'identite", 'required' => false])
            ->add('niss', null, ['label' => 'Numero national', 'required' => false])
            ->add('profession', null, ['required' => false])
            ->add('maritalStatus', ChoiceType::class, [
                'label' => 'Statut marital',
                'required' => false,
                'choices' => [
                    'Celibataire' => 1,
                    'Marie' => 2,
                    'Divorce' => 3,
                    'Veuf(ve)' => 4,
                ],
            ])
            ->add('incomeAmount', IntegerType::class, ['label' => 'Revenus', 'required' => false])
            ->add('incomeRecurence', ChoiceType::class, [
                'label' => 'Frequence des rentrees',
                'required' => false,
                'choices' => [
                    'Mensuel' => 'monthly',
                    'Annuel' => 'annual',
                ],
            ])
            ->add('incomeDate', null, ['label' => 'Date des rentrees', 'required' => false])
            ->add('chargedPeople', IntegerType::class, ['label' => 'Personnes a charge', 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
        ]);
    }
}
