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
- `updateSessionField()` — **the canonical write path for any field change** (inline edits, prescriber corrections, dashboard updates, form mode). Always call this instead of mutating `extractedData` directly.
- `completeSession()` — finalises the session, creates a `FieldEdit` trace for the status change.

### Required fields per phase

Managed by `OnboardingServiceRequiredFields`. Current required fields:

| Phase | Required fields |
|---|---|
| discovery | `client.prenom`, `client.age`, `client.statut`, `client.pro`, `client.attente` |
| qualification | `projets.vision`, `projets.retraite_age`, `projets.objectifs`, `projets.priorites` |
| risk_analysis | `risque.profil`, `risque.transmission`, `risque.valeurs` |
| etapes | `etapes.etapes`, `etapes.etape_cle`, `etapes.timeline` |
| patrimoine | `patrimoine.immo`, `patrimoine.tresorerie`, `patrimoine.financier`, `flux.revenus`, `flux.charges`, `flux.epargne_mensuelle` |

`etapes.timeline` is AI-generated — a JSON array of `{titre, categorie, annee, horizon, notes}` objects. Fields with type `json` in `getFieldTypes()` are excluded from the form mode panel.

`flux.*` fields live in their own namespace in `extractedData` but belong to the patrimoine phase for completeness scoring.

### Field provenance system (EP03)

Every mutation to `OnboardingSession.extractedData` must go through `OnboardingService::updateSessionField()` or `FieldProvenanceService::trackFieldUpdate()`. This creates a `FieldEdit` row that records who changed what, when, why, and from which source.

Sources: `declared` | `detected` | `updated` | `corrected`
Roles: `client` | `prescriber` | `system`

The client dashboard and rapport both read `source` / `source_label` from `PlanilifeDashboardBuilder::build()` to render colour-coded provenance badges (CSS classes `source-declared`, `source-detected`, `source-updated`, `source-corrected`).

`FieldProvenanceService` also exposes:
- `getTimeline(session, limit)` — chronological list of all edits (used in the dashboard history and the admin audit view)
- `getPrescriberCorrections(session, limit)` — filtered list of `ROLE_PRESCRIBER` edits (used in the client "Corrections prescripteurs" dashboard section)

### PlanilifeDashboardBuilder

Builds the full client dashboard and rapport from a session. Returns:

```php
[
    'session'                => OnboardingSession|null,
    'completion'             => ['score' => float, 'report_ready' => bool, 'missing_labels' => string[]],
    'tabs'                   => [['key', 'label', 'subtitle', 'fields' => [['path', 'label', 'value',
                                  'display_value', 'source', 'source_label', 'history_count', ...]]],
    'timeline'               => [...],   // life events from etapes.timeline
    'recent_edits'           => [...],   // last 8 FieldEdit entries (all roles)
    'prescriber_corrections' => [...],   // last 20 FieldEdit entries with ROLE_PRESCRIBER
    'source_report'          => ['declared', 'detected', 'updated', 'corrected'],
]
```

`completion.report_ready` is true when score ≥ 80%. The rapport route (`/portal/rapport`) enforces this gate.

### Prescriber sharing (EP05)

`PrescriberInvitation` stores a token (64-char hex), the authorized dashboard blocks (`['profil', 'projets', ...]`), and the prescriber's role label. No Symfony account is required. Invitations expire after 30 days.

- Client manages invitations at `/portal/partage`
- Prescriber views filtered blocks at `/prescripteur/{token}` and corrects fields via `SOURCE_CORRECTED / ROLE_PRESCRIBER`
- Prescriber views the rapport at `/prescripteur/{token}/rapport`
- On each correction, `BrevoMailer` sends a notification email to the client (silent no-op when `BREVO_ENABLED=0`)
- Expired/revoked tokens return HTTP 410 (`prescriber/expired.html.twig`)

### Form mode (EP02 — US023)

The chat UI (`/onboarding/{id}/chat`) has a "Mode formulaire" toggle. When active, a panel displays the current phase's required fields as direct inputs. Each field saves via `POST /onboarding/{id}/field` with full audit trail (`SOURCE_UPDATED / ROLE_CLIENT`).

The route validates `field_path` against the regex `^(client|projets|risque|etapes|patrimoine|flux)\.[a-z_]+(\.[a-z_]+)*$` before writing.

### Onboarding chat — form mode route

`POST /onboarding/{id}/field` (name: `app_onboarding_field_update`) returns:
```json
{ "success": true, "completeness": 72.0, "missingFields": [...], "extractedData": {...}, "phase": "discovery" }
```

### Testing conventions

All tests are integration tests under `tests/Integration/`. They use a real SQLite database (separate from dev). There are no unit tests. Each test creates its own fixtures inline and cleans up after itself.

`MockAiChatService` is used in tests via the `test` environment service override — check `config/services_test.yaml` if it exists, otherwise the mock is wired by the test itself.

When adding required fields, always update the affected integration test fixtures to include the new fields, otherwise completeness-dependent assertions will fail.

### Migrations

Use hand-written migrations in `migrations/`. Naming convention: `Version{YYYYMMDDHHMMSS}.php`. The dev and test databases are SQLite — use SQLite-compatible DDL (e.g. `CLOB` instead of `TEXT` for JSON columns, `REFERENCES` inline for FK).

### Environment variables

Key variables for local dev:
```
OPENAI_API_KEY=sk-proj-...      # Required for onboarding chat
BREVO_ENABLED=0                 # Email sending (off by default)
BREVO_API_KEY=                  # Required only when BREVO_ENABLED=1
```

### Beta status (May 2026)

All 42 user stories across EP01–EP08 are done. The platform covers the full end-to-end flow:
registration → 5-phase interview (chat + form mode) → 6-tab dashboard → prescriber sharing → beta rapport → RGPD rights → admin back-office.

Manual test scenarios are documented in `docs/scenarios-test.md`.
Next priorities (post-beta) are tracked in `docs/backlog.md` under "Prochaines Evolutions Post-Beta".

### Known gotchas

- **Heredoc in PHP match expressions**: the Edit tool sometimes writes Unicode curly quotes (`\xe2\x80\x99`) instead of ASCII apostrophes in `<<<'IDENTIFIER'` nowdoc syntax. If a PHP parse error says "Unclosed '{'" on the `match` line, check the nowdoc delimiters with `xxd` and rewrite with regular concatenated strings instead.
- **`extractedData` provenance wrapper**: raw values in `extractedData` may be either a plain scalar or `{current, source, history}`. Always use `FieldProvenanceService::getCurrentValue()` or go through `PlanilifeDashboardBuilder` when reading display values — never access `['current']` directly in controllers.
- **`flux.*` namespace in patrimoine phase**: `flux.revenus` etc. are stored at `extractedData['flux']` but scored during the patrimoine phase. This is intentional — do not move them into `patrimoine.*`.
- **CSS is inline**: all styles are in `templates/base.html.twig`. No build step, no external stylesheet.
