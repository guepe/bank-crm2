<?php

namespace App\Command;

use App\Entity\Account;
use App\Entity\BankProduct;
use App\Entity\Category;
use App\Entity\Contact;
use App\Entity\CreditProduct;
use App\Entity\Document;
use App\Entity\FileLinked;
use App\Entity\FiscalProduct;
use App\Entity\Lead;
use App\Entity\SavingsProduct;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:seed-demo-data', description: 'Populate the local database with demo CRM data')]
class SeedDemoDataCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $existingLeadCount = $this->entityManager->getRepository(Lead::class)->count([]);
        if ($existingLeadCount > 0) {
            $io->warning('La base contient deja des leads. Seed annule pour eviter les doublons.');

            return Command::SUCCESS;
        }

        $categories = $this->createCategories();
        $documents = $this->createDocuments();
        $contacts = $this->createContacts($documents);
        $products = $this->createProducts($categories);
        $accounts = $this->createAccounts($contacts, $documents, $products);
        $leads = $this->createLeads();
        $fileLinked = $this->createFileLinkedSamples();
        $users = $this->createDemoUsers($contacts);

        foreach ([
            ...$categories,
            ...$documents,
            ...$contacts,
            ...$products,
            ...$accounts,
            ...$leads,
            ...$fileLinked,
            ...$users,
        ] as $entity) {
            $this->entityManager->persist($entity);
        }

        $this->entityManager->flush();

        $io->success(sprintf(
            'Seed termine: %d categories, %d documents, %d contacts, %d produits, %d comptes, %d leads, %d fichiers lies, %d utilisateurs.',
            count($categories),
            count($documents),
            count($contacts),
            count($products),
            count($accounts),
            count($leads),
            count($fileLinked),
            count($users)
        ));

        return Command::SUCCESS;
    }

    /** @return list<Category> */
    private function createCategories(): array
    {
        $retail = (new Category())->setName('Retail');
        $pro = (new Category())->setName('Professionnel');
        $epargne = (new Category())->setName('Epargne')->setParent($retail);
        $credit = (new Category())->setName('Credit')->setParent($retail);
        $fiscal = (new Category())->setName('Fiscal')->setParent($pro);

        return [$retail, $pro, $epargne, $credit, $fiscal];
    }

    /** @return list<Document> */
    private function createDocuments(): array
    {
        return [
            (new Document())
                ->setName('Carte identite')
                ->setPath('demo/carte-identite-marie.pdf')
                ->setMimeType('application/pdf')
                ->setSize(148220),
            (new Document())
                ->setName('Fiche salaire')
                ->setPath('demo/fiche-salaire-janvier.pdf')
                ->setMimeType('application/pdf')
                ->setSize(92310),
            (new Document())
                ->setName('Statuts societe')
                ->setPath('demo/statuts-societe.pdf')
                ->setMimeType('application/pdf')
                ->setSize(244901),
        ];
    }

    /** @param list<Document> $documents
     *  @return list<Contact>
     */
    private function createContacts(array $documents): array
    {
        $contact1 = (new Contact())
            ->setFirstname('Marie')
            ->setLastname('Dupont')
            ->setEmail('marie.dupont@example.test')
            ->setPhone('081/22.33.44')
            ->setGsm('0470/11.22.33')
            ->setStreetNum('Rue de Fer 12')
            ->setZip('5000')
            ->setCity('Namur')
            ->setCountry('BE')
            ->setBirthplace('Namur')
            ->setBirthdate(new \DateTime('1988-03-14'))
            ->setProfession('Infirmiere')
            ->setIncomeAmount(3200)
            ->setIncomeRecurence('monthly')
            ->setIncomeDate('Le 25');
        $contact1->getDocuments()->add($documents[0]);

        $contact2 = (new Contact())
            ->setFirstname('Julien')
            ->setLastname('Martin')
            ->setEmail('julien.martin@example.test')
            ->setPhone('04/220.00.11')
            ->setGsm('0499/88.77.66')
            ->setStreetNum('Avenue Reine Astrid 45')
            ->setZip('4000')
            ->setCity('Liege')
            ->setCountry('BE')
            ->setBirthplace('Liege')
            ->setBirthdate(new \DateTime('1979-11-02'))
            ->setProfession('Architecte')
            ->setIncomeAmount(5400)
            ->setIncomeRecurence('monthly')
            ->setIncomeDate('Le 5');
        $contact2->getDocuments()->add($documents[1]);

        $contact3 = (new Contact())
            ->setFirstname('Sophie')
            ->setLastname('Lambert')
            ->setEmail('sophie.lambert@example.test')
            ->setPhone('02/555.77.88')
            ->setGsm('0488/55.44.33')
            ->setStreetNum('Chaussée de Charleroi 89')
            ->setZip('1060')
            ->setCity('Saint-Gilles')
            ->setCountry('BE')
            ->setBirthplace('Bruxelles')
            ->setBirthdate(new \DateTime('1991-07-21'))
            ->setProfession('Consultante')
            ->setIncomeAmount(4100)
            ->setIncomeRecurence('monthly')
            ->setIncomeDate('Le 28');

        return [$contact1, $contact2, $contact3];
    }

    /** @param list<Category> $categories
     *  @return list<object>
     */
    private function createProducts(array $categories): array
    {
        $bank = (new BankProduct())
            ->setNumber('BE12-0001-2026')
            ->setType('Compte courant')
            ->setCompany('Bank CRM Demo')
            ->setAmount('8500.0000')
            ->setDescription('Compte courant principal')
            ->setTauxInteret('0.10');
        $bank->addCategory($categories[0]);

        $credit = (new CreditProduct())
            ->setNumber('CR-2026-0042')
            ->setType('Hypothecaire')
            ->setCompany('Bank CRM Demo')
            ->setAmount('215000.0000')
            ->setDuration('240.00')
            ->setPaymentDate('Le 3')
            ->setVariability('Fixe')
            ->setPurpose('Acquisition residence principale')
            ->setGarantee('Mandat hypothecaire')
            ->setStartDate(new \DateTime('2026-01-01'))
            ->setEndDate(new \DateTime('2046-01-01'))
            ->setTauxInteret('3.25');
        $credit->addCategory($categories[3]);

        $fiscal = (new FiscalProduct())
            ->setNumber('FISC-2026-010')
            ->setType('Epargne pension')
            ->setCompany('Bank CRM Demo')
            ->setCapitalTerme('18000.0000')
            ->setRecurrentPrimeAmount('125.0000')
            ->setPaymentDate('Le 10')
            ->setPaymentDeadline('31/12')
            ->setStartDate(new \DateTime('2025-01-01'))
            ->setTauxInteret('2.10');
        $fiscal->addCategory($categories[4]);

        $savings = (new SavingsProduct())
            ->setNumber('SAV-2026-009')
            ->setType('Branche 21')
            ->setCompany('Bank CRM Demo')
            ->setAmount('25000.0000')
            ->setDuration('96.00')
            ->setPrimeRecurence('monthly')
            ->setRecurrentPrimeAmount('200.0000')
            ->setPaymentDate('Le 15')
            ->setStartDate(new \DateTime('2026-02-01'))
            ->setTauxInteret('1.85');
        $savings->addCategory($categories[2]);

        return [$bank, $credit, $fiscal, $savings];
    }

    /** @param list<Contact> $contacts
     *  @param list<Document> $documents
     *  @param list<object> $products
     *  @return list<Account>
     */
    private function createAccounts(array $contacts, array $documents, array $products): array
    {
        $account1 = (new Account())
            ->setName('Famille Dupont')
            ->setCompanyStatut('Personne physique')
            ->setType('Core')
            ->setStreetNum('Rue de Fer 12')
            ->setZip('5000')
            ->setCity('Namur')
            ->setCountry('BE')
            ->setStartingDate(new \DateTime('2024-05-12'))
            ->setNotes('Client historique avec potentiel cross-sell.')
            ->setOtherBank('Banque concurrente A');
        $account1->addContact($contacts[0]);
        $account1->addDocument($documents[0]);
        $account1->addProduct($products[0]);
        $account1->addProduct($products[2]);

        $account2 = (new Account())
            ->setName('Atelier Martin SRL')
            ->setCompanyStatut('SPRL')
            ->setType('Standard')
            ->setStreetNum('Avenue Reine Astrid 45')
            ->setZip('4000')
            ->setCity('Liege')
            ->setCountry('BE')
            ->setStartingDate(new \DateTime('2025-09-01'))
            ->setNotes('Besoin de financement professionnel et placements de tresorerie.')
            ->setOtherBank('Banque concurrente B');
        $account2->addContact($contacts[1]);
        $account2->addDocument($documents[1]);
        $account2->addDocument($documents[2]);
        $account2->addProduct($products[1]);

        $account3 = (new Account())
            ->setName('Sophie Lambert')
            ->setCompanyStatut('Personne physique')
            ->setType('Potentiel')
            ->setStreetNum('Chaussée de Charleroi 89')
            ->setZip('1060')
            ->setCity('Saint-Gilles')
            ->setCountry('BE')
            ->setStartingDate(new \DateTime('2026-03-10'))
            ->setNotes('Prospect chaud converti en cliente recente.')
            ->setOtherBank('Neobanque C');
        $account3->addContact($contacts[2]);
        $account3->addProduct($products[3]);

        return [$account1, $account2, $account3];
    }

    /** @return list<Lead> */
    private function createLeads(): array
    {
        return [
            (new Lead())
                ->setName('Garage Delvaux')
                ->setCompanyStatut('SA')
                ->setType('Potentiel')
                ->setStreetNum('Rue des Artisans 7')
                ->setZip('5100')
                ->setCity('Jambes')
                ->setCountry('BE')
                ->setOtherBank('Banque Delta')
                ->setStartingDate(new \DateTime('2026-04-01'))
                ->setNotes('Demande un rendez-vous pour credit d investissement.'),
            (new Lead())
                ->setName('Isabelle Renard')
                ->setCompanyStatut('Personne physique')
                ->setType('Standard')
                ->setStreetNum('Boulevard Tirou 18')
                ->setZip('6000')
                ->setCity('Charleroi')
                ->setCountry('BE')
                ->setOtherBank('Banque Horizon')
                ->setStartingDate(new \DateTime('2026-04-07'))
                ->setNotes('Interessée par une assurance-vie et une optimisation fiscale.'),
            (new Lead())
                ->setName('Menuiserie Verhaegen')
                ->setCompanyStatut('SPRL')
                ->setType('Core')
                ->setStreetNum('Route de Mons 101')
                ->setZip('7000')
                ->setCity('Mons')
                ->setCountry('BE')
                ->setOtherBank('Banque Union')
                ->setStartingDate(new \DateTime('2026-04-12'))
                ->setNotes('Lead issu d un apporteur d affaires local.'),
        ];
    }

    /** @return list<FileLinked> */
    private function createFileLinkedSamples(): array
    {
        return [
            (new FileLinked())
                ->setType('pdf')
                ->setName('brochure-produits.pdf')
                ->setFiledata('Demo binary payload 1'),
            (new FileLinked())
                ->setType('image')
                ->setName('scan-carte-id.jpg')
                ->setFiledata('Demo binary payload 2'),
        ];
    }

    /** @param list<Contact> $contacts
     *  @return list<User>
     */
    private function createDemoUsers(array $contacts): array
    {
        $adminUser = (new User())
            ->setUsername('demo')
            ->setEmail('demo@example.com')
            ->setRoles(['ROLE_ADMIN'])
            ->setEnabled(true);
        $adminUser->setPassword($this->passwordHasher->hashPassword($adminUser, 'demo'));

        $clientUser = (new User())
            ->setUsername('clientdemo')
            ->setEmail('clientdemo@example.test')
            ->setRoles(['ROLE_CLIENT'])
            ->setEnabled(true)
            ->setContact($contacts[0]);
        $clientUser->setPassword($this->passwordHasher->hashPassword($clientUser, 'clientdemo'));

        return [$adminUser, $clientUser];
    }
}
