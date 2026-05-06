<?php

namespace App\Service;

use App\Entity\Account;
use App\Entity\BankProduct;
use App\Entity\Contact;
use App\Entity\CreditProduct;
use App\Entity\FieldEdit;
use App\Entity\FiscalProduct;
use App\Entity\MetaProduct;
use App\Entity\OnboardingSession;
use App\Entity\SavingsProduct;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Orchestrates PLANILIFE onboarding phases and persistence.
 */
class OnboardingService
{
    private const ONBOARDING_PRODUCT_MARKER = '[planilife-onboarding-product]';

    /**
     * @var list<string>
     */
    private const PHASE_SEQUENCE = [
        OnboardingSession::PHASE_DISCOVERY,
        OnboardingSession::PHASE_QUALIFICATION,
        OnboardingSession::PHASE_RISK_ANALYSIS,
        OnboardingSession::PHASE_ETAPES,
        OnboardingSession::PHASE_PATRIMOINE,
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DataExtractionService $dataExtraction,
        private readonly OnboardingServiceRequiredFields $requiredFieldsHelper,
        private readonly FieldProvenanceService $provenanceService,
    ) {}

    /**
     * Process the structured LLM payload, persist entities, and return the live state snapshot.
     * Now with field provenance tracking for collaborative documents (EP03).
     */
    public function processLlmResponse(OnboardingSession $session, array $llmJson): array
    {
        $currentPhase = $session->getPhase();
        $extractedFields = is_array($llmJson['extractedFields'] ?? null) ? $llmJson['extractedFields'] : [];

        if ($extractedFields !== []) {
            // Track each field update with provenance
            $nested = $this->convertDotNotationToNestedArray($extractedFields);
            $this->trackFieldUpdates($session, $nested, FieldEdit::SOURCE_DETECTED);

            // Merge with existing data
            $merged = $this->mergeRecursive($session->getExtractedData(), $nested);
            $session->setExtractedData($merged);
        } else {
            $this->updateSessionFromConversation($session);
        }

        $this->hydrateEntitiesFromSession($session);

        $phaseComplete = (bool) ($llmJson['phaseComplete'] ?? false);
        $nextPhase = is_string($llmJson['nextPhase'] ?? null) ? $llmJson['nextPhase'] : null;

        $completeness = $this->calculateCompleteness($session, $currentPhase);
        $missingFields = $this->getMissingFields($session, $currentPhase);

        if ($phaseComplete || $missingFields === []) {
            $advancedTo = $this->advancePhase($session, $nextPhase);
            if ($advancedTo !== null) {
                $currentPhase = $advancedTo;
                $completeness = $this->calculateCompleteness($session, $currentPhase);
                $missingFields = $this->getMissingFields($session, $currentPhase);
            }
        }

        $session->setCompleteness($completeness);
        $this->persistSession($session);

        return [
            'phase' => $session->getPhase(),
            'completeness' => $completeness,
            'missingFields' => $this->requiredFieldsHelper->toDisplayLabels($missingFields),
            'extractedData' => $session->getExtractedData(),
            'contactSummary' => $this->buildContactSummary($session),
            'accountSummary' => $this->buildAccountSummary($session),
        ];
    }

    public function calculateCompleteness(OnboardingSession $session, ?string $phase = null): float
    {
        $phase ??= $session->getPhase();
        $requiredFields = $this->requiredFieldsHelper->getRequiredFields()[$phase] ?? [];

        if ($requiredFields === []) {
            return 100.0;
        }

        $missing = $this->getMissingFields($session, $phase);
        $completed = count($requiredFields) - count($missing);

        return round(($completed / count($requiredFields)) * 100, 1);
    }

    /**
     * @return list<string>
     */
    public function getMissingFields(OnboardingSession $session, ?string $phase = null): array
    {
        $phase ??= $session->getPhase();

        return $this->requiredFieldsHelper->getMissingFields($session->getExtractedData(), $phase);
    }

    /**
     * @return list<string>
     */
    public function getMissingFieldLabels(OnboardingSession $session, ?string $phase = null): array
    {
        return $this->requiredFieldsHelper->toDisplayLabels($this->getMissingFields($session, $phase));
    }

