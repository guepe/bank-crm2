<?php

namespace Guepe\CrmBankBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class ContactForm extends AbstractType {
	public function buildForm(FormBuilder $builder, array $options) {
		$builder
				->add('firstname', 'text',
						array("label" => "Prénom", 'required' => false))
				->add('lastname', 'text',
						array("label" => "Nom", 'required' => true))
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
				->add('email', 'email',
						array("label" => "Email", 'required' => false))
				->add('phone', 'text',
						array("label" => "Téléphone", 'required' => false))
				->add('gsm', 'text',
						array("label" => "GSM", 'required' => false))
				->add('birthplace', 'text',
						array("label" => "Lieu de naissance",
								'required' => false))
				->add('birthdate', 'birthday',
						array("label" => "Date de naissance",
								'required' => false, 'widget' => 'single_text',
								'format' => 'dd/MM/yyyy'))
				->add('eid', 'text',
						array("label" => "Numéro de carte d'identité",
								'required' => false))
				->add('niss', 'text',
						array("label" => "Numéro nationnal",
								'required' => false))
				->add('profession', 'text',
						array("label" => "Profession", 'required' => false))
				->add('marital_status', 'choice',
						array("label" => "Statut marital", 'required' => false,
								'choices' => array('single' => 'célibataire',
										'window' => 'veuf(ve)',
										'married' => 'marié',
										'separated' => 'divorcé')))
				->add('income_amount', 'money',
						array("label" => "Revenus", 'required' => false))
				->add('income_recurence', 'choice',
						array("label" => "Fréquence des rentrées",
								'required' => false,
								'choices' => array('monthly' => 'Mensuel',
										'annual' => 'Annuel')))
				->add('income_date', 'text',
						array("label" => 'Date des rentrées',
								'required' => false))
		// ->add('files')
		;
	}

	public function getDefaultOptions(array $options) {
		return array('data_class' => 'Guepe\CrmBankBundle\Entity\Contact',);
	}

	public function getName() {
		return 'contact';
	}

}
