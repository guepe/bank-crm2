<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordType;
use App\Form\PortalContactType;
use Doctrine\ORM\EntityManagerInterface;
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