    public function advancePhase(OnboardingSession $session, ?string $requestedNextPhase = null): ?string
    {
        if ($requestedNextPhase !== null && in_array($requestedNextPhase, self::PHASE_SEQUENCE, true)) {
            $session->setPhase($requestedNextPhase);

            return $requestedNextPhase;
        }

        $currentIndex = array_search($session->getPhase(), self::PHASE_SEQUENCE, true);
        if ($currentIndex === false) {
            return null;
        }

        $nextPhase = self::PHASE_SEQUENCE[$currentIndex + 1] ?? null;
        if ($nextPhase === null) {
            return null;
        }

        $session->setPhase($nextPhase);

        return $nextPhase;
    }

    /**
     * @return array{contact: array<string, mixed>, account: array<string, mixed>}
     */
    public function buildEntitySummaries(OnboardingSession $session): array
    {
        return [
            'contact' => $this->buildContactSummary($session),
            'account' => $this->buildAccountSummary($session),
        ];
    }

    /**
     * @return list<string>
     */
    public function getPhaseSequence(): array
    {
        return self::PHASE_SEQUENCE;
    }

    public function createContactFromSession(OnboardingSession $session): Contact
    {
        $contact = $session->getContact() ?? new Contact();
        $data = $this->getCurrentExtractedData($session);

        // Use getStringValue() to handle provenance format transparently
        $firstname = $this->getStringValue($data, 'client.prenom') ?? $this->getStringValue($data, 'firstname');
        if ($firstname !== null) {
            $contact->setFirstname($firstname);
        }

        $lastname = $this->getStringValue($data, 'client.nom') ?? $this->getStringValue($data, 'lastname');
        if ($lastname !== null) {
            $contact->setLastname($lastname);
        } elseif ($contact->getLastname() === '' && $contact->getFirstname() !== null) {
            $contact->setLastname('A completer');
        }

        $email = $this->getStringValue($data, 'client.email') ?? $this->getStringValue($data, 'email');
        if ($email !== null) {
            $contact->setEmail($email);
        }

        $phone = $this->getStringValue($data, 'client.phone') ?? $this->getStringValue($data, 'phone');
        if ($phone !== null) {
            $contact->setPhone($phone);
        }

        $gsm = $this->getStringValue($data, 'client.gsm');
        if ($gsm !== null) {
            $contact->setGsm($gsm);
        }

        $profession = $this->getStringValue($data, 'client.profession')
            ?? $this->getStringValue($data, 'client.pro')
            ?? $this->getStringValue($data, 'profession');
        if ($profession !== null) {
            $contact->setProfession($profession);
        }

        $income = $this->getNumericValue($data, 'client.income') ?? $this->getNumericValue($data, 'incomeAmount');
        if ($income !== null) {
            $contact->setIncomeAmount($income);
        }

        $eid = $this->getStringValue($data, 'client.eid') ?? $this->getStringValue($data, 'eid');
        if ($eid !== null) {
            $contact->setEid($eid);
        }

        $niss = $this->getStringValue($data, 'client.niss') ?? $this->getStringValue($data, 'niss');
        if ($niss !== null) {
            $contact->setNiss($niss);
        }

        $birthplace = $this->getStringValue($data, 'client.birthplace') ?? $this->getStringValue($data, 'birthplace');
        if ($birthplace !== null) {
            $contact->setBirthplace($birthplace);
        }

        $birthdate = $this->getStringValue($data, 'client.birthdate') ?? $this->getStringValue($data, 'birthdate');
        if ($birthdate !== null) {
            try {
                $contact->setBirthdate(new \DateTimeImmutable($birthdate));
            } catch (\Throwable) {
            }
        }

        $address = $this->getStringValue($data, 'client.adresse') ?? $this->getStringValue($data, 'address');
        if ($address !== null) {
            $contact->setStreetNum($address);
        }

        $city = $this->getStringValue($data, 'client.ville');
        if ($city !== null) {
            $contact->setCity($city);
        }

        $postalCode = $this->getStringValue($data, 'client.code_postal');
        if ($postalCode !== null) {
            $contact->setZip($postalCode);
        }

        $country = $this->getStringValue($data, 'client.pays');
        if ($country !== null) {
            $contact->setCountry($country);
        }

        $maritalStatus = $this->getStringValue($data, 'client.statut') ?? $this->getStringValue($data, 'maritalStatus');
        if ($maritalStatus !== null) {
            $contact->setMaritalStatus($this->normalizeMaritalStatus($maritalStatus));
        }

        $session->setContact($contact);

        return $contact;
    }

