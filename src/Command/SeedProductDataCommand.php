<?php

namespace App\Command;

use App\Entity\Account;
use App\Entity\BankProduct;
use App\Entity\Category;
use App\Entity\CreditProduct;
use App\Entity\FiscalProduct;
use App\Entity\SavingsProduct;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:seed-product-data', description: 'Append demo product data to the local database')]
class SeedProductDataCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $existing = $this->entityManager->getRepository(BankProduct::class)->findOneBy(['number' => 'BE55-EXTRA-2026']);
        if ($existing !== null) {
            $io->warning('Les produits de demo supplementaires existent deja.');

            return Command::SUCCESS;
        }

        $retail = $this->findOrCreateCategory('Retail');
        $creditCategory = $this->findOrCreateCategory('Credit');
        $fiscalCategory = $this->findOrCreateCategory('Fiscal');
        $savingsCategory = $this->findOrCreateCategory('Epargne');

        $accounts = $this->entityManager->getRepository(Account::class)->findAll();

        $bank = (new BankProduct())
            ->setNumber('BE55-EXTRA-2026')
            ->setType('Compte epargne')
            ->setCompany('Bank CRM Demo')
            ->setAmount('12650.0000')
            ->setDescription('Compte epargne secondaire pour liquidites court terme')
            ->setTauxInteret('0.75')
            ->addCategory($retail);

        $credit = (new CreditProduct())
            ->setNumber('CR-PLUS-2026')
            ->setType('Investissement')
            ->setCompany('Bank CRM Demo')
            ->setAmount('78000.0000')
            ->setDuration('84.00')
            ->setPaymentDate('Le 12')
            ->setVariability('Variable')
            ->setPurpose('Acquisition materiel')
            ->setGarantee('Caution personnelle')
            ->setStartDate(new \DateTime('2026-06-01'))
            ->setEndDate(new \DateTime('2033-06-01'))
            ->setTauxInteret('4.10')
            ->addCategory($creditCategory);

        $fiscal = (new FiscalProduct())
            ->setNumber('FISC-BIS-2026')
            ->setType('Placement fiscal')
            ->setCompany('Bank CRM Demo')
            ->setCapitalTerme('9500.0000')
            ->setRecurrentPrimeAmount('95.0000')
            ->setPaymentDate('Le 18')
            ->setPaymentDeadline('31/12')
            ->setReserve('2000.0000')
            ->setReserveDate(new \DateTime('2026-09-30'))
            ->setStartDate(new \DateTime('2026-01-15'))
            ->setTauxInteret('2.55')
            ->addCategory($fiscalCategory);

        $savings = (new SavingsProduct())
            ->setNumber('SAV-BIS-2026')
            ->setType('Assurance epargne')
            ->setCompany('Bank CRM Demo')
            ->setAmount('42000.0000')
            ->setDuration('120.00')
            ->setPrimeRecurence('yearly')
            ->setRecurrentPrimeAmount('1500.0000')
            ->setPaymentDate('Le 30')
            ->setPaymentDeadline('31/12')
            ->setStartDate(new \DateTime('2026-04-01'))
            ->setTauxInteret('2.05')
            ->addCategory($savingsCategory);

        $products = [$bank, $credit, $fiscal, $savings];

        $accountCount = count($accounts);

        foreach ($products as $index => $product) {
            if ($accountCount > 0) {
                $accounts[$index % $accountCount]->addProduct($product);
            }
            $this->entityManager->persist($product);
        }

        $this->entityManager->flush();

        $io->success('Produits de demo supplementaires ajoutes.');

        return Command::SUCCESS;
    }

    private function findOrCreateCategory(string $name): Category
    {
        $category = $this->entityManager->getRepository(Category::class)->findOneBy(['name' => $name]);
        if ($category instanceof Category) {
            return $category;
        }

        $category = (new Category())->setName($name);
        $this->entityManager->persist($category);

        return $category;
    }
}
