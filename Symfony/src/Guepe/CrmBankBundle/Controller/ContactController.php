<?php

namespace Guepe\CrmBankBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerAware, Symfony\Component\HttpFoundation\RedirectResponse;

use Guepe\CrmBankBundle\Entity\Contact;
use Guepe\CrmBankBundle\Form\ContactForm;
use Guepe\CrmBankBundle\Form\ContactSearchForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/contact")
 */

class ContactController extends Controller {

	/**
	 * Route("/{account_id}", name="SelectContact")
	 * @Template()
	 */
	public function indexAction($account_id = null) {

		$contacts = $this->getDoctrine()
				->getRepository('GuepeCrmBankBundle:Contact')
				->findBy(array(), array('lastname' => 'asc'));

		return array('contacts' => $contacts, 'account_id' => $account_id);

	}

	/**
	 * @Route("/edit/{id}", name="EditContact")
	 * @Route("/add", name="AddContact")
	 * @Template()
	 */

	public function editAction($id = null) {
		$message = "";
		$em = $this->container->get('doctrine')->getEntityManager();
		if (isset($id)) {
			$contact = $em->find('GuepeCrmBankBundle:Contact', $id);
			if (!$contact) {
				$message = 'Aucun contact trouv�';
			}
		} else {
			$contact = new Contact();
		}
		$form = $this->container->get('form.factory')
				->create(new ContactForm(), $contact);
		$request = $this->container->get('request');
		if ($request->getMethod() == 'POST') {
			$form->bindRequest($request);

			if ($form->isValid()) {
				$em->persist($contact);
				$em->flush();
				if (isset($id)) {
					$message = 'Contact modifié avec succès !';
				} else {
					$message = 'Contact ajouté avec succès !';
				}
				return $this
						->redirect(
								$this
										->generateUrl('ShowContact',
												array('id' => $contact->getId())));
			}
		}
		return array('form' => $form->createView(), 'message' => $message,);
	}

	/**
	 * @Route("/show/{id}", name="ShowContact")
	 * @Template()
	 */

	public function showAction($id = null) {
		$message = "";
		$entity = null;
		$em = $this->container->get('doctrine')->getEntityManager();
		if (isset($id)) {
			$entity = $em->find('GuepeCrmBankBundle:Contact', $id);
			if (!$entity) {
				$message = 'Aucun contact trouvés';
			}
		}
		return array('contact' => $entity, 'message' => $message,);
	}

	public function listAction($account_id = null) {
		$em = $this->container->get('doctrine')->getEntityManager();
		$contacts = $em->getRepository('GuepeCrmBankBundle:Contact')
				->findBy(array(), array('lastname' => 'asc'));

		$form = $this->container->get('form.factory')
				->create(new ContactSearchForm());

		return $this->container->get('templating')
				->renderResponse(
						'GuepeCrmBankBundle:Contact:search.html.twig',
						array('contacts' => $contacts,
								'account_id' => $account_id,
								'form' => $form->createView()));
	}

	/**	
	 * @Route("/search/{account_id}", name="SearchContact",defaults={"account_id" = null})
	 * @Route("/{account_id}", name="SelectContact")
	 * @Route("/",name="AllContacts",defaults={"account_id" = null})
	 */
	public function searchAction($account_id = null) {
		$request = $this->container->get('request');

		$message = '';

		if ($request->isXmlHttpRequest()) {
			$lastname = '';
			$lastname = $request->request->get('lastname');
			$em = $this->container->get('doctrine')->getEntityManager();
			if ($lastname != '') {
				$qb = $em->createQueryBuilder();
				$qb->select('a')->from('GuepeCrmBankBundle:Contact', 'a')
						->where("a.lastname LIKE :lastname")
						->orderBy('a.lastname', 'ASC')
						->setParameter('lastname', '%' . $lastname . '%');

				$query = $qb->getQuery();
				$contacts = $query->getResult();
			} else {
				$contacts = $em->getRepository('GuepeCrmBankBundle:Contact')
						->findBy(array(), array('lastname' => 'asc'));

			}

			return $this->container->get('templating')
					->renderResponse(
							'GuepeCrmBankBundle:Contact:list.html.twig',
							array('contacts' => $contacts,
									'account_id' => $account_id,
									'message' => $message));
		} else {
			return $this->listAction($account_id);
		}
	}

}
