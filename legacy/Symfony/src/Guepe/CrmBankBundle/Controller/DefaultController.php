<?php

namespace Guepe\CrmBankBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerAware, Symfony\Component\HttpFoundation\RedirectResponse;

use Guepe\CrmBankBundle\Entity\Contact;
use Guepe\CrmBankBundle\Entity\Account;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class DefaultController extends Controller {

	/**
	 * @Route("/", name="GuepeCrmBankBundle_homepage")
	 */
	public function indexAction() {
		$name = "Index Ok";

		return $this
				->render('GuepeCrmBankBundle:Default:index.html.twig',
						array('name' => $name));
	}

	/**
	 * @Route("/Populate", name="Populate")
	 */

	public function populateAction() {
		$em = $this->container->get('doctrine')->getEntityManager();

		$contact = new Contact();
		$contact->setFirstname("Marcel");
		$contact->setLastname("Seul");
		$em->persist($contact);

		$account = new Account();
		$account->setName("Mr Seul");
		$account->addContact($contact);
		$account->setNotes("some info bla bla bla");
		$account->setOtherBank("dexia.... baaah");
		//    	$account->setStartingDate("2010-12-03");
		$account->setType("SC");
		//    	$account->addAccount($account);
		$em->persist($account);

		$contact2 = new Contact();
		$contact2->setFirstname("Josette");
		$contact2->setLastname("Seul");
		$em->persist($contact2);

		$account2 = new Account();
		$account2->setName("Mme Seul");
		$account2->addContact($contact2);
		$em->persist($account2);

		$account3 = new Account();
		$account3->setName("Famille Seul");
		$account3->addContact($contact);
		$account3->addContact($contact2);
		$em->persist($account3);

		$em->flush();

		$name = "Creation Ok";

		return $this
				->render('GuepeCrmBankBundle:Default:index.html.twig',
						array('name' => $name));
	}

	/**
	 * @Route("/CleanDB", name="CleanDB")
	 */

	public function CleanDBAction() {
		$em = $this->container->get('doctrine')->getEntityManager();

		$accounts = $this->getDoctrine()
				->getRepository('GuepeCrmBankBundle:Account')->findAll();
		if ($accounts) {
			foreach ($accounts as $account) {
				$em->remove($account);
			}
		}

		$contacts = $this->getDoctrine()
				->getRepository('GuepeCrmBankBundle:Contact')->findAll();
		if ($contacts) {
			foreach ($contacts as $contact) {
				$em->remove($contact);
			}
		}

		$bankproducts = $this->getDoctrine()
				->getRepository('GuepeCrmBankBundle:BankProduct')->findAll();
		if ($bankproducts) {
			foreach ($bankproducts as $bankproduct) {
				$em->remove($bankproduct);
			}
		}

		$em->flush();

		$name = "Clean UP Ok";

		return $this
				->render('GuepeCrmBankBundle:Default:index.html.twig',
						array('name' => $name));
	}

}
