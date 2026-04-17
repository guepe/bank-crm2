<?php

namespace App\Service;

use App\Entity\BankProduct;
use App\Entity\CreditProduct;
use App\Entity\Document;
use App\Entity\FiscalProduct;
use App\Entity\MetaProduct;
use App\Entity\SavingsProduct;
use Symfony\Component\Process\Process;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ProductDocumentAnalyzer
{
    private const MODEL = 'gpt-4o-mini';

    public function __construct(
        private readonly DocumentStorage $documentStorage,
        private readonly HttpClientInterface $httpClient,
        private readonly string $openaiApiKey = '',
        private readonly int $timeout = 30,
    ) {
    }

    /**
     * @return array{message:string,applied_fields:list<string>}
     */
    public function analyzeAndApply(MetaProduct $product, Document $document): array
    {
        $absolutePath = $document->getPath() !== null ? $this->documentStorage->getAbsolutePath($document->getPath()) : null;
        if ($absolutePath === null || !is_file($absolutePath)) {
            return [
                'message' => 'Le document est bien lie au produit, mais son fichier n a pas pu etre relu.',
                'applied_fields' => [],
            ];
        }

        if ($this->openaiApiKey === '') {
            return [
                'message' => 'Le document est bien lie au produit. L analyse IA n est pas activee sur cet environnement.',
                'applied_fields' => [],
            ];
        }

        $mimeType = (string) ($document->getMimeType() ?? '');

        try {
            if (str_starts_with($mimeType, 'image/')) {
                $suggestedFields = $this->analyzeImage($product, $document, $absolutePath, $mimeType);
            } else {
                $text = $this->extractText($absolutePath, $mimeType);
                if ($text === null || trim($text) === '') {
                    return [
                        'message' => 'Le document est bien lie au produit, mais aucun texte exploitable n a pu etre extrait.',
                        'applied_fields' => [],
                    ];
                }

                $suggestedFields = $this->analyzeText($product, $document, $text);
            }
        } catch (\Throwable) {
            return [
                'message' => 'Le document est bien lie au produit, mais l analyse IA a echoue.',
                'applied_fields' => [],
            ];
        }

        if ($suggestedFields === []) {
            return [
                'message' => 'Le document est bien lie au produit, mais aucune donnee fiable n a pu etre extraite.',
                'applied_fields' => [],
            ];
        }

        $appliedFields = $this->applySuggestedFields($product, $suggestedFields);

        return [
            'message' => $appliedFields === []
                ? 'Le document est bien lie au produit, mais aucune nouvelle propriete n a ete renseignee automatiquement.'
                : 'Le document a ete analyse et certaines proprietes du produit ont ete completees automatiquement.',
            'applied_fields' => $appliedFields,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function analyzeText(MetaProduct $product, Document $document, string $text): array
    {
        $response = $this->httpClient->request('POST', 'https://api.openai.com/v1/chat/completions', [
            'headers' => $this->getHeaders(),
            'json' => [
                'model' => self::MODEL,
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.1,
                'max_tokens' => 900,
                'messages' => [
                    ['role' => 'system', 'content' => $this->buildPrompt($product, $document)],
                    ['role' => 'user', 'content' => "Document a analyser:\n".mb_substr($text, 0, 12000)],
                ],
            ],
            'timeout' => $this->timeout,
        ]);

        $data = $response->toArray(false);
        $content = $data['choices'][0]['message']['content'] ?? null;

        return is_string($content) ? $this->parseFieldPayload($content) : [];
    }

    /**
     * @return array<string, mixed>
     */
    private function analyzeImage(MetaProduct $product, Document $document, string $absolutePath, string $mimeType): array
    {
        $imageBytes = file_get_contents($absolutePath);
        if ($imageBytes === false) {
            return [];
        }

        $response = $this->httpClient->request('POST', 'https://api.openai.com/v1/chat/completions', [
            'headers' => $this->getHeaders(),
            'json' => [
                'model' => self::MODEL,
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.1,
                'max_tokens' => 900,
                'messages' => [
                    ['role' => 'system', 'content' => $this->buildPrompt($product, $document)],
                    [
                        'role' => 'user',
                        'content' => [
                            ['type' => 'text', 'text' => 'Analyse ce document produit et propose uniquement les champs fiables a completer.'],
                            ['type' => 'image_url', 'image_url' => ['url' => sprintf('data:%s;base64,%s', $mimeType, base64_encode($imageBytes))]],
                        ],
                    ],
                ],
            ],
            'timeout' => $this->timeout,
        ]);

        $data = $response->toArray(false);
        $content = $data['choices'][0]['message']['content'] ?? null;

        return is_string($content) ? $this->parseFieldPayload($content) : [];
    }

    private function buildPrompt(MetaProduct $product, Document $document): string
    {
        $productClass = match (true) {
            $product instanceof BankProduct => 'bank_product',
            $product instanceof CreditProduct => 'credit_product',
            $product instanceof FiscalProduct => 'fiscal_product',
            $product instanceof SavingsProduct => 'savings_product',
            default => 'product',
        };

        $allowedFields = match (true) {
            $product instanceof BankProduct => ['number', 'type', 'company', 'description', 'references', 'notes', 'tauxInteret', 'amount'],
            $product instanceof CreditProduct => ['number', 'type', 'company', 'description', 'references', 'notes', 'tauxInteret', 'amount', 'duration', 'variability', 'purpose', 'garantee', 'startDate', 'endDate', 'recurrentPrimeAmount', 'paymentDate'],
            $product instanceof FiscalProduct => ['number', 'type', 'company', 'description', 'references', 'notes', 'tauxInteret', 'recurrentPrimeAmount', 'capitalTerme', 'garantee', 'paymentDate', 'paymentDeadline', 'reserve', 'reserveDate', 'startDate', 'endDate'],
            $product instanceof SavingsProduct => ['number', 'type', 'company', 'description', 'references', 'notes', 'tauxInteret', 'amount', 'duration', 'primeRecurence', 'recurrentPrimeAmount', 'capitalTerme', 'garantee', 'paymentDate', 'paymentDeadline', 'reserve', 'reserveDate', 'startDate', 'endDate'],
            default => ['number', 'type', 'company', 'description', 'references', 'notes', 'tauxInteret'],
        };

        $currentData = json_encode($this->serializeProduct($product), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $allowedJson = json_encode($allowedFields, JSON_UNESCAPED_SLASHES);
        $documentName = $document->getName() ?: 'Document produit';

        return <<<PROMPT
Tu analyses un document lie a un produit CRM bancaire.

Type de produit: {$productClass}
Document: {$documentName}

Etat actuel du produit:
{$currentData}

Tu peux proposer uniquement ces champs:
{$allowedJson}

Reponds uniquement en JSON strict au format:
{
  "message": "resume tres court",
  "fields": {
    "amount": "12500.50",
    "number": "BE12...",
    "startDate": "2026-04-01"
  }
}

Regles:
- n invente jamais une valeur absente ou douteuse
- n utilise que les champs autorises
- pour les dates, utiliser le format YYYY-MM-DD
- pour les montants et taux, utiliser un nombre decimal en chaine
- si aucune donnee fiable n est trouvable, renvoyer "fields": {}
- la cle "notes" ne doit servir qu a ajouter une information vraiment utile et concise issue du document
PROMPT;
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeProduct(MetaProduct $product): array
    {
        $data = [
            'number' => $product->getNumber(),
            'type' => $product->getType(),
            'company' => $product->getCompany(),
            'description' => $product->getDescription(),
            'references' => $product->getReferences(),
            'notes' => $product->getNotes(),
            'tauxInteret' => $product->getTauxInteret(),
        ];

        if ($product instanceof BankProduct) {
            $data['amount'] = $product->getAmount();
        } elseif ($product instanceof CreditProduct) {
            $data += [
                'amount' => $product->getAmount(),
                'duration' => $product->getDuration(),
                'variability' => $product->getVariability(),
                'purpose' => $product->getPurpose(),
                'garantee' => $product->getGarantee(),
                'startDate' => $product->getStartDate()?->format('Y-m-d'),
                'endDate' => $product->getEndDate()?->format('Y-m-d'),
                'recurrentPrimeAmount' => $product->getRecurrentPrimeAmount(),
                'paymentDate' => $product->getPaymentDate(),
            ];
        } elseif ($product instanceof FiscalProduct) {
            $data += [
                'recurrentPrimeAmount' => $product->getRecurrentPrimeAmount(),
                'capitalTerme' => $product->getCapitalTerme(),
                'garantee' => $product->getGarantee(),
                'paymentDate' => $product->getPaymentDate(),
                'paymentDeadline' => $product->getPaymentDeadline(),
                'reserve' => $product->getReserve(),
                'reserveDate' => $product->getReserveDate()?->format('Y-m-d'),
                'startDate' => $product->getStartDate()?->format('Y-m-d'),
                'endDate' => $product->getEndDate()?->format('Y-m-d'),
            ];
        } elseif ($product instanceof SavingsProduct) {
            $data += [
                'amount' => $product->getAmount(),
                'duration' => $product->getDuration(),
                'primeRecurence' => $product->getPrimeRecurence(),
                'recurrentPrimeAmount' => $product->getRecurrentPrimeAmount(),
                'capitalTerme' => $product->getCapitalTerme(),
                'garantee' => $product->getGarantee(),
                'paymentDate' => $product->getPaymentDate(),
                'paymentDeadline' => $product->getPaymentDeadline(),
                'reserve' => $product->getReserve(),
                'reserveDate' => $product->getReserveDate()?->format('Y-m-d'),
                'startDate' => $product->getStartDate()?->format('Y-m-d'),
                'endDate' => $product->getEndDate()?->format('Y-m-d'),
            ];
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $fields
     * @return list<string>
     */
    private function applySuggestedFields(MetaProduct $product, array $fields): array
    {
        $appliedFields = [];

        foreach ($fields as $field => $value) {
            if ($value === null || $value === '' || !is_string($field)) {
                continue;
            }

            $normalized = is_string($value) ? trim($value) : $value;

            switch ($field) {
                case 'number':
                    if ($product->getNumber() === null || trim((string) $product->getNumber()) === '') {
                        $product->setNumber((string) $normalized);
                        $appliedFields[] = 'Numero';
                    }
                    break;
                case 'type':
                    if ($product->getType() === null || trim((string) $product->getType()) === '') {
                        $product->setType((string) $normalized);
                        $appliedFields[] = 'Type';
                    }
                    break;
                case 'company':
                    if ($product->getCompany() === null || trim((string) $product->getCompany()) === '') {
                        $product->setCompany((string) $normalized);
                        $appliedFields[] = 'Societe';
                    }
                    break;
                case 'description':
                    if ($product->getDescription() === null || trim((string) $product->getDescription()) === '') {
                        $product->setDescription((string) $normalized);
                        $appliedFields[] = 'Description';
                    }
                    break;
                case 'references':
                    if ($product->getReferences() === null || trim((string) $product->getReferences()) === '') {
                        $product->setReferences((string) $normalized);
                        $appliedFields[] = 'Reference';
                    }
                    break;
                case 'notes':
                    if ($product->getNotes() === null || trim((string) $product->getNotes()) === '') {
                        $product->setNotes((string) $normalized);
                        $appliedFields[] = 'Notes';
                    }
                    break;
                case 'tauxInteret':
                    if ($product->getTauxInteret() === null || trim((string) $product->getTauxInteret()) === '') {
                        $product->setTauxInteret($this->normalizeDecimal($normalized));
                        $appliedFields[] = 'Taux interet';
                    }
                    break;
                default:
                    $label = $this->applyTypeSpecificField($product, $field, $normalized);
                    if ($label !== null) {
                        $appliedFields[] = $label;
                    }
                    break;
            }
        }

        return array_values(array_unique($appliedFields));
    }

    private function applyTypeSpecificField(MetaProduct $product, string $field, mixed $value): ?string
    {
        if ($product instanceof BankProduct && $field === 'amount' && ($product->getAmount() === null || trim((string) $product->getAmount()) === '')) {
            $product->setAmount($this->normalizeDecimal($value));
            return 'Montant';
        }

        if ($product instanceof CreditProduct) {
            return $this->applyCreditField($product, $field, $value);
        }

        if ($product instanceof FiscalProduct) {
            return $this->applyFiscalField($product, $field, $value);
        }

        if ($product instanceof SavingsProduct) {
            return $this->applySavingsField($product, $field, $value);
        }

        return null;
    }

    private function applyCreditField(CreditProduct $product, string $field, mixed $value): ?string
    {
        return match ($field) {
            'amount' => $this->applyStringIfEmpty($product->getAmount(), fn (string $v) => $product->setAmount($this->normalizeDecimal($v)), $value, 'Montant'),
            'duration' => $this->applyStringIfEmpty($product->getDuration(), fn (string $v) => $product->setDuration($this->normalizeDecimal($v)), $value, 'Duree'),
            'variability' => $this->applyStringIfEmpty($product->getVariability(), fn (string $v) => $product->setVariability($this->normalizeVariability($v)), $value, 'Variabilite'),
            'purpose' => $this->applyStringIfEmpty($product->getPurpose(), fn (string $v) => $product->setPurpose($v), $value, 'Objet'),
            'garantee' => $this->applyStringIfEmpty($product->getGarantee(), fn (string $v) => $product->setGarantee($v), $value, 'Garantie'),
            'recurrentPrimeAmount' => $this->applyStringIfEmpty($product->getRecurrentPrimeAmount(), fn (string $v) => $product->setRecurrentPrimeAmount($this->normalizeDecimal($v)), $value, 'Prime recurrente'),
            'paymentDate' => $this->applyStringIfEmpty($product->getPaymentDate(), fn (string $v) => $product->setPaymentDate($v), $value, 'Date de paiement'),
            'startDate' => $this->applyDateIfEmpty($product->getStartDate(), fn (\DateTimeInterface $v) => $product->setStartDate($v), $value, 'Debut'),
            'endDate' => $this->applyDateIfEmpty($product->getEndDate(), fn (\DateTimeInterface $v) => $product->setEndDate($v), $value, 'Fin'),
            default => null,
        };
    }

    private function applyFiscalField(FiscalProduct $product, string $field, mixed $value): ?string
    {
        return match ($field) {
            'recurrentPrimeAmount' => $this->applyStringIfEmpty($product->getRecurrentPrimeAmount(), fn (string $v) => $product->setRecurrentPrimeAmount($this->normalizeDecimal($v)), $value, 'Prime recurrente'),
            'capitalTerme' => $this->applyStringIfEmpty($product->getCapitalTerme(), fn (string $v) => $product->setCapitalTerme($this->normalizeDecimal($v)), $value, 'Capital terme'),
            'garantee' => $this->applyStringIfEmpty($product->getGarantee(), fn (string $v) => $product->setGarantee($v), $value, 'Garantie'),
            'paymentDate' => $this->applyStringIfEmpty($product->getPaymentDate(), fn (string $v) => $product->setPaymentDate($v), $value, 'Date de paiement'),
            'paymentDeadline' => $this->applyStringIfEmpty($product->getPaymentDeadline(), fn (string $v) => $product->setPaymentDeadline($v), $value, 'Echeance'),
            'reserve' => $this->applyStringIfEmpty($product->getReserve(), fn (string $v) => $product->setReserve($this->normalizeDecimal($v)), $value, 'Reserve'),
            'reserveDate' => $this->applyDateIfEmpty($product->getReserveDate(), fn (\DateTimeInterface $v) => $product->setReserveDate($v), $value, 'Date reserve'),
            'startDate' => $this->applyDateIfEmpty($product->getStartDate(), fn (\DateTimeInterface $v) => $product->setStartDate($v), $value, 'Debut'),
            'endDate' => $this->applyDateIfEmpty($product->getEndDate(), fn (\DateTimeInterface $v) => $product->setEndDate($v), $value, 'Fin'),
            default => null,
        };
    }

    private function applySavingsField(SavingsProduct $product, string $field, mixed $value): ?string
    {
        return match ($field) {
            'amount' => $this->applyStringIfEmpty($product->getAmount(), fn (string $v) => $product->setAmount($this->normalizeDecimal($v)), $value, 'Montant'),
            'duration' => $this->applyStringIfEmpty($product->getDuration(), fn (string $v) => $product->setDuration($this->normalizeDecimal($v)), $value, 'Duree'),
            'primeRecurence' => $this->applyStringIfEmpty($product->getPrimeRecurence(), fn (string $v) => $product->setPrimeRecurence($this->normalizePrimeRecurrence($v)), $value, 'Frequence'),
            'recurrentPrimeAmount' => $this->applyStringIfEmpty($product->getRecurrentPrimeAmount(), fn (string $v) => $product->setRecurrentPrimeAmount($this->normalizeDecimal($v)), $value, 'Prime recurrente'),
            'capitalTerme' => $this->applyStringIfEmpty($product->getCapitalTerme(), fn (string $v) => $product->setCapitalTerme($this->normalizeDecimal($v)), $value, 'Capital terme'),
            'garantee' => $this->applyStringIfEmpty($product->getGarantee(), fn (string $v) => $product->setGarantee($v), $value, 'Garantie'),
            'paymentDate' => $this->applyStringIfEmpty($product->getPaymentDate(), fn (string $v) => $product->setPaymentDate($v), $value, 'Date de paiement'),
            'paymentDeadline' => $this->applyStringIfEmpty($product->getPaymentDeadline(), fn (string $v) => $product->setPaymentDeadline($v), $value, 'Echeance'),
            'reserve' => $this->applyStringIfEmpty($product->getReserve(), fn (string $v) => $product->setReserve($this->normalizeDecimal($v)), $value, 'Reserve'),
            'reserveDate' => $this->applyDateIfEmpty($product->getReserveDate(), fn (\DateTimeInterface $v) => $product->setReserveDate($v), $value, 'Date reserve'),
            'startDate' => $this->applyDateIfEmpty($product->getStartDate(), fn (\DateTimeInterface $v) => $product->setStartDate($v), $value, 'Debut'),
            'endDate' => $this->applyDateIfEmpty($product->getEndDate(), fn (\DateTimeInterface $v) => $product->setEndDate($v), $value, 'Fin'),
            default => null,
        };
    }

    private function applyStringIfEmpty(?string $currentValue, callable $setter, mixed $value, string $label): ?string
    {
        if ($currentValue !== null && trim($currentValue) !== '') {
            return null;
        }

        $normalized = trim((string) $value);
        if ($normalized === '') {
            return null;
        }

        $setter($normalized);

        return $label;
    }

    private function applyDateIfEmpty(?\DateTimeInterface $currentValue, callable $setter, mixed $value, string $label): ?string
    {
        if ($currentValue !== null || !is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            $setter(new \DateTimeImmutable($value));
        } catch (\Throwable) {
            return null;
        }

        return $label;
    }

    /**
     * @return array<string, mixed>
     */
    private function parseFieldPayload(string $content): array
    {
        try {
            $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            return [];
        }

        return is_array($decoded['fields'] ?? null) ? $decoded['fields'] : [];
    }

    private function normalizeDecimal(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = str_replace([' ', ','], ['', '.'], trim((string) $value));

        return is_numeric($normalized) ? $normalized : null;
    }

    private function normalizeVariability(string $value): string
    {
        $normalized = mb_strtolower(trim($value));

        return match (true) {
            str_contains($normalized, 'fix') => 'Fixe',
            str_contains($normalized, 'mix') => 'Mixte',
            default => 'Variable',
        };
    }

    private function normalizePrimeRecurrence(string $value): string
    {
        $normalized = mb_strtolower(trim($value));

        return match (true) {
            str_contains($normalized, 'trim') => 'quarterly',
            str_contains($normalized, 'ann') => 'yearly',
            str_contains($normalized, 'uni') => 'single',
            default => 'monthly',
        };
    }

    /**
     * @return array<string, string>
     */
    private function getHeaders(): array
    {
        return [
            'Authorization' => 'Bearer '.$this->openaiApiKey,
            'Content-Type' => 'application/json',
        ];
    }

    private function extractText(string $absolutePath, string $mimeType): ?string
    {
        if ($mimeType === 'application/pdf' || str_ends_with(strtolower($absolutePath), '.pdf')) {
            return $this->extractPdfText($absolutePath);
        }

        if (
            $mimeType === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            || str_ends_with(strtolower($absolutePath), '.docx')
        ) {
            return $this->extractDocxText($absolutePath);
        }

        if (str_starts_with($mimeType, 'text/') || str_ends_with(strtolower($absolutePath), '.txt')) {
            $content = file_get_contents($absolutePath);
            return $content === false ? null : $content;
        }

        return null;
    }

    private function extractPdfText(string $absolutePath): ?string
    {
        $process = new Process(['pdftotext', $absolutePath, '-']);
        $process->setTimeout(20);
        $process->run();

        if (!$process->isSuccessful()) {
            return null;
        }

        $output = trim($process->getOutput());

        return $output !== '' ? $output : null;
    }

    private function extractDocxText(string $absolutePath): ?string
    {
        if (!class_exists(\ZipArchive::class)) {
            return null;
        }

        $zip = new \ZipArchive();
        if ($zip->open($absolutePath) !== true) {
            return null;
        }

        $content = $zip->getFromName('word/document.xml');
        $zip->close();

        if (!is_string($content) || trim($content) === '') {
            return null;
        }

        $text = html_entity_decode(strip_tags(str_replace('</w:p>', "\n", $content)));
        $text = trim(preg_replace('/\s+/', ' ', $text) ?? '');

        return $text !== '' ? $text : null;
    }
}
