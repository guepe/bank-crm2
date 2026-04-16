<?php

namespace App\Controller;

use App\Entity\Account;
use App\Form\AccountType;
use App\Service\ListFilterOptions;
use App\Service\TimelineBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/accounts')]
#[IsGranted('ROLE_USER')]
class AccountController extends AbstractController
{
    #[Route('', name: 'app_account_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $entityManager, ListFilterOptions $filterOptions): Response
    {
        $search = trim((string) $request->query->get('q', ''));
        $city = trim((string) $request->query->get('city', ''));
        $type = trim((string) $request->query->get('type', ''));
        $contacts = (string) $request->query->get('contacts', '');
        $queryBuilder = $entityManager->getRepository(Account::class)->createQueryBuilder('a');
        $queryBuilder->distinct();

        if ($search !== '') {
            $queryBuilder
                ->andWhere('a.name LIKE :search OR a.city LIKE :search OR a.type LIKE :search OR a.otherBank LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        if ($city !== '') {
            $queryBuilder
                ->andWhere('a.city = :city')
                ->setParameter('city', $city);
        }

        if ($type !== '') {
            $queryBuilder
                ->andWhere('a.type = :type')
                ->setParameter('type', $type);
        }

        if ($contacts === 'with') {
            $queryBuilder
                ->leftJoin('a.contacts', 'contact')
                ->andWhere('contact.id IS NOT NULL');
        } elseif ($contacts === 'without') {
            $queryBuilder
                ->leftJoin('a.contacts', 'contact')
                ->andWhere('contact.id IS NULL');
        }

        return $this->render('account/index.html.twig', [
            'accounts' => $queryBuilder
                ->orderBy('a.name', 'ASC')
                ->getQuery()
                ->getResult(),
            'search' => $search,
            'filters' => [
                'city' => $city,
                'type' => $type,
                'contacts' => $contacts,
            ],
            'filter_options' => [
                'cities' => $filterOptions->distinctNonEmptyValues(Account::class, 'a', 'city'),
                'types' => $filterOptions->distinctNonEmptyValues(Account::class, 'a', 'type'),
            ],
        ]);
    }

    #[Route('/new', name: 'app_account_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $account = new Account();
        $form = $this->createForm(AccountType::class, $account);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($account);
            $entityManager->flush();
            $this->addFlash('success', 'Compte cree.');

            return $this->redirectToRoute('app_account_show', ['id' => $account->getId()]);
        }

        return $this->render('account/form.html.twig', [
            'account' => $account,
            'form' => $form,
            'page_title' => 'Nouveau compte',
        ]);
    }

    #[Route('/{id}', name: 'app_account_show', methods: ['GET'])]
    public function show(Account $account, TimelineBuilder $timelineBuilder): Response
    {
        return $this->render('account/show.html.twig', [
            'account' => $account,
            'timeline' => $timelineBuilder->buildForAccount($account),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_account_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Account $account, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AccountType::class, $account);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Compte mis a jour.');

            return $this->redirectToRoute('app_account_show', ['id' => $account->getId()]);
        }

        return $this->render('account/form.html.twig', [
            'account' => $account,
            'form' => $form,
            'page_title' => 'Modifier le compte',
        ]);
    }

    #[Route('/{id}/delete', name: 'app_account_delete', methods: ['POST'])]
    public function delete(Request $request, Account $account, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete-account-'.$account->getId(), (string) $request->request->get('_token'))) {
            $entityManager->remove($account);
            $entityManager->flush();
            $this->addFlash('success', 'Compte supprime.');
        }

        return $this->redirectToRoute('app_account_index');
    }
}
