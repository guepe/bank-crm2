<?php

namespace Guepe\CrmBankBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

use Guepe\CrmBankBundle\Entity\Contact;



class AccountForm extends AbstractType
{
   public function buildForm(FormBuilder $builder, array $options)
    {        
        $builder
            ->add('name')
            ->add('company_statut','choice',array('choices' =>
            	 array(
            	 'Personne physique' => 'Personne physique',
            	 'S.C.' => 'S.C.',
            	 'SPRL' => 'SPRL',
            	 'SA' => 'SA',
            	 'ASS de fait' => 'ASS de fait',
            	 'Indivision' => 'Indivision',
            	 'Administration' => 'Administration'
            	 )))
            ->add('notes')
            ->add('type','choice',array('choices' =>
            	array(
            	'Core' => 'Core',
            	'Potentiel' => 'Potentiel',
            	'Standard' => 'Standard')))
            ->add('starting_date','date',array('input'=> 'datetime', 'widget' => 'single_text','format' => 'dd/MM/yyyy'))
            ->add('contacts', 'collection', array('type' => new ContactForm()))
			->add('bankproduct', 'collection', array('type' => new BankProductForm()))
			->add('creditproduct', 'collection', array('type' => new CreditProductForm()))
			->add('fiscalproduct', 'collection', array('type' => new FiscalProductForm()))
			->add('savingsproduct', 'collection', array('type' => new SavingsProductForm()));
			
			
            
            
        ;
    }
    
 	public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Guepe\CrmBankBundle\Entity\Account',
        );
    }
    
    public function getName()
    {
        return 'account';
    }    
    
}
