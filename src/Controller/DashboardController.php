<?php

namespace App\Controller;

use App\Entity\Account;
use App\Entity\Contact;
use App\Entity\Lead;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        return $this->render('dashboard/index.html.twig', [
            'lead_count' => $entityManager->getRepository(Lead::class)->count([]),
            'contact_count' => $entityManager->getRepository(Contact::class)->count([]),
            'account_count' => $entityManager->getRepository(Account::class)->count([]),
            'recent_leads' => $entityManager->getRepository(Lead::class)->findBy([], ['id' => 'DESC'], 5),
            'recent_contacts' => $entityManager->getRepository(Contact::class)->findBy([], ['id' => 'DESC'], 5),
            'recent_accounts' => $entityManager->getRepository(Account::class)->findBy([], ['id' => 'DESC'], 5),
        ]);
    }
}
