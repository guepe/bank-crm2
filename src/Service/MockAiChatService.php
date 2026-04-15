<?php

namespace App\Service;

use App\Entity\OnboardingSession;

/**
 * Mock AI service for development fallback.
 */
class MockAiChatService implements AiChatServiceInterface
{
    public function __construct(
        private readonly OnboardingServiceRequiredFields $requiredFieldsHelper = new OnboardingServiceRequiredFields(),
    ) {
    }

    public function chat(array $messages, ?string $systemPrompt = null): array
    {
        $userMessages = array_values(array_filter($messages, static fn(array $message): bool => ($message['role'] ?? null) === 'user'));
        $lastUserMessage = trim((string) (($userMessages[count($userMessages) - 1]['content'] ?? '') ?: ''));

        $fieldGuess = [];
        if ($lastUserMessage !== '') {
            $fieldGuess['client.reponse_libre'] = $lastUserMessage;
        }

        return [
            'message' => $lastUserMessage === ''
                ? 'Bonjour, commençons simplement: quel est votre prénom?'
                : 'Bien noté. Pouvez-vous continuer avec l’élément le plus important pour vous en ce moment?',
            'extractedFields' => $fieldGuess,
            'phaseComplete' => false,
            'nextPhase' => null,
        ];
    }

    public function streamChat(array $messages, string $systemPrompt): \Generator
    {
        $payload = $this->chat($messages, $systemPrompt);

        foreach (preg_split('/(\s+)/u', $payload['message'], -1, PREG_SPLIT_DELIM_CAPTURE) ?: [] as $token) {
            if ($token === '') {
                continue;
            }

            yield [
                'event' => 'token',
                'data' => $token,
            ];
        }

        yield [
            'event' => 'final',
            'data' => $payload,
        ];
    }

    public function buildSystemPrompt(OnboardingSession $session, string $phase): string
    {
        $missing = $this->requiredFieldsHelper->getMissingFields($session->getExtractedData(), $phase);

        return sprintf(
            "Mock PLANILIFE prompt. Phase=%s. Missing=%s",
            $phase,
            $missing === [] ? 'none' : implode(', ', $missing)
        );
    }

    public function getSystemPrompt(): string
    {
        return 'Tu es un assistant d onboarding client PLANILIFE.';
    }
}
