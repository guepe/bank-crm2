<?php

namespace Guepe\CrmBankBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;



class ContactForm extends AbstractType
{
   public function buildForm(FormBuilder $builder, array $options)
    {        
        $builder
            ->add('firstname')
            ->add('lastname','text',array('required' => true))
            ->add('street_num')
            ->add('city')
            ->add('zip')
            ->add('country','country')
            ->add('email')
            ->add('phone')
            ->add('gsm')
            ->add('birthplace')
            ->add('birthdate','birthday',array('widget' => 'single_text'))
            ->add('eid')
            ->add('niss')
            ->add('marital_status','choice',array('choices' =>
            	 array(
            	 'single' => 'célibataire',
            	 'window' => 'veuf(ve)',
            	 'married' => 'marié',
            	 'separated' => 'divorcé'
            	 )))
            ->add('income_amount','money')
            ->add('income_recurence','choice',array('choices' =>
            	 array(
            	 'monthly' => 'Mensuel',
            	 'annual' => 'Annuel'
            	 )))
            ->add('income_date','date',array('widget' => 'single_text'))
            //->add('charged_people')
           // ->add('files')
            ;
    }
    
 	public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Guepe\CrmBankBundle\Entity\Contact',
        );
    }
    
    public function getName()
    {
        return 'contact';
    }    
    
}
