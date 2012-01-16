<?php

namespace Guepe\CrmBankBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

use Guepe\CrmBankBundle\Entity\Contact;



class ContactSearchForm extends AbstractType
{
   public function buildForm(FormBuilder $builder, array $options)
    {        
        $builder->add('lastname', 'text', array('label' => 'Nom'));
       		 	
    	      
    }
    
    public function getName()
    {
        return 'contactsearch';
    }    
    
}
