<?php

namespace Guepe\CrmBankBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Guepe\CrmBankBundle\Entity\CreditProduct;
use Guepe\CrmBankBundle\Form\CreditProductForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * CreditProduct controller.
 *
 */
/**	
 * @Route("/creditproduct/")
 */

class CreditProductController extends Controller {
	/**
	 * Lists all BankProduct entities.
	 * @Template()
	 *
	 */
	public function indexAction() {
		$em = $this->getDoctrine()->getEntityManager();

		$entities = $em->getRepository('GuepeCrmBankBundle:CreditProduct')
				->findAll();

		return array('entities' => $entities);
	}

	/**
	 * Creates a new BankProduct entity.
	 * 
	 */
	/**	
	 * @Route("create/{account_id}", name="creditproduct_create")
	 * @Template()
	 */
	public function createAction($account_id) {
		$em = $this->getDoctrine()->getEntityManager();
		$entity = new CreditProduct();

		$request = $this->getRequest();
		$form = $this->createForm(new CreditProductForm(), $entity);
		if ($request->getMethod() == 'POST') {
			$form->bindRequest($request);
			if ($form->isValid()) {
				$account = $em->find('GuepeCrmBankBundle:Account', $account_id);
				$account->addCreditProduct($entity);
				$em->persist($entity);
				$em->persist($account);
				$em->flush();
				return $this
						->redirect(
								$this
										->generateUrl('ShowAccount',
												array('id' => $account_id)));
			}
		}
		return array('entity' => $entity, 'form' => $form->createView());
	}
}
