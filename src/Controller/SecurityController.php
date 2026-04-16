<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Entity\User;
use App\Form\RegistrationType;
use App\Repository\OnboardingSessionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class SecurityController extends AbstractController
{
    use TargetPathTrait;

    #[Route('/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils, OnboardingSessionRepository $onboardingSessionRepository): Response
    {
        if ($this->getUser()) {
            if ($this->isGranted('ROLE_CLIENT') && !$this->isGranted('ROLE_USER')) {
                /** @var User $user */
                $user = $this->getUser();
                $inProgressSession = $onboardingSessionRepository->findInProgressByUser($user);

                if ($inProgressSession !== null) {
                    return $this->redirectToRoute('app_onboarding_chat', ['id' => $inProgressSession->getId()]);
                }

                return $this->redirectToRoute('app_portal_dashboard');
            }

            return $this->redirectToRoute('app_dashboard');
        }

        return $this->render('security/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        UserAuthenticatorInterface $userAuthenticator,
        \App\Security\LoginFormAuthenticator $loginFormAuthenticator,
        OnboardingSessionRepository $onboardingSessionRepository,
    ): Response {
        if ($this->getUser()) {
            if ($this->isGranted('ROLE_CLIENT') && !$this->isGranted('ROLE_USER')) {
                /** @var User $user */
                $user = $this->getUser();
                $inProgressSession = $onboardingSessionRepository->findInProgressByUser($user);

                if ($inProgressSession !== null) {
                    return $this->redirectToRoute('app_onboarding_chat', ['id' => $inProgressSession->getId()]);
                }

                return $this->redirectToRoute('app_portal_dashboard');
            }

            return $this->redirectToRoute('app_dashboard');
        }

        $user = new User();
        $user->setRoles(['ROLE_CLIENT']);
        $user->setEnabled(true);

        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $existingUsername = $entityManager->getRepository(User::class)->findOneBy([
                'username' => $user->getUsername(),
            ]);

            if ($existingUsername instanceof User) {
                $form->get('username')->addError(new FormError('Ce nom d utilisateur est deja utilise.'));
            }

            $existingEmail = $user->getEmail() !== null
                ? $entityManager->getRepository(User::class)->findOneBy(['email' => $user->getEmail()])
                : null;

            if ($existingEmail instanceof User) {
                $form->get('email')->addError(new FormError('Cette adresse email est deja utilisee.'));
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $contact = new Contact();
            $contact->setFirstname((string) $form->get('firstname')->getData());
            $contact->setLastname((string) $form->get('lastname')->getData());
            $contact->setEmail($user->getEmail());

            $user->setContact($contact);
            $user->setPassword($passwordHasher->hashPassword($user, (string) $form->get('plainPassword')->getData()));

            $entityManager->persist($contact);
            $entityManager->persist($user);
            $entityManager->flush();

            $request->getSession()->set('_security.main.target_path', $this->generateUrl('app_onboarding_new'));
            $this->addFlash('success', 'Compte cree. Commencons votre onboarding.');

            return $userAuthenticator->authenticateUser($user, $loginFormAuthenticator, $request);
        }

        return $this->render('security/register.html.twig', [
            'form' => $form,
            'page_title' => 'Créer un compte',
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): never
    {
        throw new \LogicException('This method is intercepted by the firewall logout handler.');
    }
}
