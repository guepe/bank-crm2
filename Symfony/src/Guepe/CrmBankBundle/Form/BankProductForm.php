<?php

namespace Guepe\CrmBankBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class BankProductForm extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
        	->add('type','choice',array('choices' =>
            	 array(
            	 'vue' => 'vue',
            	 'epargne' => 'epargne',
            	 'epargne+' => 'epargne+'
            	 )))
            ->add('references',null,array("label" => "Références"))
            ->add('number',null,array("label" => "Numéro de compte"))
            ->add('company',null,array("label" => "Organisme"))
            ->add('amount','money',array("label" => "Montant"))
            ->add('notes','textarea',array("label" => "Notes"))
            ->add('tauxinteret',null,array("label" => "Taux d'intérêt"))
            ->add('description','textarea',array("label" => "Description"))
        ;
    }

    public function getName()
    {
        return 'guepe_crmbankbundle_bankproducttype';
    }
}
