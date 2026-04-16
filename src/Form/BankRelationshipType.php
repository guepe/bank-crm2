<?php

namespace App\Form;

use App\Entity\BankRelationship;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BankRelationshipType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('bankName', null, ['label' => 'Banque'])
            ->add('bankContactName', null, ['label' => 'Interlocuteur bancaire', 'required' => false])
            ->add('bankContactEmail', null, ['label' => 'E-mail interlocuteur', 'required' => false])
            ->add('bankContactPhone', null, ['label' => 'Telephone interlocuteur', 'required' => false])
            ->add('notes', TextareaType::class, ['label' => 'Notes', 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BankRelationship::class,
        ]);
    }
}
