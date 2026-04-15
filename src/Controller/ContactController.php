<?php

namespace App\Controller;

use App\Entity\Account;
use App\Entity\Contact;
use App\Form\ContactType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/contacts')]
#[IsGranted('ROLE_USER')]
class ContactController extends AbstractController
{
    #[Route('', name: 'app_contact_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $search = trim((string) $request->query->get('q', ''));
        $queryBuilder = $entityManager->getRepository(Contact::class)->createQueryBuilder('c');

        if ($search !== '') {
            $queryBuilder
                ->andWhere('c.firstname LIKE :search OR c.lastname LIKE :search OR c.email LIKE :search OR c.city LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        return $this->render('contact/index.html.twig', [
            'contacts' => $queryBuilder
                ->orderBy('c.lastname', 'ASC')
                ->addOrderBy('c.firstname', 'ASC')
                ->getQuery()
                ->getResult(),
            'search' => $search,
        ]);
    }

    #[Route('/new', name: 'app_contact_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $contact = new Contact();
        $account = null;
        if ($accountId = $request->query->getInt('account')) {
            $account = $entityManager->getRepository(Account::class)->find($accountId);
            if ($account instanceof Account) {
                $account->addContact($contact);
            }
        }

        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($contact);
            if ($account instanceof Account) {
                $entityManager->persist($account);
            }
            $entityManager->flush();
            $this->addFlash('success', $account instanceof Account ? 'Contact cree et lie au compte.' : 'Contact cree.');

            if ($account instanceof Account) {
                return $this->redirectToRoute('app_account_show', ['id' => $account->getId()]);
            }

            return $this->redirectToRoute('app_contact_show', ['id' => $contact->getId()]);
        }

        return $this->render('contact/form.html.twig', [
            'contact' => $contact,
            'form' => $form,
            'page_title' => 'Nouveau contact',
            'account_context' => $account,
        ]);
    }

    #[Route('/{id}', name: 'app_contact_show', methods: ['GET'])]
    public function show(Contact $contact): Response
    {
        return $this->render('contact/show.html.twig', [
            'contact' => $contact,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_contact_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Contact $contact, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Contact mis a jour.');

            return $this->redirectToRoute('app_contact_show', ['id' => $contact->getId()]);
        }

        return $this->render('contact/form.html.twig', [
            'contact' => $contact,
            'form' => $form,
            'page_title' => 'Modifier le contact',
        ]);
    }

    #[Route('/{id}/delete', name: 'app_contact_delete', methods: ['POST'])]
    public function delete(Request $request, Contact $contact, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete-contact-'.$contact->getId(), (string) $request->request->get('_token'))) {
            $entityManager->remove($contact);
            $entityManager->flush();
            $this->addFlash('success', 'Contact supprime.');
        }

        return $this->redirectToRoute('app_contact_index');
    }
}
