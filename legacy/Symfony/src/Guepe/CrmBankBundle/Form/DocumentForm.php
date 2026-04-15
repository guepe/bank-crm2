<?php

namespace Guepe\CrmBankBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class DocumentForm extends AbstractType {
	public function buildForm(FormBuilder $builder, array $options) {
		$builder
				->add('name','text',array("label" => "Nom ou type", 'required' => false))
				->add('file',null,array("label" => "Fichier", 'required' => false));
	}

	public function getName() {
		return 'guepe_crmbankbundle_documenttype';
	}
}
