<?php

namespace Guepe\CrmBankBundle\Controller;
use Guepe\CrmBankBundle\Entity\Contact;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerAware, Symfony\Component\HttpFoundation\RedirectResponse;

use Guepe\CrmBankBundle\Entity\Document;
use Guepe\CrmBankBundle\Form\DocumentForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/document")
 */

class DocumentController extends Controller {
	/**
	 * @Route("/", name="UploadDocument")
	 * @Template("GuepeCrmBankBundle:Document:upload.html.twig")
	 */
	public function uploadAction() {
		$document = new Document();
		$form = $this->container->get('form.factory')
				->create(new DocumentForm(), $document);

		if ($this->getRequest()->getMethod() === 'POST') {
			$form->bindRequest($this->getRequest());
			if ($form->isValid()) {
				$em = $this->getDoctrine()->getEntityManager();
				$em->persist($document);
				$em->flush();
				return $this
						->redirect(
								$this
										->generateUrl(
												'GuepeCrmBankBundle_homepage'));
			}
		}
		return array('form' => $form->createView());
	}

	/**
	 * @Route("/AddDocument/{contact_id}", name="AddDocument")
	 * @Template("GuepeCrmBankBundle:Document:upload.html.twig")
	 */
	public function uploadContactDocumentAction($contact_id) {
		$document = new Document();
		$form = $this->container->get('form.factory')
				->create(new DocumentForm(), $document);

		if ($this->getRequest()->getMethod() === 'POST') {
			$form->bindRequest($this->getRequest());
			if ($form->isValid()) {
				$em = $this->getDoctrine()->getEntityManager();
				$contact = $em->find('GuepeCrmBankBundle:Contact', $contact_id);
				$contact->addDocument($document);
				$em->persist($contact);
				$em->persist($document);
				$em->flush();
				return $this
						->redirect(
								$this
										->generateUrl('ShowContact',
												array('id' => $contact->getId())));
			}
		}
		return array('form' => $form->createView());
	}
}

