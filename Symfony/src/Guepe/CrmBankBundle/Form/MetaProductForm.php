<?php

namespace Guepe\CrmBankBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;


class MetaProductForm extends AbstractType
{
   public function buildForm(FormBuilder $builder, array $options)
    {        
        $builder
            ->add('name')
            ->add('type','choice',array('choices' =>
            	 array(
            	 'vue' => 'Vue',
            	 'epargne' => 'Epargne',
            	 'titre' => 'Titre',
            	 'terme' => 'Terme'
            	 )))
        ;
    }
    
 	public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Guepe\CrmBankBundle\Entity\MetaProduct',
        );
    }
    
    public function getName()
    {
        return 'metaproduct';
    }    
    
}
