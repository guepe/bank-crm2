<?php

namespace Guepe\CrmBankBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

use Guepe\CrmBankBundle\Entity\Lead;

class LeadSearchForm extends AbstractType {
	public function buildForm(FormBuilder $builder, array $options) {
		$builder->add('name', 'text', array('label' => 'Nom'));

	}

	public function getName() {
		return 'leadsearch';
	}

}
