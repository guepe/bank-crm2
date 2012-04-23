<?php

namespace Guepe\CrmBankBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class BankProductForm extends AbstractType {
	public function buildForm(FormBuilder $builder, array $options) {
		$builder
				->add('type', 'choice',
						array(
								'choices' => array('vue' => 'vue',
										'epargne' => 'epargne',
										'epargne+' => 'epargne+'),
								'required' => false))
				->add('references', null,
						array("label" => "Références", 'required' => false))
				->add('number', null,
						array("label" => "Numéro de compte",
								'required' => false))
				->add('company', null,
						array("label" => "Organisme", 'required' => false))
				->add('amount', 'money',
						array("label" => "Montant", 'required' => false))
				->add('notes', 'textarea',
						array("label" => "Notes", 'required' => false))
				->add('tauxinteret', null,
						array("label" => "Taux d'intérêt", 'required' => false))
				->add('description', 'textarea',
						array("label" => "Description", 'required' => false));
	}

	public function getName() {
		return 'guepe_crmbankbundle_bankproducttype';
	}
}
