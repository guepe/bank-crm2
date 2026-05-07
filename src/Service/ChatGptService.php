<?php

namespace App\Service;

use App\Entity\OnboardingSession;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * OpenAI chat service tailored for PLANILIFE onboarding flows.
 */
class ChatGptService implements AiChatServiceInterface
{
    private string $model = 'gpt-4o-mini';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly OnboardingServiceRequiredFields $requiredFieldsHelper,
        private readonly string $openaiApiKey = '',
        private readonly float $temperature = 0.7,
        private readonly int $maxTokens = 1000,
        private readonly int $timeout = 30,
    ) {
        if ($this->openaiApiKey === '') {
            throw new \RuntimeException('OPENAI_API_KEY environment variable is not set');
        }
    }

    public function chat(array $messages, ?string $systemPrompt = null): array
    {
        try {
            $response = $this->httpClient->request('POST', 'https://api.openai.com/v1/chat/completions', [
                'headers' => $this->getHeaders(),
                'json' => [
                    'model' => $this->model,
                    'messages' => $this->formatMessages($messages, $systemPrompt ?? $this->getSystemPrompt()),
                    'temperature' => $this->temperature,
                    'max_tokens' => $this->maxTokens,
                    'response_format' => ['type' => 'json_object'],
                ],
                'timeout' => $this->timeout,
            ]);

            $data = $response->toArray(false);

            if (isset($data['error'])) {
                throw new \RuntimeException('OpenAI API Error: '.$data['error']['message']);
            }

            $content = $data['choices'][0]['message']['content'] ?? null;
            if (!is_string($content) || $content === '') {
                throw new \RuntimeException('Invalid response from OpenAI API');
            }

            return $this->parseStructuredResponse($content);
        } catch (HttpExceptionInterface $e) {
            throw new \RuntimeException('HTTP Error: '.$e->getMessage(), previous: $e);
        } catch (\Exception $e) {
            throw new \RuntimeException('ChatGPT Service Error: '.$e->getMessage(), previous: $e);
        }
    }

    public function streamChat(array $messages, string $systemPrompt): \Generator
    {
        try {
            $response = $this->httpClient->request('POST', 'https://api.openai.com/v1/chat/completions', [
                'headers' => $this->getHeaders(),
                'json' => [
                    'model' => $this->model,
                    'messages' => $this->formatMessages($messages, $systemPrompt),
                    'temperature' => $this->temperature,
                    'max_tokens' => $this->maxTokens,
                    'response_format' => ['type' => 'json_object'],
                    'stream' => true,
                ],
                'timeout' => $this->timeout,
            ]);

            $buffer = '';
            $fullText = '';

            foreach ($this->httpClient->stream($response) as $chunk) {
                if ($chunk->isTimeout()) {
                    continue;
                }

                $buffer .= $chunk->getContent();

                while (($delimiterPos = strpos($buffer, "\n\n")) !== false) {
                    $rawEvent = substr($buffer, 0, $delimiterPos);
                    $buffer = substr($buffer, $delimiterPos + 2);

                    foreach (explode("\n", $rawEvent) as $line) {
                        $line = trim($line);
                        if ($line === '' || !str_starts_with($line, 'data:')) {
                            continue;
                        }

                        $payload = trim(substr($line, 5));
                        if ($payload === '[DONE]') {
                            continue;
                        }

                        $data = json_decode($payload, true);
                        $token = $data['choices'][0]['delta']['content'] ?? null;
                        if (!is_string($token) || $token === '') {
                            continue;
                        }

                        $fullText .= $token;

                        yield [
                            'event' => 'token',
                            'data' => $token,
                        ];
                    }
                }
            }

            yield [
                'event' => 'final',
                'data' => $this->parseStructuredResponse($fullText),
            ];
        } catch (\Throwable $e) {
            yield [
                'event' => 'error',
                'data' => $e->getMessage(),
            ];
        }
    }

    public function buildSystemPrompt(OnboardingSession $session, string $phase): string
    {
        $phaseInstructions = $this->getPhaseInstructions($phase);
        $currentData = $session->getExtractedData();
        $currentDataJson = json_encode($currentData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $missingFields = $this->requiredFieldsHelper->getMissingFields($currentData, $phase);

        $missingBlock = $missingFields === []
            ? '- Aucun champ obligatoire manquant dans cette phase.'
            : '- '.implode("\n- ", $missingFields);

        return <<<PROMPT
Tu es PLANILIFE, un assistant belge d'onboarding patrimonial.

Objectif:
- mener un entretien conversationnel structuré en 5 phases
- poser une seule vraie question à la fois
- extraire au maximum les informations utiles de la réponse du client
- rester chaleureux, direct, clair, sans donner de conseil financier

Phase courante: {$phase}
Instructions de phase:
{$phaseInstructions}

Données connues:
{$currentDataJson}

Champs manquants prioritaires pour cette phase:
{$missingBlock}

Contraintes de réponse:
- écrire en français belge
- 1 question principale maximum
- 2 à 4 phrases maximum
- si l'utilisateur donne plusieurs infos d'un coup, les confirmer brièvement puis continuer
- ne jamais mentionner la structure JSON
- éviter les listes sauf si indispensable

Tu dois TOUJOURS répondre en JSON strict avec ce format:
{
  "message": "message conversationnel",
  "extractedFields": {
    "client.prenom": "Jean",
    "client.age": 45
  },
  "phaseComplete": false,
  "nextPhase": null
}

Règles d'extraction:
- extraire tout ce qui est clairement dit, même si la question portait sur autre chose
- utiliser des clés en notation pointée
- utiliser des tableaux pour les listes
- utiliser true/false pour les booléens
- pour la phase patrimoine, utiliser si possible ces clés structurées:
  - patrimoine.bank_products
  - patrimoine.credit_products
  - patrimoine.savings_products
  - patrimoine.fiscal_products
  - flux.revenus, flux.charges, flux.epargne_mensuelle (montants mensuels nets)
- chaque produit doit idéalement contenir: banque, type, libelle, numero, montant, taux, mensualite, duree, debut, fin, garantie, objet, reserve selon ce qui est connu
- pour la phase etapes, produire etapes.timeline comme tableau JSON: [{titre, categorie, annee, horizon, notes}]
- si une valeur n'est pas certaine, ne pas l'inventer
PROMPT;
    }

    public function getSystemPrompt(): string
    {
        return 'Tu es PLANILIFE, assistant de planification patrimoniale belge. Pose une question à la fois, conversationnellement, sans conseil financier.';
    }

    private function getHeaders(): array
    {
        return [
            'Authorization' => 'Bearer '.$this->openaiApiKey,
            'Content-Type' => 'application/json',
        ];
    }

    private function formatMessages(array $messages, string $systemPrompt): array
    {
        $formatted = [[
            'role' => 'system',
            'content' => $systemPrompt,
        ]];

        foreach ($messages as $message) {
            $formatted[] = [
                'role' => ($message['role'] ?? 'user') === 'assistant' ? 'assistant' : 'user',
                'content' => (string) ($message['content'] ?? ''),
            ];
        }

        return $formatted;
    }

    private function parseStructuredResponse(string $content): array
    {
        try {
            $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            $decoded = $this->extractJsonObject($content);
        }

        if (!is_array($decoded)) {
            return [
                'message' => trim($content),
                'extractedFields' => [],
                'phaseComplete' => false,
                'nextPhase' => null,
            ];
        }

        return [
            'message' => (string) ($decoded['message'] ?? trim($content)),
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

    private function getPhaseInstructions(string $phase): string
    {
        return match ($phase) {
            OnboardingSession::PHASE_DISCOVERY =>
                "Comprendre qui est le client : identite simple, situation familiale, profession et attente principale.\n"
                ."Champs a capturer : client.prenom, client.age, client.statut (celibataire/marie/pacse/divorce/veuf), client.pro, client.attente (formulation libre de l’attente principale).\n"
                ."Si l’occasion se presente naturellement, noter egalement client.nb_enfants.",

            OnboardingSession::PHASE_QUALIFICATION =>
                "Faire emerger la vision a long terme, l’age de retraite vise, les objectifs prioritaires et les priorites classees.\n"
                ."Champs a capturer : projets.vision, projets.retraite_age, projets.objectifs (liste), projets.priorites "
                ."(liste ordonnee du plus au moins important - demander explicitement ‘Si vous deviez classer vos priorites, dans quel ordre les mettriez-vous ?’).",

            OnboardingSession::PHASE_RISK_ANALYSIS =>
                "Explorer le profil de risque, la sensibilite aux variations, les enjeux de transmission et les valeurs d’investissement.\n"
                ."Champs a capturer : risque.profil (conservateur/modere/dynamique/agressif), risque.transmission, "
                ."risque.valeurs (convictions d’investissement : ESG, immobilier, exclure secteurs, preferences ethiques ou geographiques).",

            OnboardingSession::PHASE_ETAPES =>
                "Lister les grandes etapes de vie ou projets a venir, leurs delais, leur certitude, et l’etape cle.\n"
                ."Champs a capturer : etapes.etapes (liste libre), etapes.etape_cle (formulation libre).\n"
                ."En plus, produire etapes.timeline : un tableau JSON structure avec pour chaque etape un objet "
                ."{titre, categorie (vie|famille|professionnel|patrimoine|financier|prioritaire), annee (nombre ou null), horizon (texte), notes}.\n"
                ."Ce tableau sera utilise directement dans la timeline interactive - le remplir meme partiellement.",

            OnboardingSession::PHASE_PATRIMOINE =>
                "Cartographier le patrimoine global : immobilier, societe, tresorerie, financier, dettes, et flux mensuels.\n"
                ."Champs a capturer : patrimoine.immo, patrimoine.tresorerie, patrimoine.financier, "
                ."ainsi que flux.revenus (revenus nets mensuels totaux), flux.charges (charges fixes mensuelles), "
                ."flux.epargne_mensuelle (capacite d’epargne mensuelle disponible).\n"
                ."Quand les bases sont connues, terminer la phase en demandant les informations bancaires pour alimenter les produits : "
                ."banques utilisees, comptes, epargnes, credits, assurances epargne, produits fiscaux.\n"
                ."Pose une seule question a la fois mais cherche progressivement pour chaque produit : banque, libelle, "
                ."numero/reference si connu, montant, mensualite ou versement, taux, duree, dates, garantie, finalite.\n"
                ."Si le client n’a pas un type de produit, fais-le preciser naturellement et accepte un tableau vide pour ce type.",

            default => "Poursuivre l’entretien de maniere naturelle.",
        };
    }
}
