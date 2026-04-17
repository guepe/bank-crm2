<?php

namespace App\Controller;

use App\Entity\BankProduct;
use App\Entity\CreditProduct;
use App\Entity\Document;
use App\Entity\FiscalProduct;
use App\Entity\MetaProduct;
use App\Entity\SavingsProduct;
use App\Form\ProductDocumentUploadType;
use App\Service\DocumentStorage;
use App\Service\ProductDocumentAnalyzer;
use App\Service\ProductRouteHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/products')]
#[IsGranted('ROLE_USER')]
class ProductController extends AbstractController
{
    #[Route('', name: 'app_product_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        return $this->render('product/index.html.twig', [
            'bank_products' => $entityManager->getRepository(BankProduct::class)->findBy([], ['id' => 'DESC']),
            'credit_products' => $entityManager->getRepository(CreditProduct::class)->findBy([], ['id' => 'DESC']),
            'fiscal_products' => $entityManager->getRepository(FiscalProduct::class)->findBy([], ['id' => 'DESC']),
            'savings_products' => $entityManager->getRepository(SavingsProduct::class)->findBy([], ['id' => 'DESC']),
        ]);
    }

    #[Route('/{id}/documents/upload', name: 'app_product_document_upload', methods: ['POST'])]
    public function uploadDocument(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        DocumentStorage $storage,
        ProductDocumentAnalyzer $analyzer,
        ProductRouteHelper $routeHelper,
    ): Response {
        $product = $entityManager->find(MetaProduct::class, $id);
        if (!$product instanceof MetaProduct) {
            throw $this->createNotFoundException('Produit introuvable.');
        }

        $form = $this->createForm(ProductDocumentUploadType::class, null, [
            'action' => $this->generateUrl('app_product_document_upload', ['id' => $product->getId()]),
        ]);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->addFlash('error', 'Le document produit n a pas pu etre ajoute. Verifie le fichier envoye.');

            return $this->redirectToRoute($routeHelper->showRoute($product), ['id' => $product->getId()]);
        }

        /** @var array{name:?string,uploadedFile:\Symfony\Component\HttpFoundation\File\UploadedFile} $data */
        $data = $form->getData();
        $uploadedFile = $form->get('uploadedFile')->getData();
        if ($uploadedFile === null) {
            $this->addFlash('error', 'Aucun fichier n a ete recu.');

            return $this->redirectToRoute($routeHelper->showRoute($product), ['id' => $product->getId()]);
        }

        $stored = $storage->store($uploadedFile);
        $document = (new Document())
            ->setName(trim((string) ($data['name'] ?? '')) !== '' ? (string) $data['name'] : $uploadedFile->getClientOriginalName())
            ->setPath($stored['path'])
            ->setMimeType($stored['mime_type'])
            ->setSize($stored['size']);

        $document->addProduct($product);
        foreach ($product->getAccounts() as $account) {
            $document->addAccount($account);
        }

        $entityManager->persist($document);

        $analysis = $analyzer->analyzeAndApply($product, $document);
        $entityManager->flush();

        $message = $analysis['message'];
        if ($analysis['applied_fields'] !== []) {
            $message .= ' Champs completes: '.implode(', ', $analysis['applied_fields']).'.';
        }

        $this->addFlash('success', $message);

        return $this->redirectToRoute($routeHelper->showRoute($product), ['id' => $product->getId()]);
    }
}
