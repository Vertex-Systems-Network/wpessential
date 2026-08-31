# WPEssential — Privacy, Data Classification & Retention Contract

Status: Phase 0 planning. No runtime implementation authorized.

## Goal
Make privacy behavior explicit before schemas are implemented. WPEssential modules may process identity, submissions, messages, billing references, IPs, diagnostics and backups; these require classification, minimization, export/erase behavior and retention ownership.

This document is technical/product planning, not jurisdiction-specific legal advice.

## Data classes
### Class P0 — Public / non-sensitive configuration
Examples:
- public CPT/taxonomy labels;
- public listing templates;
- public documentation settings.

### Class P1 — Internal configuration
Examples:
- unpublished definitions;
- admin menu layouts;
- workflow structure;
- internal connection metadata excluding credentials.

### Class P2 — Personal data
Examples:
- user IDs/profile fields;
- form submissions containing person data;
- membership enrollment history;
- chat participant/message metadata;
- IP/device/access log data;
- support ticket identity/contact data.

### Class P3 — Sensitive/credential/security data
Examples:
- API secrets/OAuth tokens;
- password-equivalent tokens;
- signed download keys;
- application-password material references;
- security recovery tokens;
- secrets used for webhook verification.

P3 values belong in Vault/private token stores and are never generic dynamic tokens, logs, support bundles or ordinary exports.

### Class P4 — High-impact business/private content
Examples:
- protected member files;
- private chat content;
- private form uploads;
- backup archives;
- potentially sensitive custom-table records.

Access depends on resource policy, not merely module capability.

## Data minimization
Every persisted field must answer:
- why is it needed?
- who uses it?
- retention duration/condition?
- whether value can be derived instead of stored?
- whether hash/reference is enough?
- export/erase behavior?

Do not store raw provider payload forever merely because it was received. Keep normalized necessary fields; if raw event retention is needed for reconciliation/debugging, classify and expire it.

## Module ownership/retention responsibility
- Membership owns enrollment/entitlement/override/team/invite/provider-event retention policy.
- Forms owns entries/uploads where WPE storage is used.
- Chat owns conversations/messages/attachments/read state.
- Notifications owns notification/delivery/read metadata.
- Workflow owns run/step logs.
- Connections owns delivery logs; Vault owns secrets.
- Protector owns access/security logs.
- Support owns local cached support metadata; service-side tickets follow service policy.
- Backup owns local catalog/manifests, while actual archive retention is destination policy plus WPE schedule rules.

## Retention configuration
Where meaningful a module should support:
- keep indefinitely;
- retain for configured duration;
- delete/anonymize after terminal state + duration;
- per-category retention rather than one global number;
- legal/administrative hold adapter only if a real product requirement emerges.

Defaults should be conservative and documented; Phase 0 does not invent one universal duration.

## WordPress personal-data export/erase integration
Modules storing personal data should register with WordPress privacy exporter/eraser mechanisms where applicable.

Export may include:
- membership enrollments/history appropriate to user;
- form entries owned/identified as that user where policy permits;
- chat participation/message data subject to other participants/legal constraints;
- notification preferences/history;
- selected audit/access data attributable to user where export is appropriate.

Erase must distinguish:
- delete;
- anonymize/pseudonymize;
- retain for legitimate operational/legal reason;
- unlink external provider reference.

Never erase records in a way that corrupts accounting/access/security history without a defined policy.

## Membership privacy
Store external billing IDs/status facts necessary to reconcile access, but not card credentials.

Provider webhook/raw data:
- signature verification metadata may be retained;
- duplicate/replay IDs retained as needed for idempotency window;
- unnecessary payer/payment payload fields should not be copied into WPE tables.

Enrollment history may require longer retention than active entitlement cache; keep these as different stores/concerns.

## Chat privacy
- access checks on every conversation/message operation;
- retention policy per site/conversation type where practical;
- attachment access follows conversation authorization;
- search index must not become an authorization bypass;
- deletion/edit history behavior explicitly configured/audited where product requires moderation records.

## Forms privacy
Each form should be able to declare:
- store entries yes/no;
- retention;
- file retention;
- field classification;
- consent field purpose/reference;
- whether IP/user-agent is stored;
- whether data is sent to external integrations.

Do not collect IP/user-agent by default unless product/security purpose requires it.

## Logs/audit
Audit logs should store identifiers and safe summaries, not full sensitive payloads.

Examples:
Good: `user 42 updated membership plan UUID ...`.
Bad: store full form body, chat message, secret token or protected document in audit JSON.

IP logging has privacy impact and should be purpose-limited/retained separately where feasible.

## Diagnostics/support bundles
Before upload/send:
- preview categories;
- redact secrets/tokens;
- exclude user content by default;
- include only necessary environment/plugin/error metadata;
- explicit consent;
- record what was transmitted.

## Backups
Backups may contain P2/P3/P4 data.
- encrypt archive where configured/available;
- destination credential is Vault-managed;
- transport security required;
- secure deletion is provider/filesystem dependent and must not be overclaimed;
- backup retention can outlive live-record erase, so privacy docs must state backup restoration implications.

## Imports
Imported personal data receives the same classification/policy as native-created data. Import must not bypass consent/retention/access rules merely because source is CSV/XML/API.

## Exports
Configuration export excludes secrets by default.
Data export requires capability/resource policy and must prevent spreadsheet formula injection in CSV-like outputs.

## Telemetry
No hidden telemetry.
If product analytics/telemetry is ever added:
- separate ADR/product decision;
- explicit disclosure/consent according to distribution requirements;
- minimal event schema;
- no sensitive content/secrets;
- disable option;
- documented destination/retention.

## AI usage
External AI provider integration must classify outbound data before transmission.
Default:
- definitions/schema may be allowed after opt-in;
- P2/P4 content requires explicit context/purpose controls;
- P3 secrets prohibited except narrowly scoped connector mechanics where unavoidable and separately reviewed;
- model provider cannot become a covert data export path.

## Retention job safety
Cleanup jobs:
- are chunked/idempotent;
- log counts/categories, not deleted sensitive content;
- honor active holds/configuration;
- do not delete referenced records before referential policy resolves them;
- can resume after interruption.

## Module implementation gate
Each module must document before implementation:
- data classes stored;
- lawful/product purpose assumption;
- storage owner;
- retention default/options;
- exporter/eraser behavior;
- anonymization/deletion semantics;
- log behavior;
- backup/export implications;
- external processors/providers;
- user/admin privacy controls;
- tests for unauthorized export/erase and retention cleanup.