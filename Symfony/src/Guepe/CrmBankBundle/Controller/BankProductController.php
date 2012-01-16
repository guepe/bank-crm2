<?php

namespace Guepe\CrmBankBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Guepe\CrmBankBundle\Entity\BankProduct;
use Guepe\CrmBankBundle\Form\BankProductForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * BankProduct controller.
 *
 */
 /**	
 * @Route("/bankproduct/")
 */

class BankProductController extends Controller
{
    /**
     * Lists all BankProduct entities.
     * @Template()
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entities = $em->getRepository('GuepeCrmBankBundle:BankProduct')->findAll();

        return array(
            'entities' => $entities
        );
    }

     /**
     * Creates a new BankProduct entity.
     * 
     */
    /**	
	* @Route("create/{account_id}", name="bankproduct_create")
	* @Template()
	*/
    public function createAction($account_id)
    {
    	$em = $this->getDoctrine()->getEntityManager();
    	$entity  = new BankProduct();
    	
        $request = $this->getRequest();
        $form    = $this->createForm(new BankProductForm(), $entity);
        if ($request->getMethod() == 'POST') {
	        $form->bindRequest($request);
	        if ($form->isValid()) {
	    		$account = $em->find('GuepeCrmBankBundle:Account', $account_id);
        		$account->addBankProduct($entity);
	        	$em->persist($entity);
				$em->persist($account);
				$em->flush();
	            return $this->redirect($this->generateUrl('ShowAccount', array('id' => $account_id)));
	        }
        }
        return  array(
            'entity' => $entity,
            'form'   => $form->createView()
        );
    }
}
