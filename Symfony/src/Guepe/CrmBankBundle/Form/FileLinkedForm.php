<?php

namespace Guepe\CrmBankBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;



class FileLinkedForm extends AbstractType
{
   public function buildForm(FormBuilder $builder, array $options)
    {        
        $builder
            ->add('name')
            ->add('type')
            ->add('filedata')
            ;
    }
    
 	public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Guepe\CrmBankBundle\Entity\FileLinked',
        );
    }
    
    public function getName()
    {
        return 'filelinked';
    }    
    
}
