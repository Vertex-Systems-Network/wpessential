# WPEssential — Error Taxonomy & Failure UX Contract

Status: Phase 0 planning. No runtime implementation authorized.

## Goal
Make errors consistent, actionable and safe across UI, REST, Abilities, Jobs and integrations.

## Error classes
### Validation
Examples: invalid slug, missing required field, unsupported option combination.
- user-correctable;
- field/path details allowed;
- no retry until corrected.

### Authorization
Examples: missing capability, policy deny, membership deny.
- safe generic message to unauthorized caller;
- richer admin diagnostic only with permission;
- never reveal protected resource details unnecessarily.

### Conflict
Examples: stale revision, duplicate key, concurrent seat assignment, state changed since form opened.
- include current version/conflict hint;
- support refresh/merge/retry where safe.

### Dependency
Examples: required module/provider/builder missing or incompatible.
- name dependency/version requirement;
- recovery action;
- module may enter degraded state.

### Integration/Auth
Examples: expired OAuth token, revoked API key, invalid webhook signature.
- no secret echo;
- connection/re-auth action;
- distinguish permanent configuration error from transient outage.

### Rate/Capacity
Examples: provider 429, membership capacity full, queue backpressure.
- retry-after or user action when known;
- do not spin retries indefinitely.

### Timeout/Network
- retry only for idempotent/safe operations;
- show provider/connection category, not internal stack.

### Data Integrity
Examples: missing relation target, checksum failure, corrupt archive, invalid migration state.
- stop unsafe write/restore;
- escalate severity;
- recovery/backup reference where possible.

### Migration/Compatibility
- incompatible Free/Pro/platform API;
- schema migration required/failed;
- safe degraded mode where possible.

### Internal Bug
Unexpected exception/invariant violation.
- correlation ID;
- production-safe user message;
- structured log with stack/context server-side according to environment;
- no SQL/secrets/paths exposed unnecessarily.

## Stable machine codes
Errors should have stable machine-readable codes independent of translated message.
Examples:
- `wpe_validation_invalid_slug`
- `wpe_auth_capability_denied`
- `wpe_conflict_revision_stale`
- `wpe_dependency_missing`
- `wpe_integration_auth_expired`
- `wpe_rate_limited`
- `wpe_data_checksum_failed`
- `wpe_migration_failed`
- `wpe_internal_unexpected`

Codes are namespaced and documented; consumers must not parse English text.

## Error envelope
Normalize where applicable:
- `code`
- localized `message`
- `category`
- `severity`
- `retryable`
- `field/path` details for validation
- `correlation_id`
- optional `recovery_action`
- optional safe `meta`

REST/Abilities/Jobs may serialize differently but preserve semantics.

## Severity
- info
- warning
- error
- critical

Critical means business/security/data integrity risk, not merely a red banner.

## Retry policy
Retries must be classified:
- never retry validation/authorization;
- retry transient network/rate errors with bounded backoff/jitter;
- retry writes only with idempotency/duplicate protection;
- migration/restore retries require explicit safe state;
- webhook duplicate delivery handled by idempotency, not treated as error.

## UI failure states
### Inline field
For local validation.
### Page/banner
For degraded module/dependency/migration issue.
### Toast
For transient success/non-blocking failure only; never the sole surface for critical error.
### Modal/confirmation
For recovery decision/destructive retry.
### Diagnostics link
For authorized technical details with correlation ID.

## Partial failure
Batch/workflow/import operations must report:
- total/succeeded/failed/skipped;
- per-item safe error code;
- retryable subset;
- whether transaction/compensation occurred;
- downloadable error report subject to privacy policy.

Do not report entire batch as success because 90% passed.

## Membership access denial UX
Access denial is not always an “error.” It is a policy result.
Possible outcomes:
- show login;
- show plan requirement/upgrade CTA;
- show drip availability date;
- show generic forbidden;
- redirect only when explicitly configured and loop-safe.

Never reveal plan/private-resource details to a caller who lacks permission to know them.

## Backup/restore errors
Differentiate:
- source read failure;
- archive creation failure;
- checksum mismatch;
- destination auth/network failure;
- restore preflight failure;
- extraction/file permission failure;
- DB import failure;
- post-restore health failure.

Recovery instructions depend on stage.

## Support diagnostics
Correlation ID and normalized error category should make support possible without asking users to paste full stack traces or secrets.

## Logging
Structured log fields:
- timestamp;
- code/category/severity;
- module/action;
- correlation/run/job ID;
- principal ID if appropriate;
- resource safe identifier;
- retry attempt;
- safe exception class/summary.

Redact:
- credentials/tokens;
- Authorization headers;
- passwords;
- private message/form content unless explicitly necessary and protected;
- payment-card data.

## Environment behavior
Development/test may expose deeper traces to authorized developers. Production UI never displays raw exception traces by default.

## Localization
Machine code remains stable English-like identifier; user messages are translatable. Do not build logic on translated text.

## Accessibility
Error summaries receive focus/announcement appropriately; field errors associate with controls; color is not sole signal.

## Acceptance gate
Every module spec must list expected validation, authorization, dependency, integration and integrity failure cases and map them to this taxonomy before implementation.