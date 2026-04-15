<?php

namespace Guepe\CrmBankBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

use Guepe\CrmBankBundle\Entity\Contact;

class AccountForm extends AbstractType {
	public function buildForm(FormBuilder $builder, array $options) {
		$builder->add('name', null, array('label' => "Nom"))
				->add('company_statut', 'choice',
						array(
								'choices' => array(
										'Personne physique' => 'Personne physique',
										'S.C.' => 'S.C.', 'SPRL' => 'SPRL',
										'SA' => 'SA',
										'ASS de fait' => 'ASS de fait',
										'Indivision' => 'Indivision',
										'Administration' => 'Administration'),
								'label' => "Statut", 'required' => false))
				->add('type', 'choice',
						array(
								'choices' => array('Core' => 'Core',
										'Potentiel' => 'Potentiel',
										'Standard' => 'Standard'),
								'label' => "Type", 'required' => false))
				->add('starting_date', 'date',
						array('label' => "Début de la relation",
								'input' => 'datetime',
								'widget' => 'single_text',
								'format' => 'dd/MM/yyyy', 'required' => false))
				->add('streetnum', null,
						array('label' => "Rue et numéro", 'max_length' => 100,
								'attr' => array("size" => "58"),
								'required' => false))
				->add('zip', null,
						array('label' => "Code postal", 'required' => false))
				->add('city', null,
						array('label' => "Ville", 'required' => false))
				->add('country', 'country',
						array("label" => "Pays",
								'preferred_choices' => array('BE'),
								'required' => false))
				->add('notes', null,
						array('label' => "Notes", 'required' => false))
				->add('otherbank', null,
						array('label' => "Autre banque", 'required' => false))
				->add('contacts', 'collection',
						array('type' => new ContactForm()))
				->add('bankproduct', 'collection',
						array('type' => new BankProductForm()))
				->add('creditproduct', 'collection',
						array('type' => new CreditProductForm()))
				->add('fiscalproduct', 'collection',
						array('type' => new FiscalProductForm()))
				->add('savingsproduct', 'collection',
						array('type' => new SavingsProductForm()));

		;
	}

	public function getDefaultOptions(array $options) {
		return array('data_class' => 'Guepe\CrmBankBundle\Entity\Account',);
	}

	public function getName() {
		return 'account';
	}

}
