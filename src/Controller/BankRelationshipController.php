<?php

namespace App\Controller;

use App\Entity\BankRelationship;
use App\Entity\Contact;
use App\Form\BankDisclosureSubmissionType;
use App\Form\BankRelationshipType;
use App\Repository\BankAccessLinkRepository;
use App\Service\BankRelationshipManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class BankRelationshipController extends AbstractController
{
    #[Route('/contacts/{id}/banks/new', name: 'app_bank_relationship_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Contact $contact, Request $request, EntityManagerInterface $entityManager): Response
    {
        $relationship = new BankRelationship($contact);
        $form = $this->createForm(BankRelationshipType::class, $relationship);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $contact->addBankRelationship($relationship);
            $entityManager->persist($relationship);
            $entityManager->flush();
            $this->addFlash('success', 'Relation bancaire creee.');

            return $this->redirectToRoute('app_contact_show', ['id' => $contact->getId()]);
        }

        return $this->render('bank_relationship/form.html.twig', [
            'contact' => $contact,
            'form' => $form,
            'page_title' => 'Nouvelle relation bancaire',
        ]);
    }

    #[Route('/contacts/{contact}/banks/{id}/edit', name: 'app_bank_relationship_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function edit(Contact $contact, BankRelationship $relationship, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BankRelationshipType::class, $relationship);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Relation bancaire mise a jour.');

            return $this->redirectToRoute('app_contact_show', ['id' => $contact->getId()]);
        }

        return $this->render('bank_relationship/form.html.twig', [
            'contact' => $contact,
            'form' => $form,
            'page_title' => 'Modifier la relation bancaire',
        ]);
    }

    #[Route('/contacts/{contact}/banks/{id}/send', name: 'app_bank_relationship_send', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function send(Contact $contact, BankRelationship $relationship, Request $request, BankRelationshipManager $manager): Response
    {
        if (!$this->isCsrfTokenValid('send-bank-relationship-'.$relationship->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        try {
            $manager->sendBankRequest($relationship);
            $this->addFlash('success', 'Le dossier client a ete envoye a la banque.');
        } catch (\InvalidArgumentException $exception) {
            $this->addFlash('error', $exception->getMessage());
        }

        return $this->redirectToRoute('app_contact_show', ['id' => $contact->getId()]);
    }

    #[Route('/bank-access/{token}', name: 'app_bank_access', methods: ['GET', 'POST'])]
    public function access(string $token, Request $request, BankAccessLinkRepository $bankAccessLinkRepository, BankRelationshipManager $manager): Response
    {
        $accessLink = $bankAccessLinkRepository->findActiveByToken($token);
        if ($accessLink === null) {
            throw $this->createNotFoundException('Ce lien bancaire est invalide, expire ou revoque.');
        }

        $contact = $accessLink->getContact();
        $form = $this->createForm(BankDisclosureSubmissionType::class, null, ['contact' => $contact]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array{account:\App\Entity\Account,number:?string,type:?string,amount:mixed,notes:?string} $data */
            $data = $form->getData();
            $manager->addSubmittedProduct($accessLink, $data);
            $this->addFlash('success', 'Produit bancaire ajoute au dossier client.');

            return $this->redirectToRoute('app_bank_access', ['token' => $token]);
        }

        return $this->render('bank_relationship/access_link.html.twig', [
            'access_link' => $accessLink,
            'summary' => $accessLink->getSummarySnapshot(),
            'submitted_products' => $manager->getSubmittedProducts($accessLink->getBankRelationship()),
            'form' => $form,
        ]);
    }
}
