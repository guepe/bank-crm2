<?php

namespace Guepe\CrmBankBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class BankProductForm extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('number',null,array("label" => "Numéro de compte"))
            ->add('amount','money',array("label" => "Montant"))
            ->add('type','choice',array('choices' =>
            	 array(
            	 'courant' => 'courant',
            	 'epargne' => 'epargne',
            	 'titre' => 'titre',
            	 'terme' => 'terme'
            	 )))
        ;
    }

    public function getName()
    {
        return 'guepe_crmbankbundle_bankproducttype';
    }
}
