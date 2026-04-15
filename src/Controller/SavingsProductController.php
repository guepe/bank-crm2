<?php

namespace App\Controller;

use App\Entity\Account;
use App\Entity\SavingsProduct;
use App\Form\SavingsProductType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/products/savings')]
#[IsGranted('ROLE_USER')]
class SavingsProductController extends AbstractController
{
    #[Route('/new', name: 'app_savings_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $product = new SavingsProduct();
        $account = null;
        if ($accountId = $request->query->getInt('account')) {
            $account = $entityManager->getRepository(Account::class)->find($accountId);
            if ($account instanceof Account) {
                $account->addProduct($product);
            }
        }

        $form = $this->createForm(SavingsProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($product);
            $entityManager->flush();
            $this->addFlash('success', $account instanceof Account ? 'Produit d epargne cree et lie au compte.' : 'Produit d epargne cree.');

            if ($account instanceof Account) {
                return $this->redirectToRoute('app_account_show', ['id' => $account->getId()]);
            }

            return $this->redirectToRoute('app_savings_product_show', ['id' => $product->getId()]);
        }

        return $this->render('product/form.html.twig', [
            'form' => $form,
            'page_title' => 'Nouveau produit d epargne',
            'product_type' => 'savings',
            'account_context' => $account,
        ]);
    }

    #[Route('/{id}', name: 'app_savings_product_show', methods: ['GET'])]
    public function show(SavingsProduct $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
            'product_type' => 'savings',
        ]);
    }

    #[Route('/{id}/edit', name: 'app_savings_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, SavingsProduct $product, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SavingsProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Produit d epargne mis a jour.');

            return $this->redirectToRoute('app_savings_product_show', ['id' => $product->getId()]);
        }

        return $this->render('product/form.html.twig', [
            'form' => $form,
            'page_title' => 'Modifier le produit d epargne',
            'product_type' => 'savings',
            'product' => $product,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_savings_product_delete', methods: ['POST'])]
    public function delete(Request $request, SavingsProduct $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete-savings-product-'.$product->getId(), (string) $request->request->get('_token'))) {
            $entityManager->remove($product);
            $entityManager->flush();
            $this->addFlash('success', 'Produit d epargne supprime.');
        }

        return $this->redirectToRoute('app_product_index');
    }
}
