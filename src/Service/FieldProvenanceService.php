<?php

namespace App\Service;

use App\Entity\FieldEdit;
use App\Entity\OnboardingSession;
use App\Entity\User;
use App\Repository\FieldEditRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * FieldProvenanceService - Manages field provenance, history, and sourcing for collaborative documents.
 *
 * Responsibility:
 * - Track all field modifications with source, author, timestamp, reason
 * - Build provenance structure (current + history) for each field
 * - Provide audit reports and sourcing summaries
 */
class FieldProvenanceService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FieldEditRepository $fieldEditRepository,
    ) {}

    /**
     * Track a field update with provenance metadata.
     * Creates a FieldEdit record and returns updated provenance structure.
     */
    public function trackFieldUpdate(
        OnboardingSession $session,
        string $fieldPath,
        mixed $newValue,
        string $source = FieldEdit::SOURCE_DECLARED,
        ?User $author = null,
        ?string $reason = null,
        ?string $role = null,
    ): array {
        $previousEdits = $this->fieldEditRepository->findBySessionAndFieldPath($session, $fieldPath);
        $oldValue = $this->getValueByPath($session->getExtractedData(), $fieldPath);

        $fieldEdit = new FieldEdit($session, $fieldPath, $newValue, $source);
        $fieldEdit->setOldValue($oldValue);
        $fieldEdit->setRole($role ?? $this->inferRole($source, $author));

        if ($author !== null) {
            $fieldEdit->setAuthor($author);
        }

        if ($reason !== null) {
            $fieldEdit->setReason($reason);
        }

        $this->entityManager->persist($fieldEdit);

        return $this->buildProvenanceStructure($previousEdits, $fieldEdit);
    }

    /**
     * Build provenance structure for a field: { current, source, history }.
     *
     * @param list<FieldEdit> $previousEdits
     */
    private function buildProvenanceStructure(
        array $previousEdits,
        FieldEdit $currentEdit,
    ): array {
        $history = array_map(
            static fn (FieldEdit $edit): array => $edit->toProvenanceEntry(),
            $previousEdits
        );
        $history[] = $currentEdit->toProvenanceEntry();

        return [
            'current' => $currentEdit->getNewValue(),
            'source' => $currentEdit->getSource(),
            'history' => $history,
        ];
    }

    /**
     * Get complete provenance for a field (all edits).
     */
    public function getFieldProvenance(OnboardingSession $session, string $fieldPath): array
    {
        $edits = $this->fieldEditRepository->findBySessionAndFieldPath($session, $fieldPath);

        if (empty($edits)) {
            return [
                'current' => $this->getValueByPath($session->getExtractedData(), $fieldPath),
                'source' => null,
                'history' => [],
            ];
        }

        $currentEdit = end($edits);
        $history = array_map(fn(FieldEdit $e) => $e->toProvenanceEntry(), $edits);

        return [
            'current' => $currentEdit->getNewValue(),
            'source' => $currentEdit->getSource(),
            'history' => $history,
        ];
    }

    /**
     * Get sourcing report: count fields by source type.
     */
    public function getSourcingReport(OnboardingSession $session): array
    {
        return [
            'declared' => $this->fieldEditRepository->countBySessionAndSource($session, FieldEdit::SOURCE_DECLARED),
            'detected' => $this->fieldEditRepository->countBySessionAndSource($session, FieldEdit::SOURCE_DETECTED),
            'updated' => $this->fieldEditRepository->countBySessionAndSource($session, FieldEdit::SOURCE_UPDATED),
            'corrected' => $this->fieldEditRepository->countBySessionAndSource($session, FieldEdit::SOURCE_CORRECTED),
        ];
    }

    /**
     * Get recent edits timeline for session.
     */
    /**
     * Returns only the corrections made by prescribers for this session.
     */
    public function getPrescriberCorrections(OnboardingSession $session, int $limit = 20): array
    {
        $edits = $this->fieldEditRepository->findBySessionAndRole($session, FieldEdit::ROLE_PRESCRIBER, $limit);

        return array_map(fn(FieldEdit $e) => [
            'fieldPath' => $e->getFieldPath(),
            'oldValue'  => $e->getOldValue(),
            'newValue'  => $e->getNewValue(),
            'source'    => $e->getSource(),
            'role'      => $e->getRole(),
            'reason'    => $e->getReason(),
            'timestamp' => $e->getCreatedAt()->format(\DateTimeInterface::ATOM),
        ], $edits);
    }

    public function getTimeline(OnboardingSession $session, int $limit = 50): array
    {
        $edits = $this->fieldEditRepository->findRecentBySession($session, $limit);

        return array_map(fn(FieldEdit $e) => [
            'fieldPath' => $e->getFieldPath(),
            'oldValue' => $e->getOldValue(),
            'newValue' => $e->getNewValue(),
            'source' => $e->getSource(),
            'role' => $e->getRole(),
            'author' => $e->getAuthor()?->getEmail(),
            'reason' => $e->getReason(),
            'timestamp' => $e->getCreatedAt()->format(\DateTimeInterface::ATOM),
        ], $edits);
    }

    /**
     * Convert old extractedData format to new provenance format.
     * Used during migration from non-provenance to provenance model.
     *
     * @return array { current, source: 'declared', history: [...] }
     */
    public function migrateToProvenanceFormat(array $data, string $source = FieldEdit::SOURCE_DECLARED): array
    {
        if (empty($data)) {
            return [];
        }

        $result = [];
        $this->recursiveMigrate($data, $result, $source);

        return $result;
    }

    /**
     * Recursively convert flat data to provenance structure.
     */
    private function recursiveMigrate(array $data, array &$result, string $source): void
    {
        foreach ($data as $key => $value) {
            if (is_array($value) && !empty($value)) {
                if ($this->isProvenanceNode($value)) {
                    $result[$key] = $value;
                } else {
                    $result[$key] = [];
                    $this->recursiveMigrate($value, $result[$key], $source);
                }
            } else {
                // Convert scalar to provenance format
                $result[$key] = [
                    'current' => $value,
                    'source' => $source,
                    'history' => [
                        [
                            'value' => $value,
                            'source' => $source,
                            'timestamp' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
                            'author' => null,
                        ],
                    ],
                ];
            }
        }
    }

    /**
     * Extract current values from provenance-structured data.
     * Converts { current, source, history } back to flat structure for use in entities.
     */
    public function extractCurrentValues(array $provenanceData): array
    {
        if (empty($provenanceData)) {
            return [];
        }

        $result = [];
        $this->recursiveExtract($provenanceData, $result);

        return $result;
    }

    /**
     * Recursively extract 'current' values from provenance structure.
     */
    private function recursiveExtract(array $provenanceData, array &$result): void
    {
        foreach ($provenanceData as $key => $value) {
            if (is_array($value)) {
                if ($this->isProvenanceNode($value)) {
                    $result[$key] = $value['current'];
                } else {
                    $result[$key] = [];
                    $this->recursiveExtract($value, $result[$key]);
                }
            } else {
                $result[$key] = $value;
            }
        }
    }

    /**
     * Get value by dot-notation path from nested array.
     * e.g., "client.prenom" returns $data['client']['prenom']
     */
    private function getValueByPath(array $data, string $path): mixed
    {
        $keys = explode('.', $path);
        $current = $data;

        foreach ($keys as $key) {
            if (!is_array($current) || !array_key_exists($key, $current)) {
                return null;
            }
            $current = $current[$key];
        }

        if (is_array($current) && $this->isProvenanceNode($current)) {
            return $current['current'];
        }

        return $current;
    }

    /**
     * Set value by dot-notation path in nested array.
     * e.g., "client.prenom" sets $data['client']['prenom']
     */
    public function setValueByPath(array &$data, string $path, mixed $value): void
    {
        $keys = explode('.', $path);
        $current = &$data;

        foreach ($keys as $i => $key) {
            if ($i === count($keys) - 1) {
                $current[$key] = $value;
            } else {
                if (!isset($current[$key]) || !is_array($current[$key])) {
                    $current[$key] = [];
                }

                $current = &$current[$key];
            }
        }
    }

    private function inferRole(string $source, ?User $author): string
    {
        return match ($source) {
            FieldEdit::SOURCE_DETECTED => FieldEdit::ROLE_SYSTEM,
            FieldEdit::SOURCE_CORRECTED => FieldEdit::ROLE_PRESCRIBER,
            FieldEdit::SOURCE_DECLARED,
            FieldEdit::SOURCE_UPDATED => $author !== null ? FieldEdit::ROLE_CLIENT : FieldEdit::ROLE_SYSTEM,
            default => FieldEdit::ROLE_SYSTEM,
        };
    }

    /**
     * @param array<string, mixed> $value
     */
    private function isProvenanceNode(array $value): bool
    {
        return array_key_exists('current', $value)
            && array_key_exists('source', $value)
            && array_key_exists('history', $value)
            && is_array($value['history']);
    }
}
