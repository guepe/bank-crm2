<?php

namespace Guepe\CrmBankBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

use Guepe\CrmBankBundle\Entity\Contact;

class AccountSearchForm extends AbstractType {
	public function buildForm(FormBuilder $builder, array $options) {
		$builder->add('motcle', 'text', array('label' => 'Nom du compte'));
		;
	}

	public function getName() {
		return 'accountsearch';
	}

}
