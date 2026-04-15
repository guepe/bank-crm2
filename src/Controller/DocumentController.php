<?php

namespace App\Controller;

use App\Entity\Account;
use App\Entity\Document;
use App\Form\DocumentType;
use App\Service\DocumentStorage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/documents')]
#[IsGranted('ROLE_USER')]
class DocumentController extends AbstractController
{
    #[Route('', name: 'app_document_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        return $this->render('document/index.html.twig', [
            'documents' => $entityManager->getRepository(Document::class)->findBy([], ['id' => 'DESC']),
        ]);
    }

    #[Route('/new', name: 'app_document_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, DocumentStorage $storage): Response
    {
        $document = new Document();
        $account = null;
        if ($accountId = $request->query->getInt('account')) {
            $account = $entityManager->getRepository(Account::class)->find($accountId);
            if ($account instanceof Account) {
                $document->addAccount($account);
            }
        }

        $form = $this->createForm(DocumentType::class, $document);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $form->get('uploadedFile')->getData();
            if ($uploadedFile !== null) {
                $stored = $storage->store($uploadedFile);
                $document
                    ->setPath($stored['path'])
                    ->setMimeType($stored['mime_type'])
                    ->setSize($stored['size']);
            }

            $entityManager->persist($document);
            $entityManager->flush();
            $this->addFlash('success', $account instanceof Account ? 'Document ajoute et lie au compte.' : 'Document ajoute.');

            if ($account instanceof Account) {
                return $this->redirectToRoute('app_account_show', ['id' => $account->getId()]);
            }

            return $this->redirectToRoute('app_document_show', ['id' => $document->getId()]);
        }

        return $this->render('document/form.html.twig', [
            'form' => $form,
            'document' => $document,
            'page_title' => 'Nouveau document',
            'account_context' => $account,
        ]);
    }

    #[Route('/{id}', name: 'app_document_show', methods: ['GET'])]
    public function show(Document $document): Response
    {
        return $this->render('document/show.html.twig', [
            'document' => $document,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_document_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Document $document, EntityManagerInterface $entityManager, DocumentStorage $storage): Response
    {
        $form = $this->createForm(DocumentType::class, $document, ['is_edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $form->get('uploadedFile')->getData();
            if ($uploadedFile !== null) {
                $stored = $storage->store($uploadedFile);
                $document
                    ->setPath($stored['path'])
                    ->setMimeType($stored['mime_type'])
                    ->setSize($stored['size']);
            }

            $entityManager->flush();
            $this->addFlash('success', 'Document mis a jour.');

            return $this->redirectToRoute('app_document_show', ['id' => $document->getId()]);
        }

        return $this->render('document/form.html.twig', [
            'form' => $form,
            'document' => $document,
            'page_title' => 'Modifier le document',
        ]);
    }

    #[Route('/{id}/download', name: 'app_document_download', methods: ['GET'])]
    public function download(Document $document, DocumentStorage $storage): Response
    {
        if ($document->getPath() === null) {
            throw $this->createNotFoundException('Aucun fichier lie a ce document.');
        }

        $absolutePath = $storage->getAbsolutePath($document->getPath());
        if (!is_file($absolutePath)) {
            throw $this->createNotFoundException('Le fichier n existe pas sur le stockage local.');
        }

        $response = new BinaryFileResponse($absolutePath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $document->getName() ?: basename($absolutePath)
        );

        return $response;
    }

    #[Route('/{id}/delete', name: 'app_document_delete', methods: ['POST'])]
    public function delete(Request $request, Document $document, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete-document-'.$document->getId(), (string) $request->request->get('_token'))) {
            $entityManager->remove($document);
            $entityManager->flush();
            $this->addFlash('success', 'Document supprime.');
        }

        return $this->redirectToRoute('app_document_index');
    }
}
