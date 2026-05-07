<?php

namespace App\Service;

use App\Entity\BetaPilotageIncident;
use App\Entity\FieldEdit;
use App\Entity\OnboardingSession;
use App\Entity\Tenant;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class BetaPilotageDashboardBuilder
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly OnboardingServiceRequiredFields $requiredFields,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(): array
    {
        $users = $this->entityManager->getRepository(User::class)->findAll();
        $tenants = $this->entityManager->getRepository(Tenant::class)->findBy([], ['name' => 'ASC']);
        $sessions = $this->entityManager->getRepository(OnboardingSession::class)->findBy([], ['updatedAt' => 'DESC']);
        $incidents = $this->entityManager->getRepository(BetaPilotageIncident::class)->findBy(
            [],
            ['status' => 'ASC', 'createdAt' => 'DESC'],
            8
        );

        return [
            'tenants' => $this->buildTenantSummary($tenants, $users, $sessions),
            'users' => $this->buildUserSummary($users),
            'onboarding' => $this->buildOnboardingSummary($sessions),
            'reminders' => $this->buildReminderSummary($users, $sessions),
            'extraction' => $this->buildExtractionSummary($sessions),
            'incidents' => $this->buildIncidentSummary($incidents),
        ];
    }

    /**
     * @param list<Tenant> $tenants
     * @param list<User> $users
     * @param list<OnboardingSession> $sessions
     *
     * @return array<string, mixed>
     */
    private function buildTenantSummary(array $tenants, array $users, array $sessions): array
    {
        $byPlan = [];
        foreach (Tenant::planChoices() as $plan => $label) {
            $byPlan[$plan] = [
                'label' => $label,
                'count' => 0,
            ];
        }

        $rows = [];
        foreach ($tenants as $tenant) {
            $byPlan[$tenant->getPlan()]['count']++;
            $rows[$tenant->getId()] = [
                'name' => $tenant->getName(),
                'code' => $tenant->getCode(),
                'plan' => $tenant->getPlanLabel(),
                'status' => $tenant->getStatusLabel(),
                'active' => $tenant->isActive(),
                'users' => 0,
                'clients' => 0,
                'suspended_users' => 0,
                'sessions' => 0,
                'abandoned_sessions' => 0,
                'average_completion' => 0.0,
                'completion_sum' => 0.0,
            ];
        }

        $withoutTenant = [
            'name' => 'Sans tenant',
            'code' => '-',
            'plan' => '-',
            'status' => '-',
            'active' => true,
            'users' => 0,
            'clients' => 0,
            'suspended_users' => 0,
            'sessions' => 0,
            'abandoned_sessions' => 0,
            'average_completion' => 0.0,
            'completion_sum' => 0.0,
        ];

        foreach ($users as $user) {
            $tenant = $user->getTenant();
            $target = $tenant instanceof Tenant && isset($rows[$tenant->getId()])
                ? $tenant->getId()
                : null;

            if ($target === null) {
                $withoutTenant['users']++;
                if ($user->isClientUser()) {
                    $withoutTenant['clients']++;
                }
                if (!$user->isEnabled()) {
                    $withoutTenant['suspended_users']++;
                }
                continue;
            }

            $rows[$target]['users']++;
            if ($user->isClientUser()) {
                $rows[$target]['clients']++;
            }
            if (!$user->isEnabled()) {
                $rows[$target]['suspended_users']++;
            }
        }

        foreach ($sessions as $session) {
            $tenant = $session->getUser()->getTenant();
            $target = $tenant instanceof Tenant && isset($rows[$tenant->getId()])
                ? $tenant->getId()
                : null;
            $completion = $this->calculateCompletion($session);

            if ($target === null) {
                $withoutTenant['sessions']++;
                $withoutTenant['completion_sum'] += $completion;
                if ($session->getStatus() === OnboardingSession::STATUS_ABANDONED) {
                    $withoutTenant['abandoned_sessions']++;
                }
                continue;
            }

            $rows[$target]['sessions']++;
            $rows[$target]['completion_sum'] += $completion;
            if ($session->getStatus() === OnboardingSession::STATUS_ABANDONED) {
                $rows[$target]['abandoned_sessions']++;
            }
        }

        foreach ($rows as &$row) {
            $row['average_completion'] = $row['sessions'] > 0 ? round($row['completion_sum'] / $row['sessions'], 1) : 0.0;
            unset($row['completion_sum']);
        }
        unset($row);

        if ($withoutTenant['users'] > 0 || $withoutTenant['sessions'] > 0) {
            $withoutTenant['average_completion'] = $withoutTenant['sessions'] > 0
                ? round($withoutTenant['completion_sum'] / $withoutTenant['sessions'], 1)
                : 0.0;
            unset($withoutTenant['completion_sum']);
            $rows[] = $withoutTenant;
        }

        return [
            'total' => count($tenants),
            'active' => count(array_filter($tenants, static fn(Tenant $tenant): bool => $tenant->isActive())),
            'suspended' => count(array_filter($tenants, static fn(Tenant $tenant): bool => !$tenant->isActive())),
            'by_plan' => array_values($byPlan),
            'rows' => array_values($rows),
        ];
    }

    /**
     * @param list<User> $users
     *
     * @return array<string, mixed>
     */
    private function buildUserSummary(array $users): array
    {
        $clients = array_filter($users, static fn(User $user): bool => $user->isClientUser());
        $internal = array_filter($users, static fn(User $user): bool => $user->isInternalUser());

        return [
            'total' => count($users),
            'clients' => count($clients),
            'internal' => count($internal),
            'admins' => count(array_filter($users, static fn(User $user): bool => in_array('ROLE_ADMIN', $user->getRoles(), true))),
            'suspended' => count(array_filter($users, static fn(User $user): bool => !$user->isEnabled())),
            'pending_consent' => count(array_filter($clients, static fn(User $user): bool => !$user->hasAcceptedConsent())),
            'data_deletion_requests' => count(array_filter($users, static fn(User $user): bool => $user->getDataDeletionRequestedAt() !== null)),
        ];
    }

    /**
     * @param list<OnboardingSession> $sessions
     *
     * @return array<string, mixed>
     */
    private function buildOnboardingSummary(array $sessions): array
    {
        $scores = array_map(fn(OnboardingSession $session): float => $this->calculateCompletion($session), $sessions);
        $total = count($sessions);
        $statusCounts = [
            OnboardingSession::STATUS_IN_PROGRESS => 0,
            OnboardingSession::STATUS_DRAFT => 0,
            OnboardingSession::STATUS_COMPLETED => 0,
            OnboardingSession::STATUS_ABANDONED => 0,
        ];

        foreach ($sessions as $session) {
            $statusCounts[$session->getStatus()] = ($statusCounts[$session->getStatus()] ?? 0) + 1;
        }

        return [
            'total' => $total,
            'average_completion' => $total > 0 ? round(array_sum($scores) / $total, 1) : 0.0,
            'report_ready' => count(array_filter($scores, static fn(float $score): bool => $score >= 80.0)),
            'status_counts' => $statusCounts,
            'completion_buckets' => [
                '0-39' => count(array_filter($scores, static fn(float $score): bool => $score < 40.0)),
                '40-79' => count(array_filter($scores, static fn(float $score): bool => $score >= 40.0 && $score < 80.0)),
                '80-100' => count(array_filter($scores, static fn(float $score): bool => $score >= 80.0)),
            ],
        ];
    }

    /**
     * @param list<User> $users
     * @param list<OnboardingSession> $sessions
     *
     * @return array<string, mixed>
     */
    private function buildReminderSummary(array $users, array $sessions): array
    {
        $latestByUserId = [];
        foreach ($sessions as $session) {
            $userId = $session->getUser()->getId();
            if ($userId === null) {
                continue;
            }

            if (!isset($latestByUserId[$userId]) || $session->getUpdatedAt() > $latestByUserId[$userId]->getUpdatedAt()) {
                $latestByUserId[$userId] = $session;
            }
        }

        $reasons = [
            'consent' => 0,
            'not_started' => 0,
            'stale_in_progress' => 0,
            'abandoned' => 0,
        ];
        $tenantRows = [];
        $staleBefore = new \DateTimeImmutable('-7 days');

        foreach ($users as $user) {
            if (!$user->isClientUser() || !$user->isEnabled()) {
                continue;
            }

            $reason = null;
            $latest = $user->getId() !== null ? ($latestByUserId[$user->getId()] ?? null) : null;

            if (!$user->hasAcceptedConsent()) {
                $reason = 'consent';
            } elseif (!$latest instanceof OnboardingSession) {
                $reason = 'not_started';
            } elseif ($latest->getStatus() === OnboardingSession::STATUS_ABANDONED) {
                $reason = 'abandoned';
            } elseif ($latest->getStatus() === OnboardingSession::STATUS_IN_PROGRESS && $latest->getUpdatedAt() < $staleBefore) {
                $reason = 'stale_in_progress';
            }

            if ($reason === null) {
                continue;
            }

            $reasons[$reason]++;
            $tenantName = $user->getTenant()?->getName() ?? 'Sans tenant';
            $tenantRows[$tenantName] ??= [
                'tenant' => $tenantName,
                'total' => 0,
                'consent' => 0,
                'not_started' => 0,
                'stale_in_progress' => 0,
                'abandoned' => 0,
            ];
            $tenantRows[$tenantName]['total']++;
            $tenantRows[$tenantName][$reason]++;
        }

        ksort($tenantRows);

        return [
            'total' => array_sum($reasons),
            'reasons' => $reasons,
            'by_tenant' => array_values($tenantRows),
        ];
    }

    /**
     * @param list<OnboardingSession> $sessions
     *
     * @return array<string, mixed>
     */
    private function buildExtractionSummary(array $sessions): array
    {
        $fieldCounts = array_map(fn(OnboardingSession $session): int => $this->countFilledFields($session->getExtractedData()), $sessions);
        $total = count($sessions);
        $fieldEditRepository = $this->entityManager->getRepository(FieldEdit::class);

        return [
            'sessions_with_data' => count(array_filter($fieldCounts, static fn(int $count): bool => $count > 0)),
            'average_fields' => $total > 0 ? round(array_sum($fieldCounts) / $total, 1) : 0.0,
            'sources' => [
                'declared' => $fieldEditRepository->count(['source' => FieldEdit::SOURCE_DECLARED]),
                'detected' => $fieldEditRepository->count(['source' => FieldEdit::SOURCE_DETECTED]),
                'updated' => $fieldEditRepository->count(['source' => FieldEdit::SOURCE_UPDATED]),
                'corrected' => $fieldEditRepository->count(['source' => FieldEdit::SOURCE_CORRECTED]),
            ],
        ];
    }

    /**
     * @param list<BetaPilotageIncident> $recent
     *
     * @return array<string, mixed>
     */
    private function buildIncidentSummary(array $recent): array
    {
        $repository = $this->entityManager->getRepository(BetaPilotageIncident::class);

        return [
            'open' => $repository->count(['status' => BetaPilotageIncident::STATUS_OPEN]),
            'in_progress' => $repository->count(['status' => BetaPilotageIncident::STATUS_IN_PROGRESS]),
            'resolved' => $repository->count(['status' => BetaPilotageIncident::STATUS_RESOLVED]),
            'high' => $repository->count(['severity' => BetaPilotageIncident::SEVERITY_HIGH]),
            'recent' => $recent,
        ];
    }

    private function calculateCompletion(OnboardingSession $session): float
    {
        $requiredPaths = array_values(array_unique(array_merge(
            ...array_values($this->requiredFields->getRequiredFields())
        )));
        $total = count($requiredPaths);
        if ($total === 0) {
            return 100.0;
        }

        $completed = 0;
        foreach ($requiredPaths as $path) {
            if ($this->hasValue($session->getExtractedData(), $path)) {
                $completed++;
            }
        }

        return round(($completed / $total) * 100, 1);
    }

    private function countFilledFields(array $data): int
    {
        $count = 0;

        foreach ($data as $value) {
            $value = $this->currentValue($value);

            if (is_array($value)) {
                $count += $this->countFilledFields($value);
                continue;
            }

            if ($value !== null && (!is_string($value) || trim($value) !== '')) {
                $count++;
            }
        }

        return $count;
    }

    private function hasValue(array $data, string $path): bool
    {
        $value = $this->readPath($data, $path);
        $value = $this->currentValue($value);

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

    private function currentValue(mixed $value): mixed
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
