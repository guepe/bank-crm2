<?php

namespace App\Service;

use App\Entity\OnboardingSession;

class PlanilifeDashboardBuilder
{
    public const TIMELINE_PATH = 'etapes.timeline';

    /**
     * @var array<string, array{label: string, subtitle: string}>
     */
    private const TABS = [
        'profil' => [
            'label' => 'Profil de vie',
            'subtitle' => 'Identite, contexte familial, professionnel et coordonnees utiles.',
        ],
        'projets' => [
            'label' => 'Projets',
            'subtitle' => 'Objectifs de vie, priorites et vision patrimoniale.',
        ],
        'valeurs' => [
            'label' => 'Risque et valeurs',
            'subtitle' => 'Rapport au risque, valeurs personnelles et intention de transmission.',
        ],
        'timing' => [
            'label' => 'Timing',
            'subtitle' => 'Etapes de vie et horizons a fiabiliser.',
        ],
        'patrimoine' => [
            'label' => 'Patrimoine',
            'subtitle' => 'Biens, tresorerie, placements et dettes connus.',
        ],
        'flux' => [
            'label' => 'Revenus et flux',
            'subtitle' => 'Revenus, charges, capacite d epargne et mouvements recurrents.',
        ],
    ];

    /**
     * @var array<string, array{tab: string, label: string, type: string, value_type: string, required?: bool}>
     */
    private const FIELDS = [
        'client.prenom' => ['tab' => 'profil', 'label' => 'Prenom', 'type' => 'text', 'value_type' => 'string', 'required' => true],
        'client.nom' => ['tab' => 'profil', 'label' => 'Nom', 'type' => 'text', 'value_type' => 'string'],
        'client.age' => ['tab' => 'profil', 'label' => 'Age', 'type' => 'number', 'value_type' => 'integer', 'required' => true],
        'client.statut' => ['tab' => 'profil', 'label' => 'Situation familiale', 'type' => 'text', 'value_type' => 'string', 'required' => true],
        'client.pro' => ['tab' => 'profil', 'label' => 'Situation professionnelle', 'type' => 'text', 'value_type' => 'string', 'required' => true],
        'client.email' => ['tab' => 'profil', 'label' => 'Email', 'type' => 'email', 'value_type' => 'string'],
        'client.phone' => ['tab' => 'profil', 'label' => 'Telephone', 'type' => 'text', 'value_type' => 'string'],
        'client.attente' => ['tab' => 'profil', 'label' => 'Attente principale', 'type' => 'textarea', 'value_type' => 'string'],

        'projets.vision' => ['tab' => 'projets', 'label' => 'Vision patrimoniale', 'type' => 'textarea', 'value_type' => 'string', 'required' => true],
        'projets.objectifs' => ['tab' => 'projets', 'label' => 'Objectifs principaux', 'type' => 'textarea', 'value_type' => 'list', 'required' => true],
        'projets.retraite_age' => ['tab' => 'projets', 'label' => 'Age de retraite vise', 'type' => 'number', 'value_type' => 'integer', 'required' => true],
        'projets.priorites' => ['tab' => 'projets', 'label' => 'Priorites', 'type' => 'textarea', 'value_type' => 'list'],

        'risque.profil' => ['tab' => 'valeurs', 'label' => 'Profil de risque', 'type' => 'text', 'value_type' => 'string', 'required' => true],
        'risque.valeurs' => ['tab' => 'valeurs', 'label' => 'Valeurs importantes', 'type' => 'textarea', 'value_type' => 'list'],
        'risque.transmission' => ['tab' => 'valeurs', 'label' => 'Projet de transmission', 'type' => 'textarea', 'value_type' => 'string', 'required' => true],
        'risque.preferences' => ['tab' => 'valeurs', 'label' => 'Preferences d arbitrage', 'type' => 'textarea', 'value_type' => 'string'],

        'etapes.etape_cle' => ['tab' => 'timing', 'label' => 'Etape prioritaire', 'type' => 'text', 'value_type' => 'string', 'required' => true],
        'etapes.etapes' => ['tab' => 'timing', 'label' => 'Grandes etapes', 'type' => 'textarea', 'value_type' => 'list', 'required' => true],

        'patrimoine.immo' => ['tab' => 'patrimoine', 'label' => 'Biens immobiliers', 'type' => 'textarea', 'value_type' => 'json', 'required' => true],
        'patrimoine.tresorerie' => ['tab' => 'patrimoine', 'label' => 'Tresorerie disponible', 'type' => 'textarea', 'value_type' => 'json', 'required' => true],
        'patrimoine.financier' => ['tab' => 'patrimoine', 'label' => 'Epargne et placements', 'type' => 'textarea', 'value_type' => 'json', 'required' => true],
        'patrimoine.dettes' => ['tab' => 'patrimoine', 'label' => 'Credits et dettes', 'type' => 'textarea', 'value_type' => 'json'],

        'flux.revenus' => ['tab' => 'flux', 'label' => 'Revenus recurrents', 'type' => 'textarea', 'value_type' => 'json'],
        'flux.charges' => ['tab' => 'flux', 'label' => 'Charges recurrentes', 'type' => 'textarea', 'value_type' => 'json'],
        'flux.epargne_mensuelle' => ['tab' => 'flux', 'label' => 'Capacite d epargne mensuelle', 'type' => 'number', 'value_type' => 'integer'],
        'client.income' => ['tab' => 'flux', 'label' => 'Revenu principal', 'type' => 'number', 'value_type' => 'integer'],
    ];

