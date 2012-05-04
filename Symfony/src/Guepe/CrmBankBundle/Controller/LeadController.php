<?php

namespace Guepe\CrmBankBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerAware, Symfony\Component\HttpFoundation\RedirectResponse;

use Guepe\CrmBankBundle\Entity\Lead;
use Guepe\CrmBankBundle\Form\LeadForm;
use Guepe\CrmBankBundle\Form\LeadSearchForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/lead")
 */

class LeadController extends Controller {
	/**
	 * @Route("/", name="AllLead")
	 * @Template("GuepeCrmBankBundle:Lead:search.html.twig")
	 */

	public function indexAction() {
		$lead = $this->getDoctrine()
				->getRepository('GuepeCrmBankBundle:Lead')
				->findBy(array(), array('name' => 'asc'));

		return array('lead' => $lead);

	}

	/**
	 * @Route("/show/{id}", name="ShowLead")
	 * @Template()
	 */

	public function showAction($id = null) {
		$message = "";
		$lead = null;
		$em = $this->container->get('doctrine')->getEntityManager();
		if (isset($id)) {
			$lead = $em->find('GuepeCrmBankBundle:Lead', $id);
			if (!$lead) {
				$message = 'Aucun acteur trouvé';
			}
		}
		return array('lead' => $lead, 'message' => $message,);
	}

	

	/**
	 * @Route("/add", name="AddLead")
	 * @Route("/edit/{id}", name="EditLead")
	 * @Template()
	 */

	public function editAction($id = null) {
		$message = "";
		$em = $this->container->get('doctrine')->getEntityManager();
		if (isset($id)) {
			$lead = $em->find('GuepeCrmBankBundle:Lead', $id);
			if (!$lead) {
				$message = 'Aucun acteur trouvé';
			}
		} else {
			$lead = new Lead();
		}

		$form = $this->container->get('form.factory')
				->create(new LeadForm(), $lead);
		$request = $this->container->get('request');

		if ($request->getMethod() == 'POST') {
			$form->bindRequest($request);

			if ($form->isValid()) {
				$em->persist($lead);
				$em->flush();
				if (isset($id)) {
					$message = 'Lead modifié avec succès !';
				} else {
					$message = 'Lead ajouté avec succès !';
				}
				return $this
						->redirect(
								$this
										->generateUrl('ShowLead',
												array('id' => $lead->getId())));

			}
		}

		return array('form' => $form->createView(), 'message' => $message,
				'lead' => $lead);
	}

	public function listAction() {
		$em = $this->container->get('doctrine')->getEntityManager();
		$lead = $em->getRepository('GuepeCrmBankBundle:Lead')
				->findBy(array(), array('name' => 'asc'));

		$form = $this->container->get('form.factory')
				->create(new LeadSearchForm());

		return $this->container->get('templating')
				->renderResponse(
						'GuepeCrmBankBundle:Lead:search.html.twig',
						array('leads' => $lead,
								'form' => $form->createView()));
	}

	/**	
	 * @Route("/search", name="SearchLead")
	 * @Route("/", name="AllLead")
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

				$qb->select('a')->from('GuepeCrmBankBundle:Lead', 'a')
						->where("a.name LIKE :motcle")
						->orderBy('a.name', 'ASC')
						->setParameter('motcle', '%' . $motcle . '%');

				$query = $qb->getQuery();
				$leads = $query->getResult();
			} else {
				$leads = $em->getRepository('GuepeCrmBankBundle:Lead')
						->findBy(array(), array('name' => 'asc'));
			}

			return $this->container->get('templating')
					->renderResponse(
							'GuepeCrmBankBundle:Lead:list.html.twig',
							array('leads' => $leads,
									'message' => $message));
		} else {
					return $this->listAction();
		}
	}
}