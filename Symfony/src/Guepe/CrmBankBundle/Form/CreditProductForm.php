<?php

namespace Guepe\CrmBankBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class CreditProductForm extends AbstractType {
	public function buildForm(FormBuilder $builder, array $options) {
		$builder
				->add('type', 'choice',
						array(
								'choices' => array('PH' => 'PH',
										'PAT' => 'PAT', 'PATP' => 'PATP',
										'CI' => 'CI', 'VAI' => 'VAI',
										'Caisse' => 'Caisse', 'SL' => 'SL',
								)))
				->add('references', null, array("label" => "Références"))
				->add('number', null, array("label" => "Numéro de compte"))
				->add('company', null, array("label" => "Organisme"))
				->add('amount', 'money', array("label" => "Montant"))
				->add('startdate', 'date', array("label" => "Date de début",'widget' => 'single_text','format' => 'dd/MM/yyyy'))
				->add('enddate', 'date', array("label" => "Date de fin",'widget' => 'single_text','format' => 'dd/MM/yyyy'))
				->add('notes', 'textarea', array("label" => "Notes"))
				->add('recurrentprimeamount', 'money',
						array("label" => "Montant mensualité"))
				->add('tauxinteret', null, array("label" => "Taux d'intérêt"))
				->add('duration', null, array("label" => "Durée"))
				->add('Variability', 'choice',
						array(
								'choices' => array('fixe' => 'fixe',
										'an' => 'an', '3/3/3' => '3/3/3',
										'5/5/5' => '5/5/5',
										'10/5/5' => '10/5/5', 'pont' => 'pont'),
								"label" => "Variabilité"))
				->add('Garantee', null, array("label" => "Garantie"))
				->add('Purpose', null, array("label" => "But"))
				->add('description', 'textarea', array("label" => "Description"))
				->add('paymentdate', 'date', array("label" => "Date de paiement",'widget' => 'single_text','format' => 'dd/MM/yyyy'));
	}

	public function getName() {
		return 'guepe_crmbankbundle_creditproducttype';
	}
}
