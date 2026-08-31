# ADR-0024 — Membership Privacy & Retention Product Defaults

Status: **Accepted product defaults / jurisdiction-specific policy may override**  
Date: 2026-08-27

## Context

Membership stores access history, external billing references, team/invitation data and potentially high-volume access logs. Leaving retention undefined risks either deleting evidence needed for access/reconciliation or retaining unnecessary personal data indefinitely.

These defaults are product/technical behavior, not legal advice or a compliance guarantee.

## Decision

WPEssential Membership adopts explicit category-level retention instead of one global retention switch.

Default principles:
- collect/store the minimum data needed for authorization, reconciliation and explainability;
- current derived Entitlements are current-state data, not an indefinite duplicate history store;
- terminal Enrollment/transition history is retained by default for operational explainability until site policy changes it;
- full raw provider webhook payload retention is off/minimized by default;
- detailed successful protected-download logging is off by default;
- IP/device logging is off by default unless explicitly enabled for a stated purpose;
- terminal invitations become eligible for automatic cleanup, candidate default 30 days;
- billing/provider references are retained only while needed for current/historical reconciliation;
- secrets/card credentials are never Membership retention data.

## WordPress privacy integration

Membership must integrate with WordPress personal-data exporter/eraser mechanisms where applicable.

Erasure can result in:
- delete;
- anonymize/pseudonymize;
- retain with reason where operational/legal policy requires;
- identify external-provider action separately.

Active authorization/business relationships are not silently destroyed by a generic erasure callback without an explicit policy decision.

## Backup interaction

Live erasure does not rewrite historical backup archives. Product documentation/UI must disclose that restoring an old backup can reintroduce older PII and may require post-restore privacy cleanup.

## User deletion

WordPress user deletion must run Membership impact resolution for active Enrollment, team ownership/seats, current Entitlements and historical anonymization rather than uncontrolled cascading deletion.

## Configurability

Sites can choose supported per-category modes such as:
- retain indefinitely;
- retain for duration;
- terminal + duration;
- anonymize after duration;
- derived current state only;
- disabled/no collection.

The product does not claim one default satisfies every jurisdiction/industry.

## Consequences

Positive:
- safer privacy defaults for logs/provider payloads;
- preserved access explainability;
- predictable exporter/eraser behavior;
- retention becomes auditable configuration.

Costs:
- cleanup/anonymization jobs and race handling are required;
- backup restore requires privacy reconciliation awareness;
- external provider deletion cannot be pretended to occur locally.

## Evidence still required

Future authorized tests must prove exporter/eraser batching, retention cleanup races, user deletion/team ownership behavior, backup restoration implications and provider-reference handling.

## Supporting document

`docs/MODULES/MEMBERSHIP-PRIVACY-RETENTION-DEFAULTS.md`

## Development gate

No exporter/eraser/cleanup job/runtime storage is authorized by this ADR. ADR-0014 remains controlling.