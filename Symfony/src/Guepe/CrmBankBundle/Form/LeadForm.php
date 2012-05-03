<?php

namespace Guepe\CrmBankBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class LeadForm extends AbstractType {
	public function buildForm(FormBuilder $builder, array $options) {
		$builder
				->add('name', 'text',
						array("label" => "Nom", 'required' => false))
				->add('street_num', 'text',
						array("label" => "Rue et numéro", 'required' => false))
				->add('city', 'text',
						array("label" => "Ville", 'required' => false))
				->add('zip', 'text',
						array("label" => "Code postal", 'required' => false))
				->add('country', 'country',
						array("label" => "Pays",
								'preferred_choices' => array('BE'),
								'required' => false))
		;
	}

	public function getDefaultOptions(array $options) {
		return array('data_class' => 'Guepe\CrmBankBundle\Entity\Lead',);
	}

	public function getName() {
		return 'lead';
	}

}
