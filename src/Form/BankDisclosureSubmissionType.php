<?php

namespace App\Form;

use App\Entity\Account;
use App\Entity\Contact;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BankDisclosureSubmissionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Contact $contact */
        $contact = $options['contact'];

        $builder
            ->add('account', EntityType::class, [
                'class' => Account::class,
                'choices' => $contact->getAccounts()->toArray(),
                'choice_label' => 'name',
                'label' => 'Compte client concerne',
            ])
            ->add('number', null, ['label' => 'Numero du produit'])
            ->add('type', null, ['label' => 'Type de produit', 'required' => false])
            ->add('amount', MoneyType::class, ['label' => 'Montant', 'required' => false, 'currency' => 'EUR'])
            ->add('notes', TextareaType::class, ['label' => 'Notes', 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'contact' => null,
            'data_class' => null,
        ]);

        $resolver->setAllowedTypes('contact', Contact::class);
    }
}
