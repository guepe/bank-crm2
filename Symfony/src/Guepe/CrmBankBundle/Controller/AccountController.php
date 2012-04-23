<?php

namespace Guepe\CrmBankBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerAware, Symfony\Component\HttpFoundation\RedirectResponse;

use Guepe\CrmBankBundle\Entity\Contact;
use Guepe\CrmBankBundle\Entity\Account;
use Guepe\CrmBankBundle\Form\AccountForm;
use Guepe\CrmBankBundle\Form\AccountSearchForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/account")
 */

class AccountController extends Controller {
	/**
	 * @Route("/", name="AllAccounts")
	 * @Template("GuepeCrmBankBundle:Account:search.html.twig")
	 */

	public function indexAction() {
		$accounts = $this->getDoctrine()
				->getRepository('GuepeCrmBankBundle:Account')
				->findBy(array(), array('name' => 'asc'));

		return array('accounts' => $accounts);

	}

	/**
	 * @Route("/show/{id}", name="ShowAccount")
	 * @Template()
	 */

	public function showAction($id = null) {
		$message = "";
		$account = null;
		$em = $this->container->get('doctrine')->getEntityManager();
		if (isset($id)) {
			$account = $em->find('GuepeCrmBankBundle:Account', $id);
			if (!$account) {
				$message = 'Aucun acteur trouvé';
			}
		}
		return array('account' => $account, 'message' => $message,);
	}

	/**
	 * @Route("/addcontact/{id}/{contact_id}", name="AddContactToAccount")
	 */

	public function addcontactAction($id = null, $contact_id = null) {
		$message = "";
		$em = $this->container->get('doctrine')->getEntityManager();
		if (isset($id)) {
			$account = $em->find('GuepeCrmBankBundle:Account', $id);
			if (!$account) {
				$message = 'Aucun account trouvé';
			}
		}
		if (isset($contact_id)) {
			$contact = $em->find('GuepeCrmBankBundle:Contact', $contact_id);
			if (!$contact) {
				$message = 'Aucun contact trouvé';
			} else {
				$account->addContact($contact);
				$em->persist($contact);
				$em->flush();

				return $this
						->redirect(
								$this
										->generateUrl('ShowAccount',
												array('id' => $account->getId())));

			}
		}
		return $this->container->get('templating')
				->renderResponse('GuepeCrmBankBundle:Account:show.html.twig',
						array('account' => $account, 'message' => $message,));
	}

	/**
	 * @Route("/deletecontact/{id}/{contact_id}", name="DeleteContact")
	 */

	public function deletecontactAction($id = null, $contact_id = null) {
		$message = "";
		$em = $this->container->get('doctrine')->getEntityManager();
		if (isset($id)) {
			$account = $em->find('GuepeCrmBankBundle:Account', $id);
			if (!$account) {
				$message = 'Aucun account trouvé';
			}
		}
		if (isset($contact_id)) {
			$contact = $em->find('GuepeCrmBankBundle:Contact', $contact_id);
			if (!$contact) {
				$message = 'Aucun contact trouvé';
			} else {
				$em->remove($contact);
				$em->flush();

				return $this
						->redirect(
								$this
										->generateUrl('ShowAccount',
												array('id' => $account->getId())));
			}
		}
		return $this->container->get('templating')
				->renderResponse('GuepeCrmBankBundle:Account:show.html.twig',
						array('account' => $account, 'message' => $message,));
	}

	/**
	 * @Route("/deleteproduct/{id}/{product_id}", name="DeleteProduct")
	 */

	public function deleteProductAction($id = null, $product_id = null) {
		$message = "";
		$em = $this->container->get('doctrine')->getEntityManager();
		if (isset($id)) {
			$account = $em->find('GuepeCrmBankBundle:Account', $id);
			if (!$account) {
				$message = 'Aucun account trouvé';
			}
		}
		if (isset($product_id)) {
			$Product = $em->find('GuepeCrmBankBundle:MetaProduct', $product_id);
			if (!$Product) {
				$message = 'Aucun Produit trouvé';
			} else {
				$em->remove($Product);
				$em->flush();

				return $this
						->redirect(
								$this
										->generateUrl('ShowAccount',
												array('id' => $account->getId())));

			}
		}

		return $this->container->get('templating')
				->renderResponse('GuepeCrmBankBundle:Account:show.html.twig',
						array('account' => $account, 'message' => $message,));
	}

	/**
	 * @Route("/add", name="AddAccount")
	 * @Route("/edit/{id}", name="EditAccount")
	 * @Template()
	 */

	public function editAction($id = null) {
		$message = "";
		$em = $this->container->get('doctrine')->getEntityManager();
		if (isset($id)) {
			$account = $em->find('GuepeCrmBankBundle:Account', $id);
			if (!$account) {
				$message = 'Aucun acteur trouvé';
			}
		} else {
			$account = new Account();
		}

		$form = $this->container->get('form.factory')
				->create(new AccountForm(), $account);
		$request = $this->container->get('request');

		if ($request->getMethod() == 'POST') {
			$form->bindRequest($request);

			if ($form->isValid()) {
				$em->persist($account);
				$em->flush();
				if (isset($id)) {
					$message = 'Account modifié avec succès !';
				} else {
					$message = 'Account ajouté avec succès !';
				}
				return $this
						->redirect(
								$this
										->generateUrl('ShowAccount',
												array('id' => $account->getId())));

			}
		}

		return array('form' => $form->createView(), 'message' => $message,
				'account' => $account);
	}

	public function listAction() {
		$em = $this->container->get('doctrine')->getEntityManager();
		$accounts = $em->getRepository('GuepeCrmBankBundle:Account')
				->findBy(array(), array('name' => 'asc'));

		$form = $this->container->get('form.factory')
				->create(new AccountSearchForm());

		return $this->container->get('templating')
				->renderResponse(
						'GuepeCrmBankBundle:Account:search.html.twig',
						array('accounts' => $accounts,
								'form' => $form->createView()));
	}

	/**	
	 * @Route("/search", name="SearchAccount")
	 * @Route("/", name="AllAccounts")
	 */

	public function searchAction() {
		$request = $this->container->get('request');

		$message = '';

		if ($request->isXmlHttpRequest()) {
			$motcle = '';
			$motcle = $request->request->get('motcle');
			$message = $motcle;

			$em = $this->container->get('doctrine')->getEntityManager();

			if ($motcle != '') {
				$qb = $em->createQueryBuilder();
				$qb->select('a')->from('GuepeCrmBankBundle:Account', 'a')
						->where("a.name LIKE :motcle")
						->orderBy('a.name', 'ASC')
						->setParameter('motcle', '%' . $motcle . '%');

				$query = $qb->getQuery();
				$accounts = $query->getResult();
			} else {
				$accounts = $em->getRepository('GuepeCrmBankBundle:Account')
						->findBy(array(), array('name' => 'asc'));
			}

			return $this->container->get('templating')
					->renderResponse(
							'GuepeCrmBankBundle:Account:list.html.twig',
							array('accounts' => $accounts,
									'message' => $message));
		} else {
			return $this->listAction();
		}
	}

}
