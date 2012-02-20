<?php

namespace Guepe\CrmBankBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class FiscalProductForm extends AbstractType {
	public function buildForm(FormBuilder $builder, array $options) {
		$builder
				->add('type', 'choice',
						array(
								'choices' => array('EP' => 'EP',
										'ASRD' => 'ASRD', 'PLCI' => 'PLCI',
										'PLCIS' => 'PLCIS', 'Vie' => 'Vie',
										'EIP' => 'EIP', 'Groupe' => 'Groupe',
										'INAMI' => 'INAMI', 'ADE' => 'ADE',
										'RG' => 'RG',)))
				->add('references', null, array("label" => "Références"))
				->add('number', null, array("label" => "Numéro de compte"))
				->add('company', null, array("label" => "Organisme"))
				->add('startdate', 'date', array("label" => "Date de début",'widget' => 'single_text','format' => 'dd/MM/YYYY'))
				->add('enddate', 'date', array("label" => "Date de fin",'widget' => 'single_text','format' => 'dd/MM/YYYY'))
				->add('notes', 'textarea', array("label" => "Notes"))
				->add('RecurrentPrimeAmount', 'money',
						array("label" => "Montant Prime annuelle"))
				->add('capitalterme', 'money', array("label" => "Capital à terme"))
				->add('tauxinteret', null, array("label" => "Taux d'intérêt"))
				->add('garantee', null, array("label" => "Garanties"))
				->add('description', 'textarea', array("label" => "Description"))
				->add('paymentdate', 'text', array("label" => "Date de paiement"))
				->add('paymentdeadline', null,
						array("label" => "Echéance de paiement"))
				->add('reserve', 'money', array("label" => "Réserve"))
				->add('reservedate', 'date', array("label" => "Date de réserve",'widget' => 'single_text','format' => 'dd/MM/yyyy'))

		;
	}

	public function getName() {
		return 'guepe_crmbankbundle_fiscalproducttype';
	}
}
