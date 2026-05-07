<?php

namespace App\Controller;

use App\Entity\FieldEdit;
use App\Entity\OnboardingSession;
use App\Entity\PrescriberInvitation;
use App\Entity\User;
use App\Form\ChangePasswordType;
use App\Form\PortalContactType;
use App\Repository\OnboardingSessionRepository;
use App\Repository\PrescriberInvitationRepository;
use App\Service\OnboardingService;
use App\Service\PlanilifeDashboardBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/portal')]
#[IsGranted('ROLE_CLIENT')]
class PortalController extends AbstractController
{
    #[Route('', name: 'app_portal_dashboard', methods: ['GET'])]
    public function dashboard(
        OnboardingSessionRepository $sessionRepository,
        PlanilifeDashboardBuilder $dashboardBuilder,
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $contact = $user->getContact();
        $session = $sessionRepository->findLatestByUser($user);

        return $this->render('portal/dashboard.html.twig', [
            'portal_user' => $user,
            'contact' => $contact,
            'dashboard' => $dashboardBuilder->build($session),
        ]);
    }

    #[Route('/dashboard/champ', name: 'app_portal_dashboard_field_update', methods: ['POST'])]
    public function updateDashboardField(
        Request $request,
        OnboardingSessionRepository $sessionRepository,
        EntityManagerInterface $entityManager,
        OnboardingService $onboardingService,
        PlanilifeDashboardBuilder $dashboardBuilder,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        if (!$this->isCsrfTokenValid('portal-dashboard-field-'.$user->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $fieldPath = (string) $request->request->get('field_path');
        if (!$dashboardBuilder->isAllowedFieldPath($fieldPath)) {
            throw $this->createAccessDeniedException('Champ non autorise.');
        }

        $session = $this->findOrCreateDashboardSession($user, $sessionRepository, $entityManager);
        $value = $dashboardBuilder->normalizeSubmittedField($fieldPath, (string) $request->request->get('value', ''));

        $onboardingService->updateSessionField(
            $session,
            $fieldPath,
            $value,
            FieldEdit::SOURCE_UPDATED,
            $user,
            'Edition inline dashboard Planilife',
            FieldEdit::ROLE_CLIENT,
        );

        if ($fieldPath === 'client.email' && is_string($value) && $value !== '') {
            $user->setEmail($value);
            $entityManager->flush();
        }

        $this->addFlash('success', 'Champ mis a jour dans votre dashboard.');

        return $this->redirectToRoute('app_portal_dashboard', [
            '_fragment' => $dashboardBuilder->getTabForField($fieldPath),
        ]);
    }

    #[Route('/dashboard/timeline', name: 'app_portal_dashboard_timeline_update', methods: ['POST'])]
    public function updateDashboardTimeline(
        Request $request,
        OnboardingSessionRepository $sessionRepository,
        EntityManagerInterface $entityManager,
        OnboardingService $onboardingService,
        PlanilifeDashboardBuilder $dashboardBuilder,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        if (!$this->isCsrfTokenValid('portal-dashboard-timeline-'.$user->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $session = $this->findOrCreateDashboardSession($user, $sessionRepository, $entityManager);
        $currentDashboard = $dashboardBuilder->build($session);
        $events = $dashboardBuilder->updateTimelineEvents(
            $currentDashboard['timeline'],
            (string) $request->request->get('timeline_action', 'add'),
            $request->request->all(),
        );

        $onboardingService->updateSessionField(
            $session,
            PlanilifeDashboardBuilder::TIMELINE_PATH,
            $events,
            FieldEdit::SOURCE_UPDATED,
            $user,
            'Ajustement timeline dashboard Planilife',
            FieldEdit::ROLE_CLIENT,
        );
        $onboardingService->updateSessionField(
            $session,
            'etapes.etapes',
            $dashboardBuilder->summarizeTimelineForRequiredField($events),
            FieldEdit::SOURCE_UPDATED,
            $user,
            'Synchronisation des etapes depuis la timeline dashboard',
            FieldEdit::ROLE_CLIENT,
        );

        $this->addFlash('success', 'Timeline mise a jour.');

        return $this->redirectToRoute('app_portal_dashboard', [
            '_fragment' => 'timing',
        ]);
    }

    #[Route('/consentement', name: 'app_portal_consent', methods: ['GET', 'POST'])]
    public function consent(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('portal-consent-'.$user->getId(), (string) $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Jeton CSRF invalide.');
            }

            if ($request->request->get('accept_consent') !== '1') {
                $this->addFlash('error', 'Vous devez valider le consentement pour poursuivre l entretien.');

                return $this->redirectToRoute('app_portal_consent');
            }

            $user->acceptConsent('planilife-beta-2026-05');
            $entityManager->flush();
            $this->addFlash('success', 'Consentement enregistre.');

            return $this->redirectToRoute('app_onboarding_new');
        }

        return $this->render('portal/consent.html.twig', [
            'portal_user' => $user,
        ]);
    }

    #[Route('/donnees', name: 'app_portal_data', methods: ['GET'])]
    public function data(OnboardingSessionRepository $sessionRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('portal/data.html.twig', [
            'portal_user' => $user,
            'contact' => $user->getContact(),
            'sessions' => $sessionRepository->findRecentSessions($user),
        ]);
    }

    #[Route('/donnees/export', name: 'app_portal_data_export', methods: ['GET'])]
    public function exportData(OnboardingSessionRepository $sessionRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $contact = $user->getContact();
        $sessions = $sessionRepository->findRecentSessions($user);

        $user->markDataExportRequested();
        $entityManager->flush();

        $payload = [
            'generated_at' => (new \DateTimeImmutable())->format(DATE_ATOM),
            'user' => [
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'email_verified_at' => $user->getEmailVerifiedAt()?->format(DATE_ATOM),
                'consent_accepted_at' => $user->getConsentAcceptedAt()?->format(DATE_ATOM),
                'consent_version' => $user->getConsentVersion(),
            ],
            'contact' => $contact !== null ? [
                'firstname' => $contact->getFirstname(),
                'lastname' => $contact->getLastname(),
                'email' => $contact->getEmail(),
                'phone' => $contact->getPhone(),
                'gsm' => $contact->getGsm(),
                'address' => trim(implode(' ', array_filter([
                    $contact->getStreetNum(),
                    $contact->getZip(),
                    $contact->getCity(),
                    $contact->getCountry(),
                ]))),
                'birthdate' => $contact->getBirthdate()?->format('Y-m-d'),
            ] : null,
            'onboarding_sessions' => array_map(static fn ($session): array => [
                'id' => $session->getId(),
                'status' => $session->getStatus(),
                'phase' => $session->getPhase(),
                'created_at' => $session->getCreatedAt()->format(DATE_ATOM),
                'updated_at' => $session->getUpdatedAt()->format(DATE_ATOM),
                'extracted_data' => $session->getExtractedData(),
                'messages' => $session->getMessages(),
            ], $sessions),
        ];

        $response = new JsonResponse($payload);
        $response->headers->set('Content-Disposition', 'attachment; filename="planilife-donnees.json"');

        return $response;
    }

    #[Route('/donnees/suppression', name: 'app_portal_data_delete_request', methods: ['POST'])]
    public function requestDataDeletion(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$this->isCsrfTokenValid('portal-delete-data-'.$user->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $user->requestDataDeletion();
        $entityManager->flush();
        $this->addFlash('success', 'Demande de suppression enregistree. Elle doit etre traitee sous 48h.');

        return $this->redirectToRoute('app_portal_data');
    }

    #[Route('/dossier', name: 'app_portal_contact_edit', methods: ['GET', 'POST'])]
    public function editOwnContact(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $contact = $user->getContact();
        if ($contact === null) {
            throw $this->createAccessDeniedException('Aucun contact lie a ce compte client.');
        }

        $form = $this->createForm(PortalContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setEmail($contact->getEmail());
            $entityManager->flush();
            $this->addFlash('success', 'Votre dossier a ete mis a jour.');

            return $this->redirectToRoute('app_portal_dashboard');
        }

        return $this->render('portal/contact_form.html.twig', [
            'form' => $form,
            'contact' => $contact,
        ]);
    }

    #[Route('/mot-de-passe', name: 'app_portal_password', methods: ['GET', 'POST'])]
    public function changePassword(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = (string) $form->get('plainPassword')->getData();
            $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
            $entityManager->flush();
            $this->addFlash('success', 'Votre mot de passe a ete mis a jour.');

            return $this->redirectToRoute('app_portal_dashboard');
        }

        return $this->render('portal/password.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/partage', name: 'app_portal_prescriber_share', methods: ['GET', 'POST'])]
    public function prescriberShare(
        Request $request,
        OnboardingSessionRepository $sessionRepository,
        PrescriberInvitationRepository $invitationRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        /** @var User $user */
        $user    = $this->getUser();
        $session = $sessionRepository->findLatestByUser($user);

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('portal-prescriber-share-'.$user->getId(), (string) $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Jeton CSRF invalide.');
            }

            $role   = (string) $request->request->get('prescriber_role', '');
            $blocks = $request->request->all('authorized_blocks');
            $note   = (string) $request->request->get('note', '');

            if (!array_key_exists($role, PrescriberInvitation::ALL_ROLES) || empty($blocks)) {
                $this->addFlash('error', 'Veuillez choisir un role et au moins un bloc a partager.');

                return $this->redirectToRoute('app_portal_prescriber_share');
            }

            $validBlocks = array_values(array_intersect($blocks, PrescriberInvitation::ALL_BLOCKS));
            $session     = $this->findOrCreateDashboardSession($user, $sessionRepository, $entityManager);

            $invitation = new PrescriberInvitation($session, $role, $validBlocks);
            if ($note !== '') {
                $invitation->setNote($note);
            }
            $entityManager->persist($invitation);
            $entityManager->flush();

            $this->addFlash('success', 'Invitation creee. Copiez le lien et transmettez-le a votre prescripteur.');

            return $this->redirectToRoute('app_portal_prescriber_share');
        }

        return $this->render('portal/partage.html.twig', [
            'portal_user' => $user,
            'session'     => $session,
            'invitations' => $session ? $invitationRepository->findBySession($session) : [],
            'all_roles'   => PrescriberInvitation::ALL_ROLES,
            'all_blocks'  => PrescriberInvitation::ALL_BLOCKS,
        ]);
    }

    #[Route('/partage/{id}/revoquer', name: 'app_portal_prescriber_revoke', methods: ['POST'])]
    public function revokeInvitation(
        PrescriberInvitation $invitation,
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        if ($invitation->getSession()->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid('revoke-invitation-'.$invitation->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $invitation->revoke();
        $entityManager->flush();
        $this->addFlash('success', 'Invitation revoquee.');

        return $this->redirectToRoute('app_portal_prescriber_share');
    }

    private function findOrCreateDashboardSession(
        User $user,
        OnboardingSessionRepository $sessionRepository,
        EntityManagerInterface $entityManager,
    ): OnboardingSession {
        $session = $sessionRepository->findLatestByUser($user);
        if ($session instanceof OnboardingSession) {
            return $session;
        }

        $session = new OnboardingSession($user);
        $session->setContact($user->getContact());
        $entityManager->persist($session);
        $entityManager->flush();

        return $session;
    }
}
