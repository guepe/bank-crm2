<?php

namespace App\Controller;

use App\Entity\Tenant;
use App\Form\TenantType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/tenants')]
#[IsGranted('ROLE_ADMIN')]
class TenantController extends AbstractController
{
    #[Route('', name: 'app_tenant_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        return $this->render('tenant/index.html.twig', [
            'tenants' => $entityManager->getRepository(Tenant::class)->findBy([], ['name' => 'ASC']),
        ]);
    }

    #[Route('/new', name: 'app_tenant_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $tenant = new Tenant();
        $form = $this->createForm(TenantType::class, $tenant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($tenant);
            $entityManager->flush();
            $this->addFlash('success', 'Tenant cree.');

            return $this->redirectToRoute('app_tenant_show', ['id' => $tenant->getId()]);
        }

        return $this->render('tenant/form.html.twig', [
            'form' => $form,
            'page_title' => 'Nouveau tenant',
            'tenant' => $tenant,
        ]);
    }

    #[Route('/{id}', name: 'app_tenant_show', methods: ['GET'])]
    public function show(Tenant $tenant): Response
    {
        return $this->render('tenant/show.html.twig', [
            'tenant' => $tenant,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_tenant_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Tenant $tenant, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TenantType::class, $tenant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Tenant mis a jour.');

            return $this->redirectToRoute('app_tenant_show', ['id' => $tenant->getId()]);
        }

        return $this->render('tenant/form.html.twig', [
            'form' => $form,
            'page_title' => 'Modifier tenant',
            'tenant' => $tenant,
        ]);
    }

    #[Route('/{id}/status', name: 'app_tenant_status', methods: ['POST'])]
    public function changeStatus(Request $request, Tenant $tenant, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('tenant-status-'.$tenant->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $tenant->setStatus($tenant->isActive() ? Tenant::STATUS_SUSPENDED : Tenant::STATUS_ACTIVE);
        $entityManager->flush();
        $this->addFlash('success', $tenant->isActive() ? 'Tenant reactive.' : 'Tenant suspendu.');

        return $this->redirectToRoute('app_tenant_show', ['id' => $tenant->getId()]);
    }
}
