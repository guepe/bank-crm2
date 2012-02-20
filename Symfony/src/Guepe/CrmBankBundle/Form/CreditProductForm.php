<?php

namespace Guepe\CrmBankBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class CreditProductForm extends AbstractType {
	public function buildForm(FormBuilder $builder, array $options) {
		$builder
				->add('type', 'choice',
						array( 'required' => false,
								'choices' => array('PH' => 'PH',
										'PAT' => 'PAT', 'PATP' => 'PATP',
										'CI' => 'CI', 'VAI' => 'VAI',
										'Caisse' => 'Caisse', 'SL' => 'SL',
								)))
				->add('references', null, array("label" => "Références", 'required' => false))
				->add('number', null, array("label" => "Numéro de compte", 'required' => false))
				->add('company', null, array("label" => "Organisme", 'required' => false))
				->add('amount', 'money', array("label" => "Montant", 'required' => false))
				->add('startdate', 'date', array("label" => "Date de début",'widget' => 'single_text','format' => 'dd/MM/yyyy', 'required' => false))
				->add('enddate', 'date', array("label" => "Date de fin",'widget' => 'single_text','format' => 'dd/MM/yyyy', 'required' => false))
				->add('notes', 'textarea', array("label" => "Notes", 'required' => false))
				->add('recurrentprimeamount', 'money',
						array("label" => "Montant mensualité", 'required' => false))
				->add('tauxinteret', null, array("label" => "Taux d'intérêt", 'required' => false))
				->add('duration', null, array("label" => "Durée", 'required' => false))
				->add('Variability', 'choice',
						array(
								'choices' => array('fixe' => 'fixe',
										'an' => 'an', '3/3/3' => '3/3/3',
										'5/5/5' => '5/5/5',
										'10/5/5' => '10/5/5', 'pont' => 'pont'),
								"label" => "Variabilité", 'required' => false))
				->add('Garantee', null, array("label" => "Garantie", 'required' => false))
				->add('Purpose', null, array("label" => "But", 'required' => false))
				->add('description', 'textarea', array("label" => "Description", 'required' => false))
				->add('paymentdate', 'text', array("label" => "Date de paiement", 'required' => false));
	}

	public function getName() {
		return 'guepe_crmbankbundle_creditproducttype';
	}
}
