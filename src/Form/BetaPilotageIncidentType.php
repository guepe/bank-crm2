<?php

namespace App\Form;

use App\Entity\BetaPilotageIncident;
use App\Entity\Tenant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BetaPilotageIncidentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', null, ['label' => 'Titre'])
            ->add('tenant', EntityType::class, [
                'class' => Tenant::class,
                'choice_label' => static fn(Tenant $tenant): string => sprintf('%s (%s)', $tenant->getName(), $tenant->getPlanLabel()),
                'required' => false,
                'placeholder' => 'Aucun tenant specifique',
                'label' => 'Tenant concerne',
            ])
            ->add('category', ChoiceType::class, [
                'label' => 'Categorie',
                'choices' => array_flip(BetaPilotageIncident::categoryChoices()),
            ])
            ->add('severity', ChoiceType::class, [
                'label' => 'Severite',
                'choices' => array_flip(BetaPilotageIncident::severityChoices()),
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => array_flip(BetaPilotageIncident::statusChoices()),
            ])
            ->add('summary', TextareaType::class, [
                'label' => 'Synthese sans donnees dossier',
                'required' => false,
            ])
            ->add('resolutionNotes', TextareaType::class, [
                'label' => 'Notes de resolution',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BetaPilotageIncident::class,
        ]);
    }
}
