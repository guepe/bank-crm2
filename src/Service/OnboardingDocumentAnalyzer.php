<?php

namespace App\Service;

use App\Entity\Document;
use App\Entity\OnboardingSession;
use Symfony\Component\Process\Process;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OnboardingDocumentAnalyzer
{
    private const MODEL = 'gpt-4o-mini';

    public function __construct(
        private readonly DocumentStorage $documentStorage,
        private readonly HttpClientInterface $httpClient,
        private readonly string $openaiApiKey = '',
        private readonly int $timeout = 30,
    ) {
    }

    public function analyzeDocument(OnboardingSession $session, Document $document): array
    {
        $absolutePath = $document->getPath() !== null ? $this->documentStorage->getAbsolutePath($document->getPath()) : null;
        if ($absolutePath === null || !is_file($absolutePath)) {
            return $this->fallbackPayload('Le document a bien été ajouté, mais je n’ai pas pu le relire automatiquement.');
        }

        $mimeType = (string) ($document->getMimeType() ?? '');
        $phase = $session->getPhase();

        try {
            if (str_starts_with($mimeType, 'image/')) {
                return $this->analyzeImageDocument($absolutePath, $mimeType, $session, $document);
            }

            $text = $this->extractText($absolutePath, $mimeType);
            if ($text === null || trim($text) === '') {
                return $this->fallbackPayload(
                    sprintf(
                        'Le document "%s" est enregistré. Je n’ai pas pu en extraire le texte automatiquement, mais tu peux me décrire les éléments importants.',
                        $document->getName() ?: 'sans nom'
                    )
                );
            }

            return $this->analyzeTextDocument($text, $session, $document);
        } catch (\Throwable $e) {
            return $this->fallbackPayload(
                sprintf(
                    'Le document "%s" est bien enregistré, mais son analyse automatique a échoué. Tu peux continuer dans le chat et me donner les informations clés.',
                    $document->getName() ?: 'sans nom'
                )
            );
        }
    }

    private function analyzeTextDocument(string $text, OnboardingSession $session, Document $document): array
    {
        if ($this->openaiApiKey === '') {
            return $this->fallbackPayload(
                sprintf(
                    'Le document "%s" est ajouté. L’analyse IA n’est pas disponible ici, mais il est prêt à être relié au dossier.',
                    $document->getName() ?: 'sans nom'
                )
            );
        }

        $prompt = $this->buildDocumentPrompt($session, $document);
        $truncatedText = mb_substr($text, 0, 12000);

        $response = $this->httpClient->request('POST', 'https://api.openai.com/v1/chat/completions', [
            'headers' => $this->getHeaders(),
            'json' => [
                'model' => self::MODEL,
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.2,
                'max_tokens' => 900,
                'messages' => [
                    ['role' => 'system', 'content' => $prompt],
                    ['role' => 'user', 'content' => "Document à analyser:\n".$truncatedText],
                ],
            ],
            'timeout' => $this->timeout,
        ]);

        $data = $response->toArray(false);
        $content = $data['choices'][0]['message']['content'] ?? null;

        return is_string($content) ? $this->parseStructuredResponse($content) : $this->fallbackPayload('Le document est ajouté, mais la réponse IA est incomplète.');
    }

    private function analyzeImageDocument(string $absolutePath, string $mimeType, OnboardingSession $session, Document $document): array
    {
        if ($this->openaiApiKey === '') {
            return $this->fallbackPayload(
                sprintf(
                    'L’image "%s" est ajoutée. L’analyse IA n’est pas disponible ici, mais elle est bien enregistrée.',
                    $document->getName() ?: 'sans nom'
                )
            );
        }

        $imageBytes = file_get_contents($absolutePath);
        if ($imageBytes === false) {
            return $this->fallbackPayload('Le document image est ajouté, mais il n’a pas pu être relu automatiquement.');
        }

        $prompt = $this->buildDocumentPrompt($session, $document);
        $dataUrl = sprintf('data:%s;base64,%s', $mimeType, base64_encode($imageBytes));

        $response = $this->httpClient->request('POST', 'https://api.openai.com/v1/chat/completions', [
            'headers' => $this->getHeaders(),
            'json' => [
                'model' => self::MODEL,
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.2,
                'max_tokens' => 900,
                'messages' => [
                    ['role' => 'system', 'content' => $prompt],
                    [
                        'role' => 'user',
                        'content' => [
                            ['type' => 'text', 'text' => 'Analyse ce document client et extrais les données utiles.'],
                            ['type' => 'image_url', 'image_url' => ['url' => $dataUrl]],
                        ],
                    ],
                ],
            ],
            'timeout' => $this->timeout,
        ]);

        $data = $response->toArray(false);
        $content = $data['choices'][0]['message']['content'] ?? null;

        return is_string($content) ? $this->parseStructuredResponse($content) : $this->fallbackPayload('Le document image est ajouté, mais la réponse IA est incomplète.');
    }

    private function buildDocumentPrompt(OnboardingSession $session, Document $document): string
    {
        $phase = $session->getPhase();
        $documentName = $document->getName() ?: 'Document';
        $knownData = json_encode($session->getExtractedData(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $phaseHint = match ($phase) {
            OnboardingSession::PHASE_DISCOVERY => 'Priorité à l’identité, au nom, au prénom, à l’adresse, aux coordonnées et aux éléments de situation personnelle.',
            OnboardingSession::PHASE_QUALIFICATION => 'Priorité aux objectifs, projets, horizons de placement et éléments exprimant une intention financière.',
            OnboardingSession::PHASE_RISK_ANALYSIS => 'Priorité aux informations de risque, volatilité acceptée, garanties, protection, transmission.',
            OnboardingSession::PHASE_ETAPES => 'Priorité aux échéances, durées, dates de fin, jalons de vie ou de contrats.',
            OnboardingSession::PHASE_PATRIMOINE => 'Priorité aux comptes, IBAN, banques, soldes, crédits, épargnes, assurances-vie, produits fiscaux, montants, taux, échéances.',
            default => 'Extraire les informations les plus utiles au dossier.',
        };

        return <<<PROMPT
Tu analyses un document client dans un onboarding patrimonial belge.

Phase courante: {$phase}
Instructions: {$phaseHint}

Données déjà connues:
{$knownData}

Réponds uniquement en JSON strict avec le format:
{
  "message": "message court qui confirme ce que tu as trouvé et pose éventuellement la prochaine question utile",
  "extractedFields": {
    "client.prenom": "Jean",
    "patrimoine.bank_products": [
      {
        "banque": "BNP Paribas Fortis",
        "libelle": "Compte courant",
        "numero": "BE00...",
        "montant": 1520.34,
        "type": "compte courant"
      }
    ]
  },
  "phaseComplete": false,
  "nextPhase": null
}

Règles:
- n’invente jamais une donnée absente du document
- pour les comptes bancaires, chercher banque, IBAN/numéro, type de compte, titulaire, solde si visible
- pour les crédits, utiliser "patrimoine.credit_products"
- pour l’épargne/placements, utiliser "patrimoine.savings_products"
- pour les produits fiscaux, utiliser "patrimoine.fiscal_products"
- si un relevé bancaire ou un RIB est trouvé, renseigner aussi "patrimoine.tresorerie" et/ou "patrimoine.financier" quand c’est pertinent
- les tableaux doivent être complets, même avec un seul produit
- le message doit être court, naturel, et exploitable dans la conversation
- le document s’appelle "{$documentName}"
PROMPT;
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

    private function parseStructuredResponse(string $content): array
    {
        try {
            $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            $decoded = $this->extractJsonObject($content);
        }

        if (!is_array($decoded)) {
            return $this->fallbackPayload(trim($content) !== '' ? trim($content) : 'Le document a été analysé.');
        }

        return [
            'message' => (string) ($decoded['message'] ?? 'Le document a été analysé.'),
            'extractedFields' => is_array($decoded['extractedFields'] ?? null) ? $decoded['extractedFields'] : [],
            'phaseComplete' => (bool) ($decoded['phaseComplete'] ?? false),
            'nextPhase' => isset($decoded['nextPhase']) && is_string($decoded['nextPhase']) && $decoded['nextPhase'] !== '' ? $decoded['nextPhase'] : null,
        ];
    }

    private function extractJsonObject(string $content): ?array
    {
        $start = strpos($content, '{');
        $end = strrpos($content, '}');
        if ($start === false || $end === false || $end <= $start) {
            return null;
        }

        $candidate = substr($content, $start, $end - $start + 1);

        try {
            $decoded = json_decode($candidate, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            return null;
        }

        return is_array($decoded) ? $decoded : null;
    }

    private function fallbackPayload(string $message): array
    {
        return [
            'message' => $message,
            'extractedFields' => [],
            'phaseComplete' => false,
            'nextPhase' => null,
        ];
    }

    private function getHeaders(): array
    {
        return [
            'Authorization' => 'Bearer '.$this->openaiApiKey,
            'Content-Type' => 'application/json',
        ];
    }
}
