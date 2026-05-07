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
            'client.prenom'              => 'Prénom du client',
            'client.age'                 => 'Âge du client',
            'client.statut'              => 'Situation familiale',
            'client.pro'                 => 'Situation professionnelle',
            'client.attente'             => 'Attente principale',
            'client.nb_enfants'          => 'Nombre d\'enfants',
            'projets.vision'             => 'Vision patrimoniale',
            'projets.retraite_age'       => 'Âge de retraite visé',
            'projets.objectifs'          => 'Objectifs principaux',
            'projets.priorites'          => 'Priorités classées',
            'risque.profil'              => 'Profil de risque',
            'risque.transmission'        => 'Projet de transmission',
            'risque.valeurs'             => 'Valeurs et convictions',
            'etapes.etapes'              => 'Grandes étapes à venir',
            'etapes.etape_cle'           => 'Étape prioritaire',
            'etapes.timeline'            => 'Timeline structurée',
            'patrimoine.immo'            => 'Biens immobiliers',
            'patrimoine.tresorerie'      => 'Trésorerie disponible',
            'patrimoine.financier'       => 'Épargne et placements',
            'flux.revenus'               => 'Revenus mensuels',
            'flux.charges'              => 'Charges fixes mensuelles',
            'flux.epargne_mensuelle'     => 'Capacité d\'épargne mensuelle',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function getFieldTypes(): array
    {
        return [
            'client.prenom'          => 'text',
            'client.age'             => 'number',
            'client.statut'          => 'text',
            'client.pro'             => 'text',
            'client.attente'         => 'textarea',
            'client.nb_enfants'      => 'number',
            'projets.vision'         => 'textarea',
            'projets.retraite_age'   => 'number',
            'projets.objectifs'      => 'textarea',
            'projets.priorites'      => 'textarea',
            'risque.profil'          => 'text',
            'risque.transmission'    => 'textarea',
            'risque.valeurs'         => 'textarea',
            'etapes.etapes'          => 'textarea',
            'etapes.etape_cle'       => 'text',
            'etapes.timeline'        => 'json',
            'patrimoine.immo'        => 'textarea',
            'patrimoine.tresorerie'  => 'text',
            'patrimoine.financier'   => 'textarea',
            'flux.revenus'           => 'text',
            'flux.charges'           => 'text',
            'flux.epargne_mensuelle' => 'text',
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
                'client.attente',
            ],
            OnboardingSession::PHASE_QUALIFICATION => [
                'projets.vision',
                'projets.retraite_age',
                'projets.objectifs',
                'projets.priorites',
            ],
            OnboardingSession::PHASE_RISK_ANALYSIS => [
                'risque.profil',
                'risque.transmission',
                'risque.valeurs',
            ],
            OnboardingSession::PHASE_ETAPES => [
                'etapes.etapes',
                'etapes.etape_cle',
                'etapes.timeline',
            ],
            OnboardingSession::PHASE_PATRIMOINE => [
                'patrimoine.immo',
                'patrimoine.tresorerie',
                'patrimoine.financier',
                'flux.revenus',
                'flux.charges',
                'flux.epargne_mensuelle',
            ],
        ];
    }

    /**
     * Returns flat path => current value string for all required fields of the given phase.
     *
     * @return array<string, string>
     */
    public function getPhaseCurrentValues(array $extractedData, string $phase): array
    {
        $result = [];

        foreach ($this->getRequiredFields()[$phase] ?? [] as $path) {
            $result[$path] = $this->getValueByPath($extractedData, $path);
        }

        return $result;
    }

    private function getValueByPath(array $data, string $path): string
    {
        $current = $data;

        foreach (explode('.', $path) as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return '';
            }

            $current = $current[$segment];
        }

        $value = $this->getCurrentValue($current);

        if (is_array($value)) {
            return implode(', ', array_map(
                static fn (mixed $v): string => is_string($v) ? $v : (string) json_encode($v, JSON_UNESCAPED_UNICODE),
                $value
            ));
        }

        return is_scalar($value) ? (string) $value : '';
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

        $current = $this->getCurrentValue($current);

        if (is_string($current)) {
            return trim($current) !== '';
        }

        if (is_array($current)) {
            return $current !== [];
        }

        return $current !== null;
    }

    private function getCurrentValue(mixed $value): mixed
    {
        if (
            is_array($value)
            && array_key_exists('current', $value)
            && array_key_exists('source', $value)
            && array_key_exists('history', $value)
        ) {
            return $value['current'];
        }

        return $value;
    }
}
