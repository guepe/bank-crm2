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
     * @Route("/AddContactDocument/{id}", name="AddContactDocument")
     * @Template("GuepeCrmBankBundle:Document:upload.html.twig")
     */
    public function uploadContactDocument($id) {
        $document = new Document();
        $form = $this->container->get('form.factory')
            ->create(new DocumentForm(), $document);

        if ($this->getRequest()->getMethod() === 'POST') {
            $form->bindRequest($this->getRequest());
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();
                $contact = $em->find('GuepeCrmBankBundle:Contact', $id);
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

	/**
	 * @Route("/AddAccountDocument/{id}", name="AddAccountDocument")
    * @Template("GuepeCrmBankBundle:Document:upload.html.twig")
	 */
	public function uploadContactDocumentAction($id) {
		$document = new Document();
		$form = $this->container->get('form.factory')
				->create(new DocumentForm(), $document);

		if ($this->getRequest()->getMethod() === 'POST') {
			$form->bindRequest($this->getRequest());
			if ($form->isValid()) {
				$em = $this->getDoctrine()->getEntityManager();
				$account = $em->find('GuepeCrmBankBundle:Account', $id);
				$account->addDocument($document);
				$em->persist($account);
				$em->persist($document);
				$em->flush();
				return $this
						->redirect(
								$this
										->generateUrl('ShowAccount',
												array('id' => $account->getId())));
			}
		}
		return array('form' => $form->createView());
	}
}