    public function createAccountFromSession(OnboardingSession $session): Account
    {
        $account = $session->getAccount() ?? new Account();
        $data = $this->getCurrentExtractedData($session);
        $client = $data['client'] ?? [];
        $projects = $data['projets'] ?? [];
        $patrimoine = $data['patrimoine'] ?? [];

        if (is_string($data['account']['name'] ?? null)) {
            $account->setName($data['account']['name']);
        } elseif ($account->getName() === '' || $account->getName() === 'A completer') {
            $displayName = trim((string) (($client['prenom'] ?? '') . ' ' . ($client['nom'] ?? '')));
            $account->setName($displayName !== '' ? $displayName : 'A completer');
        }

        if (is_string($data['account']['type'] ?? null)) {
            $account->setType($data['account']['type']);
        } elseif (is_string($data['type'] ?? null)) {
            $account->setType($data['type']);
        }

        if (is_string($data['account']['company_statut'] ?? null)) {
            $account->setCompanyStatut($data['account']['company_statut']);
        } elseif (is_string($data['companyStatut'] ?? null)) {
            $account->setCompanyStatut($data['companyStatut']);
        }

        $noteChunks = [];
        if (is_string($client['attente'] ?? null)) {
            $noteChunks[] = 'Attente: ' . $client['attente'];
        }
        if (is_string($projects['vision'] ?? null)) {
            $noteChunks[] = 'Vision: ' . $projects['vision'];
        }
        if (is_array($projects['objectifs'] ?? null) && $projects['objectifs'] !== []) {
            $noteChunks[] = 'Objectifs: ' . implode(', ', array_map('strval', $projects['objectifs']));
        }
        if (is_array($patrimoine['dettes'] ?? null) && $patrimoine['dettes'] !== []) {
            $noteChunks[] = 'Dettes: ' . json_encode($patrimoine['dettes'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        if ($noteChunks !== []) {
            $account->setNotes(implode("\n", $noteChunks));
        }

        if ($contact = $session->getContact()) {
            if (!$account->getContacts()->contains($contact)) {
                $account->addContact($contact);
            }
        }

        $session->setAccount($account);

        return $account;
    }

    public function persistSession(OnboardingSession $session): void
    {
        $this->entityManager->persist($session);

        if ($contact = $session->getContact()) {
            $this->entityManager->persist($contact);
        }

        if ($account = $session->getAccount()) {
            $this->entityManager->persist($account);

            foreach ($account->getProducts() as $product) {
                $this->entityManager->persist($product);
            }
        }

        $this->entityManager->flush();
    }

    public function updateSessionFromConversation(OnboardingSession $session): void
    {
        $messages = $session->getMessages();

        $contactData = $this->dataExtraction->extractContactData($messages);
        $accountData = $this->dataExtraction->extractAccountData($messages);

        $fallback = [
            'client' => [
                'prenom' => $contactData['firstname'] ?? null,
                'nom' => $contactData['lastname'] ?? null,
                'email' => $contactData['email'] ?? null,
                'phone' => $contactData['phone'] ?? null,
                'birthdate' => $contactData['birthdate'] ?? null,
                'birthplace' => $contactData['birthplace'] ?? null,
                'niss' => $contactData['niss'] ?? null,
                'profession' => $contactData['profession'] ?? null,
                'statut' => $contactData['maritalStatus'] ?? null,
                'income' => $contactData['incomeAmount'] ?? null,
                'adresse' => $contactData['address'] ?? null,
            ],
            'account' => [
                'name' => $accountData['name'] ?? null,
                'company_statut' => $accountData['companyStatut'] ?? null,
                'type' => $accountData['type'] ?? null,
            ],
        ];

        $fallback = $this->filterNestedNulls($fallback);
        if ($fallback !== []) {
            $this->trackFieldUpdates($session, $fallback, FieldEdit::SOURCE_DETECTED);
            $session->setExtractedData($this->mergeRecursive($session->getExtractedData(), $fallback));
        }

        $this->hydrateEntitiesFromSession($session);
    }

    public function updateSessionField(
        OnboardingSession $session,
        string $fieldPath,
        mixed $value,
        string $source = FieldEdit::SOURCE_UPDATED,
        ?User $author = null,
        ?string $reason = null,
        ?string $role = null,
    ): array {
        $effectiveAuthor = $source === FieldEdit::SOURCE_DETECTED ? null : ($author ?? $session->getUser());
        $provenance = $this->provenanceService->trackFieldUpdate(
            $session,
            $fieldPath,
            $value,
            $source,
            $effectiveAuthor,
            $reason,
            $role,
        );

        $data = $session->getExtractedData();
        $this->provenanceService->setValueByPath($data, $fieldPath, $value);
        $session->setExtractedData($data);

        $this->hydrateEntitiesFromSession($session);
        $this->persistSession($session);

        return $provenance;
    }

    public function saveSessionData(OnboardingSession $session): void
    {
        $this->hydrateEntitiesFromSession($session);
        $this->persistSession($session);
    }

    public function completeSession(OnboardingSession $session): void
    {
        $this->saveSessionData($session);
        $session->setStatus(OnboardingSession::STATUS_COMPLETED);
        $this->entityManager->flush();
    }

    private function hydrateEntitiesFromSession(OnboardingSession $session): void
    {
        $this->createContactFromSession($session);
        $this->createAccountFromSession($session);
        $this->syncProductsFromSession($session);
    }

    /**
     * @param array<string, mixed> $flat
     *
     * @return array<string, mixed>
     */
    private function convertDotNotationToNestedArray(array $flat): array
    {
        $nested = [];

        foreach ($flat as $path => $value) {
            if (!is_string($path) || $path === '') {
                continue;
            }

            $segments = explode('.', $path);
            $current = &$nested;

            foreach ($segments as $index => $segment) {
                if ($segment === '') {
                    continue 2;
                }

                if ($index === count($segments) - 1) {
                    $current[$segment] = $value;
                    continue;
                }

                if (!isset($current[$segment]) || !is_array($current[$segment])) {
                    $current[$segment] = [];
                }

                $current = &$current[$segment];
            }

            unset($current);
        }

        return $nested;
    }

    /**
     * @param array<string, mixed> $base
     * @param array<string, mixed> $incoming
     *
     * @return array<string, mixed>
     */
    private function mergeRecursive(array $base, array $incoming): array
    {
        foreach ($incoming as $key => $value) {
            if (is_array($value) && isset($base[$key]) && is_array($base[$key])) {
                $base[$key] = $this->mergeRecursive($base[$key], $value);
                continue;
            }

            $base[$key] = $value;
        }

        return $base;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private function filterNestedNulls(array $data): array
    {
        $filtered = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = $this->filterNestedNulls($value);
            }

            if ($value === null || $value === [] || $value === '') {
                continue;
            }

            $filtered[$key] = $value;
        }

        return $filtered;
    }

    private function normalizeMaritalStatus(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value;
        }

        if (!is_string($value)) {
            return null;
        }

        return match (mb_strtolower(trim($value))) {
            'celibataire', 'célibataire' => 1,
            'marie', 'marié', 'mariee', 'mariée' => 2,
            'divorce', 'divorcé', 'divorcee', 'divorcée' => 3,
            'veuf', 'veuve' => 4,
            default => null,
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function buildContactSummary(OnboardingSession $session): array
    {
        $contact = $session->getContact();
        if (!$contact instanceof Contact) {
            return [];
        }

        return array_filter([
            'prenom' => $contact->getFirstname(),
            'nom' => $contact->getLastname() !== 'A completer' ? $contact->getLastname() : null,
            'email' => $contact->getEmail(),
            'telephone' => $contact->getPhone(),
            'profession' => $contact->getProfession(),
            'ville' => $contact->getCity(),
        ], static fn(mixed $value): bool => $value !== null && $value !== '');
    }

    /**
     * @return array<string, mixed>
     */
    private function buildAccountSummary(OnboardingSession $session): array
    {
        $account = $session->getAccount();
        if (!$account instanceof Account) {
            return [];
        }

        return array_filter([
            'nom' => $account->getName() !== 'A completer' ? $account->getName() : null,
            'type' => $account->getType(),
            'statut' => $account->getCompanyStatut(),
            'contacts' => $account->getContacts()->count(),
            'produits bancaires' => count($account->getBankProducts()),
            'credits' => count($account->getCreditProducts()),
            'epargne' => count($account->getSavingsProducts()),
            'fiscal' => count($account->getFiscalProducts()),
        ], static fn(mixed $value): bool => $value !== null && $value !== '');
    }

    private function syncProductsFromSession(OnboardingSession $session): void
    {
        $account = $session->getAccount();
        if (!$account instanceof Account) {
            return;
        }

        $patrimoine = $this->getCurrentExtractedData($session)['patrimoine'] ?? null;
        if (!is_array($patrimoine)) {
            return;
        }

        $this->removeManagedProducts($account);

        foreach ($this->buildBankProducts($patrimoine) as $product) {
            $account->addProduct($product);
            $this->entityManager->persist($product);
        }

        foreach ($this->buildCreditProducts($patrimoine) as $product) {
            $account->addProduct($product);
            $this->entityManager->persist($product);
        }

        foreach ($this->buildSavingsProducts($patrimoine) as $product) {
            $account->addProduct($product);
            $this->entityManager->persist($product);
        }

        foreach ($this->buildFiscalProducts($patrimoine) as $product) {
            $account->addProduct($product);
            $this->entityManager->persist($product);
        }
    }

    private function removeManagedProducts(Account $account): void
    {
        foreach ($account->getProducts()->toArray() as $product) {
            if (!$product instanceof MetaProduct) {
                continue;
            }

            if (!str_contains((string) $product->getNotes(), self::ONBOARDING_PRODUCT_MARKER)) {
                continue;
            }

            $account->removeProduct($product);

            if ($product->getAccounts()->count() === 0) {
                $this->entityManager->remove($product);
            }
        }
    }

    /**
     * @return list<BankProduct>
     */
    private function buildBankProducts(array $patrimoine): array
    {
        $items = $this->extractProductItems($patrimoine, ['bank_products', 'comptes']);
        $products = [];

        foreach ($items as $item) {
            if (!$this->hasMeaningfulSourceData($item, ['montant', 'solde'])) {
                continue;
            }

            $product = new BankProduct();
            $this->hydrateBaseProduct($product, $item, 'Compte / trésorerie');
            $product->setAmount($this->normalizeDecimal($item['montant'] ?? $item['solde'] ?? null));

            $products[] = $product;
        }

        return $products;
    }

    /**
     * @return list<CreditProduct>
     */
    private function buildCreditProducts(array $patrimoine): array
    {
        $items = $this->extractProductItems($patrimoine, ['credit_products', 'credits', 'dettes']);
        $products = [];

        foreach ($items as $item) {
            if (!$this->hasMeaningfulSourceData($item, ['montant', 'capital_restant', 'mensualite', 'objet', 'finalite'])) {
                continue;
            }

            $product = new CreditProduct();
            $this->hydrateBaseProduct($product, $item, 'Crédit');
            $product->setAmount($this->normalizeDecimal($item['montant'] ?? $item['capital_restant'] ?? null));
            $product->setRecurrentPrimeAmount($this->normalizeDecimal($item['mensualite'] ?? $item['versement'] ?? null));
            $product->setPurpose($this->stringOrNull($item['objet'] ?? $item['finalite'] ?? null));
            $product->setGarantee($this->stringOrNull($item['garantie'] ?? null));
            $product->setVariability($this->stringOrNull($item['variabilite'] ?? $item['type_taux'] ?? null));
            $product->setDuration($this->normalizeDecimal($item['duree'] ?? null));
            $product->setPaymentDate($this->stringOrNull($item['date_paiement'] ?? null));
            $product->setStartDate($this->normalizeDate($item['debut'] ?? $item['start_date'] ?? null));
            $product->setEndDate($this->normalizeDate($item['fin'] ?? $item['end_date'] ?? null));

            $products[] = $product;
        }

        return $products;
    }

    /**
     * @return list<SavingsProduct>
     */
    private function buildSavingsProducts(array $patrimoine): array
    {
        $items = $this->extractProductItems($patrimoine, ['savings_products', 'epargne_products', 'financier', 'placements']);
        $products = [];

        foreach ($items as $item) {
            if (!$this->hasMeaningfulSourceData($item, ['montant', 'valeur', 'versement_periodique', 'capital_terme', 'reserve'])) {
                continue;
            }

            $product = new SavingsProduct();
            $this->hydrateBaseProduct($product, $item, 'Épargne / placement');
            $product->setAmount($this->normalizeDecimal($item['montant'] ?? $item['valeur'] ?? null));
            $product->setRecurrentPrimeAmount($this->normalizeDecimal($item['versement_periodique'] ?? $item['mensualite'] ?? null));
            $product->setPrimeRecurence($this->stringOrNull($item['prime_recurrence'] ?? $item['frequence'] ?? null));
            $product->setDuration($this->normalizeDecimal($item['duree'] ?? null));
            $product->setCapitalTerme($this->normalizeDecimal($item['capital_terme'] ?? null));
            $product->setGarantee($this->stringOrNull($item['garantie'] ?? null));
            $product->setPaymentDate($this->stringOrNull($item['date_paiement'] ?? null));
            $product->setPaymentDeadline($this->stringOrNull($item['echeance_paiement'] ?? null));
            $product->setReserve($this->normalizeDecimal($item['reserve'] ?? null));
            $product->setReserveDate($this->normalizeDate($item['date_reserve'] ?? null));
            $product->setStartDate($this->normalizeDate($item['debut'] ?? $item['start_date'] ?? null));
            $product->setEndDate($this->normalizeDate($item['fin'] ?? $item['end_date'] ?? null));

            $products[] = $product;
        }

        return $products;
    }

    /**
     * @return list<FiscalProduct>
     */
    private function buildFiscalProducts(array $patrimoine): array
    {
        $items = $this->extractProductItems($patrimoine, ['fiscal_products', 'produits_fiscaux', 'fiscal']);
        $products = [];

        foreach ($items as $item) {
            if (!$this->hasMeaningfulSourceData($item, ['versement_periodique', 'mensualite', 'capital_terme', 'reserve'])) {
                continue;
            }

            $product = new FiscalProduct();
            $this->hydrateBaseProduct($product, $item, 'Produit fiscal');
            $product->setRecurrentPrimeAmount($this->normalizeDecimal($item['versement_periodique'] ?? $item['mensualite'] ?? null));
            $product->setCapitalTerme($this->normalizeDecimal($item['capital_terme'] ?? null));
            $product->setGarantee($this->stringOrNull($item['garantie'] ?? null));
            $product->setPaymentDate($this->stringOrNull($item['date_paiement'] ?? null));
            $product->setPaymentDeadline($this->stringOrNull($item['echeance_paiement'] ?? null));
            $product->setReserve($this->normalizeDecimal($item['reserve'] ?? null));
            $product->setReserveDate($this->normalizeDate($item['date_reserve'] ?? null));
            $product->setStartDate($this->normalizeDate($item['debut'] ?? $item['start_date'] ?? null));
            $product->setEndDate($this->normalizeDate($item['fin'] ?? $item['end_date'] ?? null));

            $products[] = $product;
        }

        return $products;
    }

    /**
     * @param list<string> $keys
     *
     * @return list<array<string, mixed>>
     */
    private function extractProductItems(array $patrimoine, array $keys): array
    {
        foreach ($keys as $key) {
            $value = $patrimoine[$key] ?? null;

            if (is_array($value) && $this->isListOfArrays($value)) {
                return $value;
            }
        }

        return [];
    }

    private function hydrateBaseProduct(MetaProduct $product, array $item, string $fallbackType): void
    {
        $product->setCompany($this->stringOrNull($item['banque'] ?? $item['compagnie'] ?? $item['etablissement'] ?? null));
        $product->setType($this->stringOrNull($item['type'] ?? $fallbackType));
        $product->setDescription($this->stringOrNull($item['libelle'] ?? $item['nom'] ?? $item['description'] ?? null));
        $product->setNumber($this->stringOrNull($item['numero'] ?? $item['reference'] ?? null));
        $product->setReferences($this->stringOrNull($item['reference'] ?? $item['numero'] ?? null));
        $product->setTauxInteret($this->normalizeDecimal($item['taux'] ?? $item['taux_interet'] ?? null));

        $notes = [self::ONBOARDING_PRODUCT_MARKER];

        foreach (['notes', 'precision', 'commentaire'] as $key) {
            if (is_string($item[$key] ?? null) && trim($item[$key]) !== '') {
                $notes[] = trim($item[$key]);
            }
        }

        $product->setNotes(implode("\n", $notes));
    }

    /**
     * @param list<string> $extraKeys
     */
    private function hasMeaningfulSourceData(array $item, array $extraKeys = []): bool
    {
        $keys = array_merge([
            'banque',
            'compagnie',
            'etablissement',
            'numero',
            'reference',
            'libelle',
            'nom',
            'description',
            'taux',
            'taux_interet',
        ], $extraKeys);

        foreach ($keys as $key) {
            $value = $item[$key] ?? null;

            if (is_string($value) && trim($value) !== '') {
                return true;
            }

            if (is_numeric($value)) {
                return true;
            }
        }

        return false;
    }

    private function isListOfArrays(array $value): bool
    {
        if ($value === []) {
            return false;
        }

        foreach ($value as $item) {
            if (!is_array($item)) {
                return false;
            }
        }

        return true;
    }

    private function stringOrNull(mixed $value): ?string
    {
        if (!is_scalar($value)) {
            return null;
        }

        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }

    private function normalizeDecimal(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value) || is_float($value)) {
            return number_format((float) $value, 2, '.', '');
        }

        if (!is_string($value)) {
            return null;
        }

        $normalized = str_replace([' ', "\xc2\xa0"], '', trim($value));
        $normalized = str_replace(',', '.', $normalized);
        $normalized = preg_replace('/[^0-9.\-]/', '', $normalized);

        if ($normalized === null || $normalized === '' || !is_numeric($normalized)) {
            return null;
        }

        return number_format((float) $normalized, 2, '.', '');
    }

    private function normalizeDate(mixed $value): ?\DateTimeImmutable
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return new \DateTimeImmutable($value);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Track field updates with provenance (EP03).
     * Recursively walks through new data and creates FieldEdit entries for each change.
     */
    private function trackFieldUpdates(
        OnboardingSession $session,
        array $newData,
        string $source = FieldEdit::SOURCE_DECLARED,
        string $prefix = '',
    ): void {
        foreach ($newData as $key => $value) {
            $path = $prefix ? "{$prefix}.{$key}" : $key;

            if (is_array($value) && !empty($value)) {
                // Recurse into nested structures
                $this->trackFieldUpdates($session, $value, $source, $path);
            } else {
                // Track this field update
                $this->provenanceService->trackFieldUpdate(
                    $session,
                    $path,
                    $value,
                    $source,
                    $source === FieldEdit::SOURCE_DETECTED ? null : $session->getUser(),
                );
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function getCurrentExtractedData(OnboardingSession $session): array
    {
        return $this->provenanceService->extractCurrentValues($session->getExtractedData());
    }

    /**
     * Get current values from extractedData, handling both old format and new provenance format.
     * Transparently reads from 'current' key if present, otherwise uses value directly.
     */
    private function getCurrentValue(mixed $data): mixed
    {
        if (
            is_array($data)
            && array_key_exists('current', $data)
            && array_key_exists('source', $data)
            && array_key_exists('history', $data)
        ) {
            return $data['current'];
        }

        return $data;
    }

    /**
     * Extract a string value by dot-notation path, handling provenance format transparently.
     * e.g., "client.prenom" from { "client": { "prenom": { current: "Jean", ... } } }
     */
    private function getStringValue(array $data, string $path): ?string
    {
        $keys = explode('.', $path);
        $current = $data;

        foreach ($keys as $key) {
            if (!is_array($current) || !array_key_exists($key, $current)) {
                return null;
            }
            $current = $current[$key];
        }

        $value = $this->getCurrentValue($current);
        return is_string($value) ? $value : null;
    }

    /**
     * Extract a numeric value by dot-notation path, handling provenance format transparently.
     */
    private function getNumericValue(array $data, string $path): ?int
    {
        $keys = explode('.', $path);
        $current = $data;

        foreach ($keys as $key) {
            if (!is_array($current) || !array_key_exists($key, $current)) {
                return null;
            }
            $current = $current[$key];
        }

        $value = $this->getCurrentValue($current);
        return is_numeric($value) ? (int) $value : null;
    }
}
