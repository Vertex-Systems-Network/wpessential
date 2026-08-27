# WPEssential — Audit PT-D Retention, Index & Integrity Profile

Status: **Phase 0 paper architecture / no Audit table or logging runtime authorized**  
Related: ADR-0069, ADR-0071, Policy/Abilities, Membership Transition history, Job Service, Event Inbox, Site Lifecycle, privacy/retention contracts.

## Purpose

Define what WPE Audit is, what it is not, and the first future PT-D physical/index/retention profile without creating a misleading “log everything forever” or “tamper-proof local DB” design.

## Domain boundaries

### Audit Event
Durable, security/business-relevant explanation that an actor/system attempted or completed a meaningful action against a scoped WPE resource.

Examples:
- Definition publish/disable;
- role/capability mutation;
- Membership manual override/revoke;
- destructive Reset/Import action;
- Vault secret create/rotate reference event without secret value;
- site lifecycle destructive transition;
- Backup restore initiation/completion;
- Connection credential replacement metadata;
- Product License administrative link/disconnect/transfer action.

### Domain History
Structured business history required to reconstruct domain state, such as Membership Transitions or Workflow Runs. It stays in the owning domain. Audit links to it rather than duplicating the entire record.

### Operational Diagnostics
Shorter-lived errors, latency, worker/provider diagnostics and debug traces. They are not a permanent Audit trail by default.

### Security Event
Authentication/authorization/replay/SSRF/rate-limit/suspicious events may have a dedicated security classification/retention but can use the shared Audit infrastructure when schema semantics fit.

### High-volume access analytics
Not Audit by default. Detailed page/download/IP logging remains off unless a product purpose explicitly enables it.

---

## Physical topology

### AU1 — PT-D shared scoped Audit store — first/favored profile

One shared WPE append-oriented table family with explicit network/site scope.

Why PT-D is favored:
- one network migration path;
- centralized Network Admin diagnostics when authorized;
- easier correlation across WPE modules without table fan-out;
- site-row Backup/extract/delete can be policy driven;
- high-volume write patterns suit a dedicated runtime store rather than Definition/option/meta storage.

A PT-E comparison can still be run if large-network/privacy evidence indicates material benefit, but ADR-0071 already places Audit in PT-D direction; this document does not reopen that architecture absent evidence.

---

## Scope invariants

Every site-owned Audit Event has explicit:
- `network_id`;
- `site_id`;
- event UUID/internal ID.

Network-owned Audit Event has explicit network scope and no invented site ownership.

Current blog context is never durable Audit ownership.

A Site Admin can only read target-site Audit classes they are authorized to inspect. Super Admin/network role does not make every sensitive event payload automatically visible; event classification/redaction still applies.

---

## Candidate Audit Event envelope

Logical fields:
- internal numeric ID + stable event UUID;
- network/site scope;
- occurred/recorded UTC timestamp;
- actor class (`user`, `system`, `job`, `remote_service`, `cli`, registered extension);
- actor safe identity/reference;
- action/Ability key;
- target resource type + safe stable reference;
- result (`success`, `denied`, `failed`, `partial`, `unknown/reconciliation` where relevant);
- severity/classification;
- reason/error category;
- correlation/causation/request IDs;
- related domain event/transition/run UUID;
- source component/module/profile version;
- safe before/after/change summary where necessary;
- safe metadata document with schema version;
- retention class;
- privacy classification/redaction state;
- optional client/network evidence only when purpose allows;
- creation sequence/generation metadata if useful for integrity diagnostics.

Audit never stores:
- password/reset token;
- Vault plaintext/API secret;
- Authorization/Cookie headers;
- full card/payment secrets;
- reusable private signed URLs;
- entire arbitrary provider/webhook payload by default;
- complete database row dumps merely for convenience.

---

## Append-only application semantics

Normal application path treats committed Audit Events as immutable.

Allowed later transformations require explicit governance:
- privacy anonymization/redaction while preserving event meaning;
- retention purge;
- legal/security hold state where such product requirement is explicitly implemented;
- migration to a new schema with provenance.

No ordinary admin “edit audit record” feature.

Corrections are new events referencing the prior event, not silent rewrite.

---

## Integrity claim boundary

A local WordPress database Audit table is **not tamper-proof** against an actor with sufficient database/server/root authority.

Therefore WPE may claim:
- application append-only behavior;
- detectable schema/integrity inconsistencies;
- correlation/provenance;
- controlled retention;
- optional future tamper-evidence mechanisms when actually implemented and verified.

WPE must not claim cryptographic non-repudiation merely because rows have hashes.

Future optional evidence profiles may compare:
- per-row canonical content hash;
- batch/segment hash chaining;
- signed checkpoints;
- external/WPE-service or customer-controlled immutable checkpoint anchoring.

But any such profile must document key custody, rollback/backup behavior and what attacker class it detects. A hash chain whose key/checkpoint lives only beside the editable rows is not strong protection from a privileged DB/server attacker.

No integrity-chain mechanism is selected by this paper baseline.

---

## Before/after data minimization

Audit records action meaning, not every byte that changed.

Preferred safe summaries:
- changed field/key names;
- old/new non-sensitive enum/state;
- object/revision UUIDs;
- counts;
- content/config fingerprint;
- safe reason code;
- privileged actor identity.

For large Definition/content changes, link revisions/fingerprints rather than embedding full old/new payloads.

For Vault, log secret identity + operation + version/rotation result, never secret value.

For Membership, link transition/override identity rather than copying provider payload.

---

## Candidate index families

Exact index order/types remain evidence-gated.

