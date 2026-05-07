# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
# Daily check before merge (lint + integration tests)
composer check

# Run all tests
php bin/phpunit

# Run a single test class
php bin/phpunit tests/Integration/OnboardingFlowTest.php

# Run a single test method
php bin/phpunit --filter testClientCanUpdateDashboardFieldWithProvenance

# Lint Twig templates
php bin/console lint:twig templates

# Lint Symfony container (catches wiring errors)
php bin/console lint:container

# Run migrations
php bin/console doctrine:migrations:migrate --no-interaction

# Debug routes
php bin/console debug:router
```

## Architecture Overview

Symfony 8 / PHP 8.4 / Doctrine ORM / SQLite (dev & test) / OpenAI API for onboarding chat.

All CSS lives inline in `templates/base.html.twig` — there is no compiled stylesheet.

### User roles

Three disjoint roles:
- `ROLE_USER` / `ROLE_ADMIN` — internal staff (advisors, admins). Access to CRM routes.
- `ROLE_CLIENT` — portal users (customers). Access to `/portal/*` routes only.
- Prescribers — no Symfony account; access via time-limited token at `/prescripteur/{token}`.

`User::isInternalUser()` and `User::isClientUser()` are the canonical role checks used across the codebase.

### The Planilife onboarding flow

The core feature is a 5-phase life-planning interview (`OnboardingSession`):

```
discovery → qualification → risk_analysis → etapes → patrimoine
```

Each session holds `extractedData` (nested JSON) and `messages` (conversation history). The AI (`ChatGptService`) processes messages and returns structured JSON with `extractedFields` and phase-advance signals.

`OnboardingService` is the central orchestrator:
- `processLlmResponse()` — handles AI response, tracks field provenance, advances phase, persists
- `updateSessionField()` — **the canonical write path for any field change** (inline edits, prescriber corrections, dashboard updates). Always call this instead of mutating `extractedData` directly.
- `completeSession()` — finalises the session, creates a `FieldEdit` trace for the status change.

### Field provenance system (EP03)

Every mutation to `OnboardingSession.extractedData` must go through `OnboardingService::updateSessionField()` or `FieldProvenanceService::trackFieldUpdate()`. This creates a `FieldEdit` row that records who changed what, when, why, and from which source.

Sources: `declared` | `detected` | `updated` | `corrected`
Roles: `client` | `prescriber` | `system`

The client dashboard and rapport both read `source` / `source_label` from `PlanilifeDashboardBuilder::build()` to render colour-coded provenance badges (CSS classes `source-declared`, `source-detected`, `source-updated`, `source-corrected`).

### PlanilifeDashboardBuilder

Builds the full client dashboard and rapport from a session. Returns:

```php
[
    'session'      => OnboardingSession|null,
    'completion'   => ['score' => float, 'report_ready' => bool, 'missing_labels' => string[]],
    'tabs'         => [['key', 'label', 'subtitle', 'fields' => [['path', 'label', 'value',
                        'display_value', 'source', 'source_label', 'history_count', ...]]],
    'timeline'     => [...],   // life events from etapes.timeline
    'recent_edits' => [...],   // last FieldEdit entries
    'source_report'=> ['declared', 'detected', 'updated', 'corrected'],
]
```

`completion.report_ready` is true when score ≥ 80%. The rapport route (`/portal/rapport`) enforces this gate.

### Prescriber sharing (EP05)

`PrescriberInvitation` stores a token (64-char hex), the authorized dashboard blocks (`['profil', 'projets', ...]`), and the prescriber's role label. No Symfony account is required.

- Client manages invitations at `/portal/partage`
- Prescriber views filtered blocks at `/prescripteur/{token}` and corrects fields via `SOURCE_CORRECTED / ROLE_PRESCRIBER`
- Prescriber views the rapport at `/prescripteur/{token}/rapport`

### Testing conventions

All tests are integration tests under `tests/Integration/`. They use a real SQLite database (separate from dev). There are no unit tests. Each test creates its own fixtures inline and cleans up after itself.

`MockAiChatService` is used in tests via the `test` environment service override — check `config/services_test.yaml` if it exists, otherwise the mock is wired by the test itself.

### Migrations

Use hand-written migrations in `migrations/`. Naming convention: `Version{YYYYMMDDHHMMSS}.php`. The dev and test databases are SQLite — use SQLite-compatible DDL (e.g. `CLOB` instead of `TEXT` for JSON columns, `REFERENCES` inline for FK).

### Environment variables

Key variables for local dev:
```
OPENAI_API_KEY=sk-proj-...      # Required for onboarding chat
BREVO_ENABLED=0                 # Email sending (off by default)
```
