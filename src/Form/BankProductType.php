<?php

namespace App\Form;

use App\Entity\BankProduct;
use App\Entity\Account;
use App\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BankProductType extends AbstractType
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
            'data_class' => BankProduct::class,
        ]);
    }
}
