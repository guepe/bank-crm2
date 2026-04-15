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
- chaque produit doit idéalement contenir: banque, type, libelle, numero, montant, taux, mensualite, duree, debut, fin, garantie, objet, reserve selon ce qui est connu
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
            OnboardingSession::PHASE_DISCOVERY => 'Comprendre qui est le client: identité simple, situation familiale, profession, attente principale.',
            OnboardingSession::PHASE_QUALIFICATION => 'Faire émerger la vision à long terme, l’âge de retraite visé, les objectifs prioritaires et les freins.',
            OnboardingSession::PHASE_RISK_ANALYSIS => 'Explorer le profil de risque, la sensibilité aux variations, et les enjeux de transmission.',
            OnboardingSession::PHASE_ETAPES => 'Lister les grandes étapes de vie ou projets à venir, leurs délais, leur certitude, et l’étape clé.',
            OnboardingSession::PHASE_PATRIMOINE => "Cartographier le patrimoine global: immobilier, société, trésorerie, financier, dettes, flux futurs.\nQuand les bases sont connues, terminer la phase en demandant les informations bancaires exploitables pour alimenter les produits: banques utilisées, comptes, épargnes, crédits, assurances épargne, produits fiscaux.\nPose une seule question à la fois mais cherche progressivement pour chaque produit: banque, libellé, numéro/référence si connu, montant, mensualité ou versement, taux, durée, dates, garantie, finalité.\nSi le client n'a pas un type de produit, fais-le préciser naturellement et accepte un tableau vide pour ce type.",
            default => 'Poursuivre l’entretien de manière naturelle.',
        };
    }
}
