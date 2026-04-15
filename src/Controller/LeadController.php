<?php

namespace App\Controller;

use App\Entity\Lead;
use App\Form\LeadType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/leads')]
#[IsGranted('ROLE_USER')]
class LeadController extends AbstractController
{
    #[Route('', name: 'app_lead_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $search = trim((string) $request->query->get('q', ''));
        $queryBuilder = $entityManager->getRepository(Lead::class)->createQueryBuilder('l');

        if ($search !== '') {
            $queryBuilder
                ->andWhere('l.name LIKE :search OR l.city LIKE :search OR l.type LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        return $this->render('lead/index.html.twig', [
            'leads' => $queryBuilder
                ->orderBy('l.name', 'ASC')
                ->getQuery()
                ->getResult(),
            'search' => $search,
        ]);
    }

    #[Route('/new', name: 'app_lead_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $lead = new Lead();
        $form = $this->createForm(LeadType::class, $lead);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($lead);
            $entityManager->flush();
            $this->addFlash('success', 'Lead cree.');

            return $this->redirectToRoute('app_lead_show', ['id' => $lead->getId()]);
        }

        return $this->render('lead/form.html.twig', [
            'lead' => $lead,
            'form' => $form,
            'page_title' => 'Nouveau lead',
        ]);
    }

    #[Route('/{id}', name: 'app_lead_show', methods: ['GET'])]
    public function show(Lead $lead): Response
    {
        return $this->render('lead/show.html.twig', [
            'lead' => $lead,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_lead_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Lead $lead, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LeadType::class, $lead);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Lead mis a jour.');

            return $this->redirectToRoute('app_lead_show', ['id' => $lead->getId()]);
        }

        return $this->render('lead/form.html.twig', [
            'lead' => $lead,
            'form' => $form,
            'page_title' => 'Modifier le lead',
        ]);
    }

    #[Route('/{id}/delete', name: 'app_lead_delete', methods: ['POST'])]
    public function delete(Request $request, Lead $lead, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete-lead-'.$lead->getId(), (string) $request->request->get('_token'))) {
            $entityManager->remove($lead);
            $entityManager->flush();
            $this->addFlash('success', 'Lead supprime.');
        }

        return $this->redirectToRoute('app_lead_index');
    }
}
