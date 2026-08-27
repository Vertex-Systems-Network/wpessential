# WPEssential — Event & Ability Catalog Contract

Status: Phase 0 planning. No runtime implementation authorized.

## Goal
Create one typed vocabulary for UI actions, workflows, REST, WP-CLI and future AI/MCP instead of module-specific ad-hoc callbacks.

## Naming
Use stable namespaced identifiers.

Abilities:
- `wpessential/<module>.<verb>`
- examples: `wpessential/cpt.create`, `wpessential/query.preview`, `wpessential/backup.restore`

Events:
- `wpessential.<module>.<entity>.<past_tense_event>`
- examples: `wpessential.membership.enrollment.activated`, `wpessential.form.entry.submitted`

Names are public contracts after stable release and require deprecation policy to change.

## Ability metadata
Each ability definition must include:
- stable ID;
- title/description;
- module owner;
- input JSON Schema;
- output JSON Schema;
- required capability/policy callback;
- read/write/destructive classification;
- idempotency semantics;
- dry-run support flag;
- synchronous/asynchronous execution mode;
- timeout/budget hints;
- audit category;
- sensitive-input/output annotations;
- availability/dependency requirements;
- version/deprecation metadata.

## Standard verbs
Prefer consistent verbs:
- `list`, `get`, `create`, `update`, `delete`
- `duplicate`, `publish`, `archive`, `restore`
- `preview`, `validate`, `explain`
- `run`, `retry`, `cancel`
- `export`, `import`
- `enable`, `disable`
- `attach`, `detach`
- `grant`, `revoke`

Do not invent synonyms per module without semantic reason.

## Destructive abilities
Must support, where meaningful:
- explicit destructive flag;
- precondition/version token;
- impact preview;
- dry run;
- confirmation/re-auth at UI policy layer;
- idempotency key or duplicate protection;
- audit record;
- recovery/rollback reference.

## Event envelope
All WPEssential domain events should normalize:
- event ID UUID;
- event type;
- event schema version;
- occurred-at UTC timestamp;
- site/network scope;
- actor/principal summary;
- correlation/causation IDs;
- entity type + stable entity ID;
- safe payload;
- source module;
- privacy/sensitivity classification.

Never put plaintext secrets, password-equivalent data, raw card/payment credentials or unnecessary message/file contents into generic event payloads.

## Event delivery semantics
Default assumption: **at-least-once**, not exactly-once.
Consumers must tolerate duplicate/replayed events using event IDs/idempotency.

Ordering is not universally guaranteed. Where order matters, consumers use entity revision/state version or source sequence metadata.

Critical state must be committed before non-critical emitted side effects. Event failure must not roll back an already valid primary authorization/business mutation unless transaction semantics explicitly require it.

## Initial event families

### Platform
- module enabled/disabled/degraded
- definition created/updated/published/archived/deleted
- definition import completed/failed
- migration started/completed/failed

### Content/Data
- CPT/taxonomy definition changed
- field schema changed
- relation attached/detached
- status transitioned
- custom-table row created/updated/deleted

### Forms/Workflow
- form entry submitted/validated/rejected
- workflow run started/completed/failed/cancelled
- workflow step completed/failed/retried

### Membership
- enrollment created/trialing/activated/grace/paused/expired/revoked
- cancellation scheduled/cancelled
- entitlement granted/revoked/recomputed
- membership access denied/override changed (diagnostic/audit class, not high-volume generic event by default)
- team seat assigned/released
- invitation issued/accepted/expired
- billing source linked/unlinked
- provider reconciliation drift detected/resolved

### Communication
- notification created/read/delivery attempted/delivered/failed
- email send requested/accepted/failed
- chat conversation/message created/read/edited/deleted/reported

### Operations
- backup started/completed/verified/failed/pruned
- restore started/completed/failed
- reset requested/completed/failed
- import started/completed/failed
- protected asset download denied/served (sampling/privacy rules required)

## Initial ability families
Every module should eventually expose only meaningful safe operations. Examples:

### CPT/Taxonomy
`list/get/create/update/duplicate/publish/archive/export/import/validate`

### Query
`list/get/create/update/preview/explain/execute`
`execute` must honor public/runtime budgets and policy.

### Membership
- plan list/get/create/update/publish/archive
- enrollment list/get/grant/revoke/pause/resume
- entitlement explain/recompute
- access explain/check
- team seat assign/release
- reconciliation preview/run

Access-check abilities must never become a way to reveal protected resource data.

### Backup
- destination test
- backup preview/run/cancel/status/verify
- restore preview/run/status

Restore is destructive/high-risk.

## AI/MCP exposure
Not every registered Ability is AI-exposed.

AI exposure uses a separate allowlist with default:
- read/explain/preview first;
- mutations opt-in;
- destructive abilities disabled unless explicitly approved;
- same principal permissions and Policy checks;
- model-provided args treated as untrusted input.

## Workflow exposure
Workflow actions consume Ability contracts where suitable, but workflow service credentials/principals are explicit. A workflow must not inherit creator's unlimited permissions forever by accident.

## Compatibility/versioning
- additive optional schema fields are preferred;
- breaking input/output changes require new ability/event schema version or ID;
- stable event consumers must ignore unknown additive fields;
- deprecation window documented before removal;
- Free/Pro compatibility must include Ability contract version ranges.

## Observability
Ability invocation records should normalize:
- ability ID/version;
- principal;
- duration;
- outcome/error category;
- async job/run ID;
- correlation ID;
- safe parameter summary;
- policy decision class.

## Implementation-entry checklist
Before a module implements abilities/events, its spec must enumerate:
- abilities owned;
- emitted events;
- consumed events;
- schemas;
- capability/policy;
- idempotency;
- async behavior;
- error taxonomy;
- privacy classification;
- versioning strategy.