Hot index families:
- scope + occurred/recorded time descending;
- scope + action key + time;
- scope + actor identity + time;
- scope + target resource type/reference + time;
- scope + result/severity + time;
- scope + correlation ID;
- scope + related domain event/run UUID;
- retention class + purge eligibility time.

Do not index arbitrary metadata JSON/text properties by default.

Admin search across free-form message text should not force a wide hot write-table FULLTEXT index without benchmark/use-case evidence. Structured filters come first.

---

## Retention classes

One universal retention period is rejected.

Paper classes:

### AR-S — security/authorization critical
Examples: capability changes, break-glass/recovery, destructive security settings, severe attack events.

### AR-A — administrative configuration
Definitions/settings/menu/status/integration configuration changes.

### AR-B — business/domain control
Manual Membership overrides, import decisions, operator reconciliation actions.

### AR-O — operational/recovery
Backup/Restore/Reset/job lifecycle administrative milestones.

### AR-D — diagnostic-short
Provider/worker/runtime technical diagnostic events that are useful operationally but not long-term Audit truth.

### AR-P — privacy-sensitive
Events containing IP/address/device/recipient or other personal evidence requiring stricter minimization and retention.

An event may have multiple classification flags but one effective retention policy chosen by the stricter applicable rule.

Exact default durations remain product/privacy/compliance decisions; no “forever” default is accepted merely because storage is cheap.

---

## IP, user-agent and request evidence

IP/user-agent are not automatically stored for every Audit event.

When a real security/operational purpose requires them:
- normalize/minimize;
- document privacy class/purpose;
- retain for bounded period;
- optionally store coarse/hash representation when sufficient;
- do not pretend a forwarded header is reliable client IP without trusted-proxy resolution.

Request body/headers are not generic Audit metadata.

---

## Failure semantics

Audit failure policy depends on action criticality.

### Security/destructive high-impact mutation
Future implementation should prefer a durable Audit write in the same transaction or a fail-safe journal/outbox boundary where practical. If the action cannot be safely audited and policy marks auditing mandatory, the mutation can fail rather than silently proceed.

### Lower-risk/non-transactional external action
Business action and Audit may not be atomically commit-able across systems. Store correlation/unknown state truthfully; do not pretend cross-system atomicity.

### Audit subsystem degraded
Admin receives diagnostics. WPE does not silently disable authorization or fabricate Audit success.

Exact fail-closed classes require future per-Ability policy.

---

## Relationship with JobService and Event Inbox

JobService execution history is not copied wholesale into Audit. Audit records meaningful administrative/security milestones and links Job/Attempt UUIDs.

Event Inbox stores verified external ingress. Audit can record security/reconciliation actions and link Event Inbox UUID; raw event body remains under Event Inbox retention.

Provider transport attempt logs remain Email/Connection domain operational evidence, not generic Audit duplicates.

---

## Site lifecycle

Archive/suspend does not erase Audit.

Before destructive site deletion:
- Site Lifecycle impact Plan identifies site-owned Audit retention categories;
- target-site Backup can include authorized Audit history according to profile;
- privacy erasure/redaction rules run where applicable;
- minimal network-owned deletion/transfer event can remain at network scope even after site rows are purged;
- another site's Audit rows are never affected by numeric resource ID collision.

Site transfer/migration preserves provenance and distinguishes original site/network identity from new ownership where needed.

---

## Backup/Restore semantics

Audit chronology must not be silently rewritten by Restore.

Restoring a site creates new current Audit events describing Restore/reconciliation.

If historical Audit rows from a Backup are imported:
- preserve original event UUID/origin timestamp/source backup identity;
- detect duplicates;
- mark import provenance;
- never make imported old rows appear as newly occurred actions;
- do not overwrite current post-backup Audit history.

A restore point cannot erase the evidence that a Restore itself happened in the current system history.

Exact external immutable retention, if ever offered, needs separate architecture.

---

## Query/UI behavior

Default views use structured filters:
- date range;
- site/network scope;
- actor;
- module/action;
- resource;
- result/severity;
- correlation;
- retention/privacy class where privileged.

Sensitive metadata is redacted according to viewer authority, not only hidden with CSS.

Exports are bounded, authorized, privacy-aware and JobService-backed for large ranges.

---

## Future evidence — NOT AUTHORIZED

Correctness/security:
- wrong-site Audit UUID/resource lookup;
- Site Admin vs Network Admin visibility;
- secret/header/payload redaction;
- denied action recording;
- transaction rollback: no false success Audit;
- crash around mutation/Audit boundary;
- duplicate correlation/event handling;
- privacy anonymize/purge;
- site delete/transfer;
- Backup/Restore chronology/provenance.

Scale:
- 1M / 10M / 100M rows where practical;
- sustained write bursts;
- actor/resource/time queries;
- network-wide authorized aggregation;
- purge by retention class/time;
- one noisy site;
- 100 / 1k / 10k-site networks.

Measure:
- write p50/p95/p99;
- query p50/p95/p99;
- rows examined/query plans;
- index/storage overhead;
- purge throughput/locking;
- write contention with business transactions;
- Backup/export cost;
- scope leak count (must be zero).

Optional tamper-evidence experiments, if later consented, must separately model DB-only attacker, server/root attacker, backup rollback and key/checkpoint compromise.

## Decision rule

AU1/PT-D is the favored future physical baseline under ADR-0071, but exact DDL/index/retention/failure policy remains evidence-gated. Audit completeness and secrecy/isolation are more important than retaining maximum payload volume.

## Development gate

No Audit table, migration, logger, integrity chain, external checkpoint, privacy exporter/eraser, Job execution, fixture or benchmark is authorized. ADR-0014 explicit owner consent remains required.