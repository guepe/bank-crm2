<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Repository\PortalAccessLinkRepository;
use App\Service\PortalAccessManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PortalAccessController extends AbstractController
{
    #[Route('/contacts/{id}/portal-access/send', name: 'app_contact_portal_access_send', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function send(
        Request $request,
        Contact $contact,
        PortalAccessManager $portalAccessManager
    ): Response {
        if (!$this->isCsrfTokenValid('send-portal-access-'.$contact->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        try {
            $portalAccessManager->sendAccessEmail($contact);
            $this->addFlash('success', sprintf('Acces portail envoye a %s.', $contact->getEmail()));
        } catch (\InvalidArgumentException $exception) {
            $this->addFlash('error', $exception->getMessage());
        }

        return $this->redirectToRoute('app_contact_show', ['id' => $contact->getId()]);
    }

    #[Route('/portal/access/{token}', name: 'app_portal_access', methods: ['GET', 'POST'])]
    public function access(
        string $token,
        Request $request,
        PortalAccessLinkRepository $portalAccessLinkRepository,
        FormFactoryInterface $formFactory,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
    ): Response {
        $accessLink = $portalAccessLinkRepository->findActiveByToken($token);
        if ($accessLink === null) {
            throw $this->createNotFoundException('Ce lien d acces est invalide, expire ou deja utilise.');
        }

        $form = $formFactory->create(\App\Form\ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = (string) $form->get('plainPassword')->getData();
            $user = $accessLink->getUser();
            $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
            $accessLink->markUsed();
            $entityManager->flush();

            $this->addFlash('success', 'Votre acces est active. Vous pouvez maintenant vous connecter au portail.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('portal/access_link.html.twig', [
            'access_link' => $accessLink,
            'summary' => $accessLink->getSummarySnapshot(),
            'form' => $form,
        ]);
    }
}
