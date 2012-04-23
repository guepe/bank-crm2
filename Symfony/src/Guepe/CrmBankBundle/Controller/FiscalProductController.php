<?php

namespace Guepe\CrmBankBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Guepe\CrmBankBundle\Entity\FiscalProduct;
use Guepe\CrmBankBundle\Form\FiscalProductForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * FiscalProduct controller.
 *
 */
/**	
 * @Route("/fiscalproduct/")
 */

class FiscalProductController extends Controller {
	/**
	 * Lists all FiscalProduct entities.
	 * @Template()
	 *
	 */
	public function indexAction() {
		$em = $this->getDoctrine()->getEntityManager();

		$entities = $em->getRepository('GuepeCrmBankBundle:FiscalProduct')
				->findAll();

		return array('entities' => $entities);
	}

	/**
	 * Creates a new FiscalProduct entity.
	 * 
	 */
	/**	
	 * @Route("create/{account_id}", name="fiscalproduct_create")
	 * @Template()
	 */
	public function createAction($account_id) {
		$em = $this->getDoctrine()->getEntityManager();
		$entity = new FiscalProduct();

		$request = $this->getRequest();
		$form = $this->createForm(new FiscalProductForm(), $entity);
		if ($request->getMethod() == 'POST') {
			$form->bindRequest($request);
			if ($form->isValid()) {
				$account = $em->find('GuepeCrmBankBundle:Account', $account_id);
				$account->addFiscalProduct($entity);
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
