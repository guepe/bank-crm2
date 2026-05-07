<?php

namespace App\Controller;

use App\Entity\BetaPilotageIncident;
use App\Entity\User;
use App\Form\BetaPilotageIncidentType;
use App\Service\BetaPilotageDashboardBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/pilotage')]
#[IsGranted('ROLE_ADMIN')]
class PilotageController extends AbstractController
{
    #[Route('', name: 'app_pilotage_index', methods: ['GET'])]
    public function index(BetaPilotageDashboardBuilder $dashboardBuilder): Response
    {
        return $this->render('pilotage/index.html.twig', [
            'pilotage' => $dashboardBuilder->build(),
        ]);
    }

    #[Route('/incidents/new', name: 'app_pilotage_incident_new', methods: ['GET', 'POST'])]
    public function newIncident(Request $request, EntityManagerInterface $entityManager): Response
    {
        $incident = new BetaPilotageIncident();
        /** @var User|null $user */
        $user = $this->getUser();
        if ($user instanceof User) {
            $incident->setCreatedBy($user);
        }

        $form = $this->createForm(BetaPilotageIncidentType::class, $incident);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($incident->getStatus() === BetaPilotageIncident::STATUS_RESOLVED && $incident->getResolvedAt() === null) {
                $incident->resolve($incident->getResolutionNotes());
            }

            $entityManager->persist($incident);
            $entityManager->flush();
            $this->addFlash('success', 'Incident beta enregistre.');

            return $this->redirectToRoute('app_pilotage_index');
        }

        return $this->render('pilotage/incident_form.html.twig', [
            'form' => $form,
            'page_title' => 'Nouvel incident beta',
            'incident' => $incident,
        ]);
    }

    #[Route('/incidents/{id}/edit', name: 'app_pilotage_incident_edit', methods: ['GET', 'POST'])]
    public function editIncident(Request $request, BetaPilotageIncident $incident, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BetaPilotageIncidentType::class, $incident);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Incident beta mis a jour.');

            return $this->redirectToRoute('app_pilotage_index');
        }

        return $this->render('pilotage/incident_form.html.twig', [
            'form' => $form,
            'page_title' => 'Modifier incident beta',
            'incident' => $incident,
        ]);
    }

    #[Route('/incidents/{id}/resolve', name: 'app_pilotage_incident_resolve', methods: ['POST'])]
    public function resolveIncident(Request $request, BetaPilotageIncident $incident, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('resolve-incident-'.$incident->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $incident->resolve((string) $request->request->get('resolution_notes', 'Resolution depuis le pilotage beta.'));
        $entityManager->flush();
        $this->addFlash('success', 'Incident beta resolu.');

        return $this->redirectToRoute('app_pilotage_index');
    }
}
