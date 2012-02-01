<?php

namespace Guepe\CrmBankBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;



class ContactForm extends AbstractType
{
   public function buildForm(FormBuilder $builder, array $options)
    {        
        $builder
            ->add('firstname','text',array("label" => "Prénom"))
            ->add('lastname','text',array("label" => "Nom", 'required' => true))
            ->add('street_num','text',array("label" => "Rue et numéro"))
            ->add('city','text',array("label" => "Ville"))
            ->add('zip','text',array("label" => "Code postal"))
            ->add('country','country',array("label" => "Pays",'preferred_choices' => array('BE')))
            ->add('email','email',array("label" => "Email"))
            ->add('phone','text',array("label" => "Téléphone"))
            ->add('gsm','text',array("label" => "GSM"))
            ->add('birthplace','text',array("label" => "Lieu de naissance"))
            ->add('birthdate','birthday',array("label" => "Date de naissance", 'widget' => 'single_text','format' => 'dd/MM/yyyy'))
            ->add('eid','text',array("label" => "Numéro de carte d'identité"))
            ->add('niss','text',array("label" => "Numéro nationnal"))
            ->add('marital_status','choice',array("label" => "Statut marital", 'choices' =>
            	 array(
            	 'single' => 'célibataire',
            	 'window' => 'veuf(ve)',
            	 'married' => 'marié',
            	 'separated' => 'divorcé'
            	 )))
            ->add('income_amount','money',array("label" => "Moyen financiers"))
            ->add('income_recurence','choice',array("label" => "Fréquence des rentrées", 'choices' =>
            	 array(
            	 'monthly' => 'Mensuel',
            	 'annual' => 'Annuel'
            	 )))
            ->add('income_date','date',array("label" => 'Date des rentrées', 'widget' => 'single_text','format' => 'dd/MM/yyyy'))
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
