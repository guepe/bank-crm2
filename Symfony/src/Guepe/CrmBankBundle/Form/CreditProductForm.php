<?php

namespace Guepe\CrmBankBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class CreditProductForm extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('Number')
            ->add('Garantee')
            ->add('Purpose')
            ->add('Type','choice',array('choices' =>
            	 array(
            	 'pret hypo' => 'pret hypo',
            	 )))
        ;
    }

    public function getName()
    {
        return 'guepe_crmbankbundle_bankproducttype';
    }
}
