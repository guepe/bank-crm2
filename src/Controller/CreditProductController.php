<?php

namespace App\Controller;

use App\Entity\Account;
use App\Entity\CreditProduct;
use App\Form\CreditProductType;
use App\Form\ProductDocumentUploadType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/products/credit')]
#[IsGranted('ROLE_USER')]
class CreditProductController extends AbstractController
{
    #[Route('/new', name: 'app_credit_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $product = new CreditProduct();
        $account = null;
        if ($accountId = $request->query->getInt('account')) {
            $account = $entityManager->getRepository(Account::class)->find($accountId);
            if ($account instanceof Account) {
                $account->addProduct($product);
            }
        }

        $form = $this->createForm(CreditProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($product);
            $entityManager->flush();
            $this->addFlash('success', $account instanceof Account ? 'Produit de credit cree et lie au compte.' : 'Produit de credit cree.');

            if ($account instanceof Account) {
                return $this->redirectToRoute('app_account_show', ['id' => $account->getId()]);
            }

            return $this->redirectToRoute('app_credit_product_show', ['id' => $product->getId()]);
        }

        return $this->render('product/form.html.twig', [
            'form' => $form,
            'page_title' => 'Nouveau produit de credit',
            'product_type' => 'credit',
            'account_context' => $account,
        ]);
    }

    #[Route('/{id}', name: 'app_credit_product_show', methods: ['GET'])]
    public function show(CreditProduct $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
            'product_type' => 'credit',
            'document_upload_form' => $this->createForm(ProductDocumentUploadType::class, null, [
                'action' => $this->generateUrl('app_product_document_upload', ['id' => $product->getId()]),
            ])->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_credit_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, CreditProduct $product, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CreditProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Produit de credit mis a jour.');

            return $this->redirectToRoute('app_credit_product_show', ['id' => $product->getId()]);
        }

        return $this->render('product/form.html.twig', [
            'form' => $form,
            'page_title' => 'Modifier le produit de credit',
            'product_type' => 'credit',
            'product' => $product,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_credit_product_delete', methods: ['POST'])]
    public function delete(Request $request, CreditProduct $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete-credit-product-'.$product->getId(), (string) $request->request->get('_token'))) {
            $entityManager->remove($product);
            $entityManager->flush();
            $this->addFlash('success', 'Produit de credit supprime.');
        }

        return $this->redirectToRoute('app_product_index');
    }
}
