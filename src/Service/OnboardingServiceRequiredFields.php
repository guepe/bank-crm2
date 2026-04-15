<?php

namespace App\Service;

use App\Entity\OnboardingSession;

/**
 * Shared PLANILIFE phase requirements helper.
 */
class OnboardingServiceRequiredFields
{
    /**
     * @return array<string, string>
     */
    public function getFieldLabels(): array
    {
        return [
            'client.prenom' => 'Prénom du client',
            'client.age' => 'Âge du client',
            'client.statut' => 'Situation familiale',
            'client.pro' => 'Situation professionnelle',
            'projets.vision' => 'Vision patrimoniale',
            'projets.retraite_age' => 'Âge de retraite visé',
            'projets.objectifs' => 'Objectifs principaux',
            'risque.profil' => 'Profil de risque',
            'risque.transmission' => 'Projet de transmission',
            'etapes.etapes' => 'Grandes étapes à venir',
            'etapes.etape_cle' => 'Étape prioritaire',
            'patrimoine.immo' => 'Biens immobiliers',
            'patrimoine.tresorerie' => 'Trésorerie disponible',
            'patrimoine.financier' => 'Épargne et placements',
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    public function getRequiredFields(): array
    {
        return [
            OnboardingSession::PHASE_DISCOVERY => [
                'client.prenom',
                'client.age',
                'client.statut',
                'client.pro',
            ],
            OnboardingSession::PHASE_QUALIFICATION => [
                'projets.vision',
                'projets.retraite_age',
                'projets.objectifs',
            ],
            OnboardingSession::PHASE_RISK_ANALYSIS => [
                'risque.profil',
                'risque.transmission',
            ],
            OnboardingSession::PHASE_ETAPES => [
                'etapes.etapes',
                'etapes.etape_cle',
            ],
            OnboardingSession::PHASE_PATRIMOINE => [
                'patrimoine.immo',
                'patrimoine.tresorerie',
                'patrimoine.financier',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public function getMissingFields(array $extractedData, string $phase): array
    {
        $missing = [];

        foreach ($this->getRequiredFields()[$phase] ?? [] as $path) {
            if (!$this->hasNestedValue($extractedData, $path)) {
                $missing[] = $path;
            }
        }

        return $missing;
    }

    /**
     * @param list<string> $fields
     *
     * @return list<string>
     */
    public function toDisplayLabels(array $fields): array
    {
        $labels = $this->getFieldLabels();

        return array_map(
            static fn (string $field): string => $labels[$field] ?? $field,
            $fields
        );
    }

    private function hasNestedValue(array $data, string $path): bool
    {
        $current = $data;

        foreach (explode('.', $path) as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return false;
            }

            $current = $current[$segment];
        }

        if (is_string($current)) {
            return trim($current) !== '';
        }

        if (is_array($current)) {
            return $current !== [];
        }

        return $current !== null;
    }
}
