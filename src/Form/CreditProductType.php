<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Account;
use App\Entity\CreditProduct;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreditProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('number', null, ['label' => 'Numero'])
            ->add('type', null, ['required' => false])
            ->add('company', TextareaType::class, ['required' => false])
            ->add('description', TextareaType::class, ['required' => false])
            ->add('references', null, ['label' => 'Reference', 'required' => false])
            ->add('notes', TextareaType::class, ['required' => false])
            ->add('tauxInteret', null, ['label' => 'Taux interet', 'required' => false])
            ->add('amount', MoneyType::class, ['label' => 'Montant', 'required' => false, 'currency' => 'EUR'])
            ->add('duration', null, ['label' => 'Duree', 'required' => false])
            ->add('recurrentPrimeAmount', MoneyType::class, ['label' => 'Prime recurrente', 'required' => false, 'currency' => 'EUR'])
            ->add('paymentDate', null, ['label' => 'Date de paiement', 'required' => false])
            ->add('variability', ChoiceType::class, [
                'required' => false,
                'choices' => [
                    'Fixe' => 'Fixe',
                    'Variable' => 'Variable',
                    'Mixte' => 'Mixte',
                ],
            ])
            ->add('purpose', TextareaType::class, ['label' => 'Objet', 'required' => false])
            ->add('garantee', TextareaType::class, ['label' => 'Garantie', 'required' => false])
            ->add('startDate', DateType::class, ['label' => 'Debut', 'required' => false, 'widget' => 'single_text'])
            ->add('endDate', DateType::class, ['label' => 'Fin', 'required' => false, 'widget' => 'single_text'])
            ->add('categories', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'multiple' => true,
                'required' => false,
                'by_reference' => false,
            ])
            ->add('accounts', EntityType::class, [
                'class' => Account::class,
                'choice_label' => 'name',
                'multiple' => true,
                'required' => false,
                'by_reference' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CreditProduct::class,
        ]);
    }
}
