<?php

namespace App\Controller;

use App\Entity\Account;
use App\Entity\BankProduct;
use App\Form\BankProductType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/products/bank')]
#[IsGranted('ROLE_USER')]
class BankProductController extends AbstractController
{
    #[Route('/new', name: 'app_bank_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $product = new BankProduct();
        $account = null;
        if ($accountId = $request->query->getInt('account')) {
            $account = $entityManager->getRepository(Account::class)->find($accountId);
            if ($account instanceof Account) {
                $account->addProduct($product);
            }
        }

        $form = $this->createForm(BankProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($product);
            $entityManager->flush();
            $this->addFlash('success', $account instanceof Account ? 'Produit bancaire cree et lie au compte.' : 'Produit bancaire cree.');

            if ($account instanceof Account) {
                return $this->redirectToRoute('app_account_show', ['id' => $account->getId()]);
            }

            return $this->redirectToRoute('app_bank_product_show', ['id' => $product->getId()]);
        }

        return $this->render('product/form.html.twig', [
            'form' => $form,
            'page_title' => 'Nouveau produit bancaire',
            'product_type' => 'bank',
            'account_context' => $account,
        ]);
    }

    #[Route('/{id}', name: 'app_bank_product_show', methods: ['GET'])]
    public function show(BankProduct $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
            'product_type' => 'bank',
        ]);
    }

    #[Route('/{id}/edit', name: 'app_bank_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, BankProduct $product, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BankProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Produit bancaire mis a jour.');

            return $this->redirectToRoute('app_bank_product_show', ['id' => $product->getId()]);
        }

        return $this->render('product/form.html.twig', [
            'form' => $form,
            'page_title' => 'Modifier le produit bancaire',
            'product_type' => 'bank',
            'product' => $product,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_bank_product_delete', methods: ['POST'])]
    public function delete(Request $request, BankProduct $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete-bank-product-'.$product->getId(), (string) $request->request->get('_token'))) {
            $entityManager->remove($product);
            $entityManager->flush();
            $this->addFlash('success', 'Produit bancaire supprime.');
        }

        return $this->redirectToRoute('app_product_index');
    }
}
