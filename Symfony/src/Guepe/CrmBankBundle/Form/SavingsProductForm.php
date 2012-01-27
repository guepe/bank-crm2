<?php

namespace Guepe\CrmBankBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class SavingsProductForm extends AbstractType {
	public function buildForm(FormBuilder $builder, array $options) {
		$builder
				->add('type', 'choice',
						array(
								'choices' => array('BR21' => 'BR21',
										'BR23' => 'BR23', 'BR26' => 'BR26',
										'SAFE' => 'SAFE',
										'Booster' => 'Booster',
										'SICAV' => 'SICAV',
										'Obligations' => 'Obligations',
										'Titres' => 'Titres',
										'Terme' => 'Terme', 'BC' => 'BC',
								)))
				->add('references', null, array("label" => "Références"))
				->add('number', null, array("label" => "Numéro de compte"))
				->add('company', null, array("label" => "Organisme"))
				->add('amount', 'money', array("label" => "Montant"))
				->add('startdate', 'date',
						array("label" => "Date de début",
								'widget' => 'single_text','format' => 'dd/MM/yyyy'))
				->add('enddate', 'date',
						array("label" => "Date de fin",
								'widget' => 'single_text','format' => 'dd/MM/yyyy'))
				->add('notes', 'textarea', array("label" => "Notes"))
				->add('capitalterme', 'money',
						array("label" => "Capital à terme"))
				->add('tauxinteret', null, array("label" => "Taux d'intérêt"))
				->add('duration', null, array("label" => "Durée"))
				->add('garantee', null, array("label" => "Garanties"))
				->add('description', 'textarea', array("label" => "Description"))
				->add('paymentdate', 'date',
						array("label" => "Date de paiement",
								'widget' => 'single_text','format' => 'dd/MM/yyyy'))
				->add('paymentdeadline', null,
						array("label" => "Echéance de paiement"))
				->add('reserve', 'money', array("label" => "Réserve"))
				->add('reservedate', 'date',
						array("label" => "Date de réserve",
								'widget' => 'single_text','format' => 'dd/MM/yyyy'));
	}

	public function getName() {
		return 'guepe_crmbankbundle_savingsproducttype';
	}
}
