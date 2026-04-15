<?php

namespace App\Controller;

use App\Entity\BankProduct;
use App\Entity\CreditProduct;
use App\Entity\FiscalProduct;
use App\Entity\SavingsProduct;
use Doctrine\ORM\EntityManagerInterface;
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
}
