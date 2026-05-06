<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Entity\User;
use App\Entity\UserSecurityToken;
use App\Form\ChangePasswordType;
use App\Form\RegistrationType;
use App\Repository\OnboardingSessionRepository;
use App\Service\UserAccountSecurityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils, OnboardingSessionRepository $onboardingSessionRepository): Response
    {
        if ($this->getUser()) {
            if ($this->isGranted('ROLE_CLIENT') && !$this->isGranted('ROLE_USER')) {
                /** @var User $user */
                $user = $this->getUser();
                if (!$user->isEmailVerified()) {
                    return $this->redirectToRoute('app_email_verification_notice');
                }

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
        UserAccountSecurityManager $accountSecurityManager,
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

            $accountSecurityManager->sendEmailVerification($user);
            $this->addFlash('success', 'Compte cree. Verifiez votre e-mail pour activer l acces complet.');

            return $this->redirectToRoute('app_email_verification_notice');
        }

        return $this->render('security/register.html.twig', [
            'form' => $form,
            'page_title' => 'Créer un compte',
        ]);
    }

    #[Route('/verify-email', name: 'app_email_verification_notice', methods: ['GET'])]
    public function emailVerificationNotice(): Response
    {
        return $this->render('security/email_verification_notice.html.twig', [
            'page_title' => 'Verification e-mail',
        ]);
    }

    #[Route('/verify-email/{token}', name: 'app_verify_email', methods: ['GET'])]
    public function verifyEmail(string $token, UserAccountSecurityManager $accountSecurityManager): Response
    {
        $user = $accountSecurityManager->verifyEmailToken($token);
        if (!$user instanceof User) {
            $this->addFlash('error', 'Ce lien de verification est invalide ou expire.');

            return $this->redirectToRoute('app_email_verification_notice');
        }

        $this->addFlash('success', 'Adresse e-mail verifiee. Vous pouvez maintenant vous connecter.');

        return $this->redirectToRoute('app_login');
    }

    #[Route('/forgot-password', name: 'app_forgot_password', methods: ['GET', 'POST'])]
    public function forgotPassword(Request $request, UserAccountSecurityManager $accountSecurityManager): Response
    {
        $form = $this->createFormBuilder()
            ->add('identifier', TextType::class, [
                'label' => 'E-mail ou nom d utilisateur',
            ])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $accountSecurityManager->sendPasswordResetForIdentifier((string) $form->get('identifier')->getData());
            $this->addFlash('success', 'Si un compte correspond, un e-mail de reinitialisation a ete envoye.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/forgot_password.html.twig', [
            'form' => $form,
            'page_title' => 'Mot de passe oublie',
        ]);
    }

    #[Route('/reset-password/{token}', name: 'app_password_reset', methods: ['GET', 'POST'])]
    public function resetPassword(
        string $token,
        Request $request,
        UserAccountSecurityManager $accountSecurityManager,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
    ): Response {
        $securityToken = $accountSecurityManager->consumePasswordResetToken($token);
        if (!$securityToken instanceof UserSecurityToken) {
            $this->addFlash('error', 'Ce lien de reinitialisation est invalide ou expire.');

            return $this->redirectToRoute('app_forgot_password');
        }

        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $securityToken->getUser();
            $user->setPassword($passwordHasher->hashPassword($user, (string) $form->get('plainPassword')->getData()));
            $accountSecurityManager->markPasswordResetUsed($securityToken);
            $entityManager->flush();

            $this->addFlash('success', 'Mot de passe reinitialise. Vous pouvez vous connecter.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password.html.twig', [
            'form' => $form,
            'page_title' => 'Nouveau mot de passe',
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): never
    {
        throw new \LogicException('This method is intercepted by the firewall logout handler.');
    }
}
