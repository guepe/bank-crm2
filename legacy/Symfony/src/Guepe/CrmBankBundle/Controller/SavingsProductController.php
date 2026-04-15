<?php

namespace Guepe\CrmBankBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Guepe\CrmBankBundle\Entity\SavingsProduct;
use Guepe\CrmBankBundle\Form\SavingsProductForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * SavingsProduct controller.
 *
 */
/**	
 * @Route("/savingsproduct/")
 */

class SavingsProductController extends Controller {
	/**
	 * Lists all SavingsProduct entities.
	 * @Template()
	 *
	 *
	public function indexAction() {
		$em = $this->getDoctrine()->getEntityManager();

		$entities = $em->getRepository('GuepeCrmBankBundle:SavingsProduct')
				->findAll();

		return array('entities' => $entities);
	}
*/
	/**
	 * Creates a new SavingsProduct entity.
	 * 
	 */
	/**	
	 * @Route("create/{account_id}", name="savingsproduct_create")
	 * @Template()
	 */
	public function createAction($account_id) {
		$em = $this->getDoctrine()->getEntityManager();
		$entity = new SavingsProduct();

		$request = $this->getRequest();
		$form = $this->createForm(new SavingsProductForm(), $entity);
		if ($request->getMethod() == 'POST') {
			$form->bindRequest($request);
			if ($form->isValid()) {
				$account = $em->find('GuepeCrmBankBundle:Account', $account_id);
				$account->addSavingsProduct($entity);
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
