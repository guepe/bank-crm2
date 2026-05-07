<?php

namespace App\Controller;

use App\Entity\FieldEdit;
use App\Repository\PrescriberInvitationRepository;
use App\Service\OnboardingService;
use App\Service\PlanilifeDashboardBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/prescripteur')]
class PrescriberController extends AbstractController
{
    #[Route('/{token}', name: 'app_prescriber_view', methods: ['GET'])]
    public function view(
        string $token,
        PrescriberInvitationRepository $invitationRepository,
        PlanilifeDashboardBuilder $dashboardBuilder,
    ): Response {
        $invitation = $invitationRepository->findActiveByToken($token);
        if ($invitation === null) {
            return $this->render('prescriber/expired.html.twig', [], new Response('', Response::HTTP_GONE));
        }

        $session   = $invitation->getSession();
        $dashboard = $dashboardBuilder->build($session);
        $tabs      = array_filter(
            $dashboard['tabs'],
            static fn(array $tab): bool => in_array($tab['key'], $invitation->getAuthorizedBlocks(), true),
        );

        return $this->render('prescriber/view.html.twig', [
            'invitation' => $invitation,
            'tabs'       => array_values($tabs),
            'session'    => $session,
        ]);
    }

    #[Route('/{token}/corriger', name: 'app_prescriber_correct', methods: ['POST'])]
    public function correct(
        string $token,
        Request $request,
        PrescriberInvitationRepository $invitationRepository,
        PlanilifeDashboardBuilder $dashboardBuilder,
        OnboardingService $onboardingService,
        EntityManagerInterface $entityManager,
    ): Response {
        $invitation = $invitationRepository->findActiveByToken($token);
        if ($invitation === null) {
            return $this->render('prescriber/expired.html.twig', [], new Response('', Response::HTTP_GONE));
        }

        if (!$this->isCsrfTokenValid('prescriber-correct-'.$token, (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Jeton de securite invalide.');

            return $this->redirectToRoute('app_prescriber_view', ['token' => $token]);
        }

        $fieldPath = (string) $request->request->get('field_path', '');
        $value     = (string) $request->request->get('value', '');
        $reason    = trim((string) $request->request->get('reason', ''));

        // Verify the field belongs to an authorized block
        if (!$dashboardBuilder->isAllowedFieldPath($fieldPath)) {
            $this->addFlash('error', 'Champ non autorise.');

            return $this->redirectToRoute('app_prescriber_view', ['token' => $token]);
        }

        $tabKey = $dashboardBuilder->getTabForField($fieldPath);
        if (!in_array($tabKey, $invitation->getAuthorizedBlocks(), true)) {
            $this->addFlash('error', 'Ce champ ne fait pas partie des blocs partages.');

            return $this->redirectToRoute('app_prescriber_view', ['token' => $token]);
        }

        $session       = $invitation->getSession();
        $normalizedVal = $dashboardBuilder->normalizeSubmittedField($fieldPath, $value);

        $onboardingService->updateSessionField(
            $session,
            $fieldPath,
            $normalizedVal,
            FieldEdit::SOURCE_CORRECTED,
            null,
            $reason !== '' ? $reason : sprintf('Correction par prescripteur (%s)', $invitation->getPrescriberRoleLabel()),
            FieldEdit::ROLE_PRESCRIBER,
        );

        $invitation->markAccessed();
        $invitation->incrementCorrectionCount();
        $entityManager->flush();

        $this->addFlash('success', 'Correction enregistree.');

        return $this->redirectToRoute('app_prescriber_view', ['token' => $token]);
    }
}
