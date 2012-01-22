<?php

namespace Guepe\CrmBankBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class FiscalProductForm extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('RevisionDate')
            ->add('UniquePrimeAmount')
            ->add('UniquePrimeDate')
            ->add('RecurrentPrimeAmount')
            ->add('CapitalTerme')
            ->add('Reserve')
            ->add('ReserveDate')
            ->add('Type','choice',array('choices' =>
            	 array(
            	 'pret hypo' => 'pret hypo',
            	 )))
        ;
    }

    public function getName()
    {
        return 'guepe_crmbankbundle_fiscalproducttype';
    }
}
