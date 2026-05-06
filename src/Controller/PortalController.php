<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordType;
use App\Form\PortalContactType;
use App\Repository\OnboardingSessionRepository;
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
    public function dashboard(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $contact = $user->getContact();

        return $this->render('portal/dashboard.html.twig', [
            'portal_user' => $user,
            'contact' => $contact,
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
}
