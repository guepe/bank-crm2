<?php

namespace App\Service;

use App\Entity\OnboardingSession;

interface AiChatServiceInterface
{
    /**
     * Send messages to the AI and return the structured PLANILIFE payload.
     */
    public function chat(array $messages, ?string $systemPrompt = null): array;

    /**
     * Stream the assistant response, then yield the final structured payload.
     *
     * Each yielded value is an array:
     * - ['event' => 'token', 'data' => '...']
     * - ['event' => 'final', 'data' => [...]]
     * - ['event' => 'error', 'data' => '...']
     */
    public function streamChat(array $messages, string $systemPrompt): \Generator;

    /**
     * Build the dynamic system prompt for a given onboarding session and phase.
     */
    public function buildSystemPrompt(OnboardingSession $session, string $phase): string;

    /**
     * Get the system prompt for the onboarding conversation
     */
    public function getSystemPrompt(): string;
}
