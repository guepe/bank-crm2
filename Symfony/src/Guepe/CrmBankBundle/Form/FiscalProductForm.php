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
				->add('references', null, array("label" => "Références", 'required' => false))
				->add('number', null, array("label" => "Numéro de compte", 'required' => false))
				->add('company', null, array("label" => "Organisme", 'required' => false))
				->add('startdate', 'date', array("label" => "Date de début",'widget' => 'single_text','format' => 'dd/MM/YYYY', 'required' => false))
				->add('enddate', 'date', array("label" => "Date de fin",'widget' => 'single_text','format' => 'dd/MM/YYYY', 'required' => false))
				->add('notes', 'textarea', array("label" => "Notes", 'required' => false))
				->add('RecurrentPrimeAmount', 'money',
						array("label" => "Montant Prime annuelle", 'required' => false))
				->add('capitalterme', 'money', array("label" => "Capital à terme", 'required' => false))
				->add('tauxinteret', null, array("label" => "Taux d'intérêt", 'required' => false))
				->add('garantee', null, array("label" => "Garanties", 'required' => false))
				->add('description', 'textarea', array("label" => "Description", 'required' => false))
				->add('paymentdate', 'text', array("label" => "Date de paiement", 'required' => false))
				->add('paymentdeadline', null,
						array("label" => "Echéance de paiement", 'required' => false))
				->add('reserve', 'money', array("label" => "Réserve", 'required' => false))
				->add('reservedate', 'date', array("label" => "Date de réserve",'widget' => 'single_text','format' => 'dd/MM/yyyy', 'required' => false))

		;
	}

	public function getName() {
		return 'guepe_crmbankbundle_fiscalproducttype';
	}
}