    public function __construct(
        private readonly FieldProvenanceService $provenanceService,
        private readonly OnboardingServiceRequiredFields $requiredFields,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(?OnboardingSession $session): array
    {
        $data = $session instanceof OnboardingSession
            ? $this->provenanceService->extractCurrentValues($session->getExtractedData())
            : [];

        return [
            'session' => $session,
            'completion' => $this->buildCompletion($data),
            'tabs' => $this->buildTabs($session, $data),
            'timeline' => $this->buildLifeTimeline($data),
            'recent_edits' => $session instanceof OnboardingSession ? $this->provenanceService->getTimeline($session, 8) : [],
            'source_report' => $session instanceof OnboardingSession ? $this->provenanceService->getSourcingReport($session) : [
                'declared' => 0,
                'detected' => 0,
                'updated' => 0,
                'corrected' => 0,
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public function getAllowedFieldPaths(): array
    {
        return array_keys(self::FIELDS);
    }

    public function isAllowedFieldPath(string $fieldPath): bool
    {
        return array_key_exists($fieldPath, self::FIELDS);
    }

    public function getTabForField(string $fieldPath): string
    {
        return self::FIELDS[$fieldPath]['tab'] ?? 'profil';
    }

    public function normalizeSubmittedField(string $fieldPath, string $rawValue): mixed
    {
        if (!$this->isAllowedFieldPath($fieldPath)) {
            throw new \InvalidArgumentException(sprintf('Unsupported dashboard field "%s".', $fieldPath));
        }

        $rawValue = trim($rawValue);
        if ($rawValue === '') {
            return null;
        }

        return match (self::FIELDS[$fieldPath]['value_type']) {
            'integer' => $this->normalizeInteger($rawValue),
            'list' => $this->normalizeList($rawValue),
            'json' => $this->normalizeJsonOrText($rawValue),
            default => $rawValue,
        };
    }

    /**
     * @param list<array<string, mixed>> $events
     *
     * @return list<array<string, mixed>>
     */
    public function updateTimelineEvents(array $events, string $action, array $payload): array
    {
        $events = array_values(array_filter(
            array_map(fn (array $event, int $index): ?array => $this->normalizeTimelineEvent($event, $index), $events, array_keys($events))
        ));

        if ($action === 'delete') {
            $eventId = trim((string) ($payload['event_id'] ?? ''));

            return array_values(array_filter(
                $events,
                static fn (array $event): bool => ($event['id'] ?? '') !== $eventId
            ));
        }

        $submitted = $this->normalizeSubmittedTimelineEvent($payload);
        if ($submitted === null) {
            return $events;
        }

        if ($action === 'update') {
            $eventId = trim((string) ($payload['event_id'] ?? ''));
            $updated = false;

            foreach ($events as $index => $event) {
                if (($event['id'] ?? '') !== $eventId) {
                    continue;
                }

                $submitted['id'] = $eventId;
                $events[$index] = $submitted;
                $updated = true;
                break;
            }

            if ($updated) {
                return $events;
            }
        }

        $submitted['id'] = $submitted['id'] ?: bin2hex(random_bytes(6));
        $events[] = $submitted;

        return $events;
    }

    /**
     * @param list<array<string, mixed>> $events
     *
     * @return list<string>
     */
    public function summarizeTimelineForRequiredField(array $events): array
    {
        $summary = [];

        foreach ($events as $event) {
            $title = trim((string) ($event['title'] ?? ''));
            if ($title === '') {
                continue;
            }

            $horizon = trim((string) ($event['horizon'] ?? ''));
            $summary[] = $horizon !== '' ? sprintf('%s - %s', $title, $horizon) : $title;
        }

        return $summary;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildCompletion(array $data): array
    {
        $requiredPaths = array_values(array_unique(array_merge(
            ...array_values($this->requiredFields->getRequiredFields())
        )));

        $missing = [];
        foreach ($requiredPaths as $path) {
            if (!$this->hasValue($data, $path)) {
                $missing[] = $path;
            }
        }

        $total = count($requiredPaths);
        $completed = $total - count($missing);
        $score = $total === 0 ? 100.0 : round(($completed / $total) * 100, 1);

        return [
            'score' => $score,
            'completed' => $completed,
            'total' => $total,
            'report_ready' => $score >= 80.0,
            'missing_labels' => $this->requiredFields->toDisplayLabels($missing),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildTabs(?OnboardingSession $session, array $data): array
    {
        $tabs = [];

        foreach (self::TABS as $key => $tab) {
            $tabs[$key] = [
                'key' => $key,
                'label' => $tab['label'],
                'subtitle' => $tab['subtitle'],
                'fields' => [],
            ];
        }

        foreach (self::FIELDS as $path => $definition) {
            $tabs[$definition['tab']]['fields'][] = $this->buildField($session, $data, $path, $definition);
        }

        return $tabs;
    }

    /**
     * @param array{tab: string, label: string, type: string, value_type: string, required?: bool} $definition
     *
     * @return array<string, mixed>
     */
    private function buildField(?OnboardingSession $session, array $data, string $path, array $definition): array
    {
        $value = $this->readPath($data, $path);
        $provenance = $session instanceof OnboardingSession
            ? $this->provenanceService->getFieldProvenance($session, $path)
            : ['source' => null, 'history' => []];

        return [
            'path' => $path,
            'dom_id' => 'dashboard-field-'.str_replace('.', '-', $path),
            'label' => $definition['label'],
            'type' => $definition['type'],
            'value_type' => $definition['value_type'],
            'required' => (bool) ($definition['required'] ?? false),
            'value' => $value,
            'display_value' => $this->formatForDisplay($value),
            'form_value' => $this->formatForForm($value, $definition['value_type']),
            'source' => $provenance['source'],
            'source_label' => $this->formatSource($provenance['source'] ?? null),
            'history_count' => count($provenance['history'] ?? []),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildLifeTimeline(array $data): array
    {
        $timeline = $this->readPath($data, self::TIMELINE_PATH);
        $events = [];

        if (is_array($timeline)) {
            foreach (array_values($timeline) as $index => $event) {
                $normalized = is_array($event)
                    ? $this->normalizeTimelineEvent($event, $index)
                    : $this->normalizeTimelineEvent(['title' => $event], $index);

                if ($normalized !== null) {
                    $events[] = $normalized;
                }
            }
        }

        if ($events === []) {
            $rawSteps = $this->readPath($data, 'etapes.etapes');
            if (is_array($rawSteps)) {
                foreach (array_values($rawSteps) as $index => $step) {
                    $normalized = is_array($step)
                        ? $this->normalizeTimelineEvent($step, $index)
                        : $this->normalizeTimelineEvent(['title' => $step], $index);

                    if ($normalized !== null) {
                        $events[] = $normalized;
                    }
                }
            } elseif (is_string($rawSteps) && trim($rawSteps) !== '') {
                $events[] = $this->normalizeTimelineEvent(['title' => $rawSteps], 0);
            }
        }

        $keyStep = $this->readPath($data, 'etapes.etape_cle');
        if (is_string($keyStep) && trim($keyStep) !== '' && !$this->timelineContainsTitle($events, $keyStep)) {
            $events[] = [
                'id' => 'key-step',
                'title' => trim($keyStep),
                'category' => 'prioritaire',
                'horizon' => '',
                'date' => '',
                'notes' => '',
            ];
        }

        usort($events, static function (array $a, array $b): int {
            $dateA = (string) ($a['date'] ?? '');
            $dateB = (string) ($b['date'] ?? '');

            if ($dateA === '' && $dateB === '') {
                return 0;
            }

            if ($dateA === '') {
                return 1;
            }

            if ($dateB === '') {
                return -1;
            }

            return strcmp($dateA, $dateB);
        });

        return array_values(array_filter($events));
    }

    /**
     * @return array<string, mixed>|null
     */
    private function normalizeTimelineEvent(array $event, int $index): ?array
    {
        $title = $event['title']
            ?? $event['titre']
            ?? $event['nom']
            ?? $event['libelle']
            ?? $event['event']
            ?? $event['description']
            ?? null;

        if (!is_scalar($title) || trim((string) $title) === '') {
            return null;
        }

        return [
            'id' => trim((string) ($event['id'] ?? 'legacy-'.$index)),
            'title' => trim((string) $title),
            'category' => trim((string) ($event['category'] ?? $event['categorie'] ?? $event['type'] ?? 'vie')),
            'horizon' => trim((string) ($event['horizon'] ?? $event['echeance'] ?? '')),
            'date' => $this->normalizeDateString($event['date'] ?? $event['debut'] ?? $event['start_date'] ?? null),
            'notes' => trim((string) ($event['notes'] ?? $event['commentaire'] ?? '')),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function normalizeSubmittedTimelineEvent(array $payload): ?array
    {
        $title = trim((string) ($payload['title'] ?? ''));
        if ($title === '') {
            return null;
        }

        return [
            'id' => trim((string) ($payload['event_id'] ?? '')),
            'title' => $title,
            'category' => trim((string) ($payload['category'] ?? 'vie')),
            'horizon' => trim((string) ($payload['horizon'] ?? '')),
            'date' => $this->normalizeDateString($payload['date'] ?? null),
            'notes' => trim((string) ($payload['notes'] ?? '')),
        ];
    }

    private function timelineContainsTitle(array $events, string $title): bool
    {
        $normalizedTitle = mb_strtolower(trim($title));

        foreach ($events as $event) {
            if (mb_strtolower(trim((string) ($event['title'] ?? ''))) === $normalizedTitle) {
                return true;
            }
        }

        return false;
    }

    private function normalizeInteger(string $value): int|string
    {
        $normalized = preg_replace('/[^0-9\-]/', '', $value);

        return $normalized !== null && $normalized !== '' && is_numeric($normalized)
            ? (int) $normalized
            : $value;
    }

    /**
     * @return list<string>|array<string, mixed>
     */
    private function normalizeList(string $value): array
    {
        $decoded = $this->tryDecodeJson($value);
        if (is_array($decoded)) {
            return $decoded;
        }

        $items = preg_split('/\R+/', $value) ?: [];

        return array_values(array_filter(
            array_map(static fn (string $item): string => trim($item), $items),
            static fn (string $item): bool => $item !== ''
        ));
    }

    private function normalizeJsonOrText(string $value): mixed
    {
        $decoded = $this->tryDecodeJson($value);
        if ($decoded !== null) {
            return $decoded;
        }

        $items = $this->normalizeList($value);

        return count($items) > 1 ? $items : $value;
    }

    private function tryDecodeJson(string $value): mixed
    {
        if (!str_starts_with($value, '[') && !str_starts_with($value, '{')) {
            return null;
        }

        try {
            return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }
    }

    private function normalizeDateString(mixed $value): string
    {
        if (!is_scalar($value) || trim((string) $value) === '') {
            return '';
        }

        $value = trim((string) $value);
        try {
            return (new \DateTimeImmutable($value))->format('Y-m-d');
        } catch (\Throwable) {
            return $value;
        }
    }

    private function hasValue(array $data, string $path): bool
    {
        $value = $this->readPath($data, $path);

        if (is_string($value)) {
            return trim($value) !== '';
        }

        if (is_array($value)) {
            return $value !== [];
        }

        return $value !== null;
    }

    private function readPath(array $data, string $path): mixed
    {
        $current = $data;

        foreach (explode('.', $path) as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return null;
            }

            $current = $current[$segment];
        }

        return $current;
    }

    private function formatForDisplay(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        if (is_bool($value)) {
            return $value ? 'Oui' : 'Non';
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        if (is_array($value) && array_is_list($value)) {
            $items = array_map(function (mixed $item): string {
                if (is_scalar($item)) {
                    return (string) $item;
                }

                return json_encode($item, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '';
            }, $value);

            return implode(', ', array_filter($items, static fn (string $item): bool => $item !== ''));
        }

        return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '-';
    }

    private function formatForForm(mixed $value, string $valueType): string
    {
        if ($value === null) {
            return '';
        }

        if (is_array($value)) {
            if ($valueType === 'list' && array_is_list($value)) {
                $scalars = array_filter($value, static fn (mixed $item): bool => is_scalar($item));
                if (count($scalars) === count($value)) {
                    return implode("\n", array_map('strval', $value));
                }
            }

            return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '';
        }

        return is_scalar($value) ? (string) $value : '';
    }

    private function formatSource(?string $source): string
    {
        return match ($source) {
            'declared' => 'declare',
            'detected' => 'detecte',
            'updated' => 'mis a jour',
            'corrected' => 'corrige',
            default => 'non trace',
        };
    }
}
