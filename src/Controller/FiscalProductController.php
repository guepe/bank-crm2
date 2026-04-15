<?php

namespace App\Controller;

use App\Entity\Account;
use App\Entity\FiscalProduct;
use App\Form\FiscalProductType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/products/fiscal')]
#[IsGranted('ROLE_USER')]
class FiscalProductController extends AbstractController
{
    #[Route('/new', name: 'app_fiscal_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $product = new FiscalProduct();
        $account = null;
        if ($accountId = $request->query->getInt('account')) {
            $account = $entityManager->getRepository(Account::class)->find($accountId);
            if ($account instanceof Account) {
                $account->addProduct($product);
            }
        }

        $form = $this->createForm(FiscalProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($product);
            $entityManager->flush();
            $this->addFlash('success', $account instanceof Account ? 'Produit fiscal cree et lie au compte.' : 'Produit fiscal cree.');

            if ($account instanceof Account) {
                return $this->redirectToRoute('app_account_show', ['id' => $account->getId()]);
            }

            return $this->redirectToRoute('app_fiscal_product_show', ['id' => $product->getId()]);
        }

        return $this->render('product/form.html.twig', [
            'form' => $form,
            'page_title' => 'Nouveau produit fiscal',
            'product_type' => 'fiscal',
            'account_context' => $account,
        ]);
    }

    #[Route('/{id}', name: 'app_fiscal_product_show', methods: ['GET'])]
    public function show(FiscalProduct $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
            'product_type' => 'fiscal',
        ]);
    }

    #[Route('/{id}/edit', name: 'app_fiscal_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, FiscalProduct $product, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(FiscalProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Produit fiscal mis a jour.');

            return $this->redirectToRoute('app_fiscal_product_show', ['id' => $product->getId()]);
        }

        return $this->render('product/form.html.twig', [
            'form' => $form,
            'page_title' => 'Modifier le produit fiscal',
            'product_type' => 'fiscal',
            'product' => $product,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_fiscal_product_delete', methods: ['POST'])]
    public function delete(Request $request, FiscalProduct $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete-fiscal-product-'.$product->getId(), (string) $request->request->get('_token'))) {
            $entityManager->remove($product);
            $entityManager->flush();
            $this->addFlash('success', 'Produit fiscal supprime.');
        }

        return $this->redirectToRoute('app_product_index');
    }
}
