# WPEssential — Privacy & Data Lifecycle Executable Evidence Protocol

Status: **Phase 0 evidence specification / NOT AUTHORIZED FOR EXECUTION**  
Date: 2026-08-28  
Work package: `P0-M00-WP27`  
Related: ADR-0014, ADR-0024, ADR-0060, ADR-0069, ADR-0141, ADR-0142, `docs/PRIVACY-DATA-CLASSIFICATION-RETENTION.md`, `docs/PRIVACY/PER-MODULE-DATA-RETENTION-MATRIX.md`, Vault, Audit, Import/Export, Backup/Restore, Site Lifecycle, WordPress personal-data exporter/eraser integration.

## 1. Purpose

This is the canonical future executable-evidence contract for WPEssential's **local WordPress privacy and data lifecycle**.

It verifies that P0–P4 classification, data ownership, minimization, export, erase/anonymize, retention, cleanup, derived-data invalidation, backup/restore reconciliation and Multisite boundaries work consistently across modules.

The protocol freezes **PDL-01…PDL-176**.

**Executed: 0/176.**

This protocol is technical/product evidence planning, not jurisdiction-specific legal advice and not a substitute for site-owner legal analysis.

It does **not** replace:
- `REMOTE-SERVICE-PRIVACY-RETENTION-EVIDENCE-PROTOCOL.md` for WPE-controlled remote services;
- Vault evidence for credential secrecy;
- Audit evidence for Audit-specific retention/integrity;
- module-specific privacy/security fixtures;
- provider-specific deletion/export contracts.

No exporter, eraser, retention job, cleanup, database mutation, WordPress runtime fixture, provider call, backup restore, Multisite operation or benchmark is authorized by this document.

---

## 2. Truth boundaries

The following remain separate:

`Data classification ≠ storage location ≠ owner ≠ access authorization ≠ retention policy ≠ export eligibility ≠ erasure eligibility ≠ anonymization ≠ external deletion ≠ backup expiry ≠ secure erasure proof`

Also:
- WordPress user deletion ≠ universal deletion of all WPE records;
- site deletion ≠ privacy erasure;
- module disable ≠ data deletion;
- local erase ≠ remote-provider deletion;
- live-data erase ≠ retroactive rewrite of historical backups;
- removing an index/cache ≠ deleting canonical source data;
- a user being referenced in a record ≠ unconditional right to delete integrity-critical shared history;
- encryption ≠ no privacy obligation;
- hashed/pseudonymous data ≠ automatically anonymous data;
- consent-field presence ≠ proof of legal validity for every processing purpose;
- technical retention option ≠ jurisdiction-specific legal retention advice.

---

## 3. Canonical P0–P4 classes

- `P0` — public/non-sensitive configuration;
- `P1` — internal configuration;
- `P2` — personal data;
- `P3` — sensitive/credential/security material;
- `P4` — high-impact business/private content.

P3 belongs in Vault/private token stores where applicable and must not become generic dynamic tokens, logs, ordinary exports, support bundles, Event payloads or Audit metadata.

Mixed records/blobs require field-level classification; one generic label cannot hide a P3/P4 field inside a P1 object.

---

## 4. Certification classes

Certify independently:

- `PDL-C` — classification, purpose and ownership;
- `PDL-M` — minimization and storage boundaries;
- `PDL-A` — access/export authorization and data-subject resolution;
- `PDL-E` — erase/anonymize/unlink semantics;
- `PDL-R` — retention, holds and cleanup jobs;
- `PDL-D` — derived data/cache/search/log lifecycle;
- `PDL-B` — backup/import/export/restore implications;
- `PDL-X` — external processors/AI/telemetry/support boundaries;
- `PDL-S` — Multisite/site-lifecycle isolation;
- `PDL-O` — concurrency/failure/observability/scale.

Passing one class never implies another.

---

# 5. Fixed executable fixture matrix

## A. Classification, purpose, ownership & schema inventory — PDL-01…PDL-16

### PDL-01 — P0 classification
Public configuration is identified as P0 only when no private/internal/personal/secret field is embedded.

### PDL-02 — P1 classification
Internal configuration remains non-public by default and does not become personal-data export merely because an administrator created it.

### PDL-03 — P2 classification
Synthetic profile/submission/member/message data is correctly marked personal and participates in owner-defined privacy flows.

### PDL-04 — P3 classification
Synthetic API/OAuth/recovery/signing secret is classified P3 and routed to Vault/private token contract rather than ordinary field storage.

### PDL-05 — P4 classification
Private member file/chat/form upload/business-private row is classified high-impact and protected by resource Policy.

### PDL-06 — Mixed-record field classification
A JSON/document row containing P1 + P2 + P3/P4 fields preserves field-level classification and destination-specific redaction.

### PDL-07 — Explicit data owner
Every persisted module record category resolves to one canonical owner responsible for retention/export/erase decisions.

### PDL-08 — Shared-reference ownership
Consumer stores stable reference/projection only; it cannot claim ownership of source module record merely by referencing it.

### PDL-09 — Derived-data ownership
Cache/search/index/projection is marked derived with source owner and invalidation/erase obligations.

### PDL-10 — External-reference ownership
Provider/customer/subscription IDs remain local references; provider-owned source data/deletion state remains separate.

### PDL-11 — Purpose declaration
Persisted P2/P3/P4 field/category maps to documented technical/product purpose before storage.

### PDL-12 — No-purpose field rejection
A newly introduced personal/sensitive field without purpose/owner/classification fails readiness/schema governance.

### PDL-13 — Retention owner declaration
Every persisted category has explicit retention owner/mode even when exact duration remains configurable/evidence-gated.

### PDL-14 — Export/erase behavior declaration
Every P2/P4 and relevant P1 reference states export/erase/anonymize/retain/unlink behavior.

### PDL-15 — External processor declaration
Any data category sent externally names processor/connection/purpose category; hidden generic outbound sink is rejected.

### PDL-16 — Schema/version migration classification
Schema revision cannot silently downgrade P3/P4/P2 classification or drop lifecycle policy without explicit migration/review.

---

## B. Collection & data minimization — PDL-17…PDL-32

### PDL-17 — Minimum required fields
Representative record stores only documented fields required for purpose; optional diagnostics/content are absent by default.

### PDL-18 — Derived-vs-stored value
Value safely derivable from canonical source is not redundantly persisted without justified performance/history purpose.

### PDL-19 — Identifier/reference instead of payload
Job/Event/Audit/Notification/Workflow uses safe identifiers rather than copying full Form/Chat/private content where reference suffices.

### PDL-20 — IP default off
Module that does not require IP for explicit security/product purpose does not collect/store it merely because request exposes it.

### PDL-21 — User-agent default off/minimized
User-agent/device data is absent or bounded/purpose-specific, not universal indefinite logging.

### PDL-22 — Raw provider payload minimization
Normalized required provider facts persist; raw body retention is bounded and explicitly justified where reconciliation requires it.

### PDL-23 — Request/response body logging off
REST/webhook/integration/error logging does not generically store bodies containing P2/P3/P4.

### PDL-24 — Message/body logging off
Email/chat/form bodies are not copied into generic operational logs by default.

### PDL-25 — Password exclusion
Password/password-equivalent value cannot enter ordinary WPE tables, logs, events, exports, diagnostics or Audit.

### PDL-26 — Token/secret exclusion
OAuth/API/recovery/webhook/signing/private-download reusable secrets stay in P3/Vault/private stores and are redacted from generic destinations.

### PDL-27 — Payment-card exclusion
Full card/credential data cannot enter WPE domain storage or diagnostics; safe provider IDs/status facts only.

### PDL-28 — Temporary upload minimization
Temporary import/form/support artifacts use explicit bounded lifecycle and are not retained forever after finalization/failure.

### PDL-29 — Preview/test data separation
Preview/test-send/test-form fixtures do not persist into production business histories unless explicitly requested and labeled.

### PDL-30 — Development diagnostic depth
Development/test may expose deeper authorized diagnostics, but production defaults remain minimized/redacted.

### PDL-31 — Data copy via clone
Clone operation inventories copied P2/P3/P4 and applies environment/credential/revalidation policy rather than assuming copy is harmless.

### PDL-32 — Data inventory completeness
A future storage inventory reconciles code/schema/options/meta/files/caches/search indexes/queues/remote refs against documented module matrix; undocumented stores block certification.

---

## C. Storage, access, visibility & authorization — PDL-33…PDL-48

### PDL-33 — P3 Vault reference storage
Ordinary owner record stores secret UUID/reference plus safe metadata only; plaintext cannot be read back through normal UI/API.

### PDL-34 — Private file storage
P4 protected files/uploads are not publicly reachable solely by uploads URL/path knowledge.

### PDL-35 — Field-level authorization
Viewing/editing one record does not automatically expose all P2/P4/P3-derived fields; field/resource Policy applies where required.

### PDL-36 — Wrong-user IDOR
User/administrator permitted for Person A changes identifier to Person B and is denied according actual authority.

### PDL-37 — Wrong-site IDOR
Site A actor cannot export/read/erase Site B personal/private record through altered site/resource coordinates.

### PDL-38 — Network-vs-site authority
Network Admin/Super Admin/site Admin access follows explicit WPE Policy and target scope; network identity alone is not unrestricted payload access.

### PDL-39 — Shared user different site roles
One global WP user with different site membership/roles receives site-specific data visibility.

### PDL-40 — Resource existence privacy
Denied actor does not receive sensitive existence/count/owner details beyond safe policy.

### PDL-41 — Search authorization
Search index/result path reauthorizes source/resource and cannot expose stale revoked P2/P4.

### PDL-42 — Cache authorization
Personal/private cache keys include necessary principal/scope/policy generation; public cache cannot contain protected personalization.

### PDL-43 — Admin list/column privacy
Admin Columns/list tables obey target Policy and do not expose protected fields to an actor merely allowed to open list screen.

### PDL-44 — Dynamic Listing privacy
Listing/pagination/count/cache respects source authorization and cannot reveal hidden record counts/items through side channels beyond accepted policy.

### PDL-45 — Diagnostic read authorization
Diagnostics can show safe counts/categories without granting raw private content access.

### PDL-46 — Privacy-settings authorization
Only appropriate site/network authority can change retention/privacy configuration; UI visibility is not authorization.

### PDL-47 — Export/erase request authorization
Administrative personal-data actions require expected WordPress/WPE authority and target-site Policy; arbitrary REST/Ability callers cannot invoke them.

### PDL-48 — Audit-safe privacy action
Privacy action Audit stores actor/subject/category/count/result/reason references only, not erased/exported private content.

---

## D. WordPress personal-data export & subject resolution — PDL-49…PDL-64

### PDL-49 — Exporter registration
Applicable modules register through supported WordPress privacy exporter mechanism only where they truly own/export user-linked data.

### PDL-50 — Subject by email/user mapping
Synthetic data-subject resolution maps safely to intended WordPress user/site identities without broad email-only cross-site leakage.

### PDL-51 — Guest form subject mapping
Guest submission is exportable only when supported identification/verification rule links it to requester; arbitrary matching string is insufficient.

### PDL-52 — Shared conversation export
Chat export returns subject-appropriate content/metadata while respecting other participants/private moderation constraints.

### PDL-53 — Membership export
Enrollment/history/provider references are exported according policy without card/payment secrets or unrelated team/member data.

### PDL-54 — Notification export
User preferences/history/read state export does not leak other recipients/fan-out internals.

### PDL-55 — Form export
Only fields/entries mapped to subject and allowed by form/site policy export; P3/internal anti-spam secrets are excluded.

### PDL-56 — Profile export
Custom profile fields integrate with WP exporter while password/session/application-password/security internals remain excluded.

### PDL-57 — Audit/access-data export
Only explicitly export-eligible subject-linked Audit/security categories participate; security necessity/third-party privacy boundaries remain intact.

### PDL-58 — Pagination/chunking
Large exporter uses WordPress-compatible bounded pages/chunks and deterministic continuation without memory blow-up.

### PDL-59 — Export duplicate prevention
Repeated pages/retries do not duplicate or skip records because ordering/cursor identity is stable.

### PDL-60 — Export field redaction
P3, internal-only security metadata and unrelated P4 fields are excluded/redacted server-side.

### PDL-61 — Export authorization snapshot
Long-running export rechecks scope/policy where required and cannot silently continue after site/user authority revocation if contract requires live authorization.

### PDL-62 — Export file security
Generated export artifacts inherit private access, bounded retention and safe filename/MIME/download semantics.

### PDL-63 — Export cleanup
Expired/finished temporary export artifact is deleted/invalidated according explicit retention and failure-retry policy.

### PDL-64 — Export completeness report
Output/report identifies included categories and intentionally retained/excluded categories without claiming universal provider/backup deletion/export authority.

---

## E. Erase, anonymize, pseudonymize & unlink — PDL-65…PDL-80

### PDL-65 — Eraser registration
Applicable modules register WordPress erasers only for data they own/control locally.

### PDL-66 — Delete semantic
Disposable user-linked record is physically/logically deleted only through owner API and referential policy.

### PDL-67 — Anonymize semantic
Integrity-relevant history replaces direct identity fields with documented anonymized/pseudonymous representation without falsifying business/security history.

### PDL-68 — Retain-with-reason semantic
Record retained for explicit operational/security/integrity policy is reported as retained rather than falsely “erased.”

### PDL-69 — External-reference unlink
Local provider reference is unlinked/deleted only when allowed; this does not claim remote provider deletion unless independently confirmed.

### PDL-70 — Pseudonymization reversibility truth
If mapping/key exists that can re-identify, data remains classified personal/sensitive as appropriate and is not described as anonymous.

### PDL-71 — Shared message/record integrity
Subject erasure cannot silently corrupt records shared with other users; owner-defined redact/anonymize/retain behavior is enforced.

### PDL-72 — Relation cleanup
Personal entity erase updates relation edges/pivots through Relations owner policy; no orphan/wrong-target leakage.

### PDL-73 — Role/profile cleanup
Site-specific role/profile personal data cleanup does not delete global WP identity or sibling-site memberships unintentionally.

### PDL-74 — Active entitlement/session safety
Privacy operation does not leave stale active Membership/session/invite/access cache that continues authorization unexpectedly.

### PDL-75 — Token revocation before record cleanup
Relevant invite/recovery/private-access tokens are revoked/expired before/with subject cleanup according owner semantics.

### PDL-76 — Derived cache removal
Erase/anonymize invalidates cached/projection copies so old P2/P4 cannot remain served after canonical change.

### PDL-77 — Search index removal
Search entries are deleted/reindexed according source lifecycle and cannot resurrect erased content.

### PDL-78 — Failed partial erase
Partial failure returns truthful per-module/category results and resumable state; never claims complete erasure.

### PDL-79 — Erase idempotency
Retry after crash/timeout safely converges without deleting unrelated records or corrupting history.

### PDL-80 — Cross-module owner boundary
Privacy coordinator invokes module owner contracts; it never directly bulk-deletes another module's private tables/rows as a shortcut.

---

## F. Retention policy, holds & cleanup jobs — PDL-81…PDL-96

### PDL-81 — Definition retention
Definition/configuration retention remains separate from runtime personal/private value retention.

### PDL-82 — Operational retention
Run/log/delivery/reconciliation records have configurable/bounded operational retention where specified; no hidden indefinite default.

### PDL-83 — User-linked retention
User-linked records resolve module-specific terminal state + duration/mode rather than one global number.

### PDL-84 — External-reference retention
Provider identifiers/raw events remain only as long as reconciliation/history/idempotency purpose requires.

### PDL-85 — Archive retention
Backup archive lifecycle uses Backup schedule/destination policy, not live-record retention job.

### PDL-86 — Multiple-policy precedence
Record/category subject to multiple retention/integrity/security rules follows explicit stricter/owner-defined precedence; no shortest-duration guessing.

### PDL-87 — Retention change authorization
Changing a site/network retention policy requires correct authority and records safe Audit evidence.

### PDL-88 — Retention change non-retroactive/retroactive truth
UI clearly states whether new policy recalculates existing records or only future records; implementation matches.

### PDL-89 — Hold application
If hold feature exists, hold is explicit, scoped, authorized, auditable and not a hidden forever-retention default.

### PDL-90 — Hold release
Release recomputes eligibility safely and does not accidentally purge unrelated records immediately.

### PDL-91 — Cleanup batch bounds
Retention cleanup uses bounded batches, checkpointing/backpressure and avoids giant web-request deletes.

### PDL-92 — Cleanup crash retry
Crash mid-batch resumes idempotently and preserves target scope/eligibility.

### PDL-93 — Concurrent write during cleanup
New/updated record crossing eligibility boundary is not wrongly deleted due stale candidate list/precondition.

### PDL-94 — Referenced-record guard
Cleanup does not delete source row required by active relation/workflow/reconciliation without owner-defined policy.

### PDL-95 — Cleanup observability
Reports counts/categories/oldest eligible/errors/correlation but never deleted private bodies/secrets.

### PDL-96 — Module disable/uninstall
Disable pauses processing but preserves retained data; uninstall follows explicit cleanup level/ownership policy, not automatic universal purge.

---

## G. Derived data, caches, indexes, queues, logs & artifacts — PDL-97…PDL-112

### PDL-97 — Cache inherits classification
Cache storing P2/P4 remains protected and has no longer lifecycle than justified source use.

### PDL-98 — Cache invalidation on erase
Subject erase/anonymize invalidates all registered derived cache namespaces/generations.

### PDL-99 — Search index classification
Index terms/document fragments inherit source privacy and authorization class.

### PDL-100 — Search retention
Index cleanup is coupled to source retention/erase and does not become shadow archive.

### PDL-101 — Query/result cache
Saved Query definition is P0/P1, but cached result classification follows source rows/principal scope.

### PDL-102 — Job payload minimization
Long-lived Job stores references/typed safe values where possible; no secret/private body duplication just for convenience.

### PDL-103 — Workflow run payload minimization
Workflow history/context retention is bounded/classified and avoids full source-object snapshots unless explicitly required.

### PDL-104 — Notification/email delivery logs
Recipient/status metadata is minimized; rendered body/attachments are not generic long-retention logs.

### PDL-105 — Event Inbox raw payload
Raw external payload retention is bounded and separately classified from normalized event facts.

### PDL-106 — Audit separation
Audit holds safe identifiers/summaries according AUD; erasing source content does not copy it into Audit as preservation shortcut.

### PDL-107 — Diagnostic logs
Stack/error traces are operational artifacts with separate shorter retention and redaction, not permanent Audit/domain history.

### PDL-108 — Temporary import artifacts
Uploaded files/chunks/intermediate maps have explicit temp retention and cleanup after success/failure/cancel.

### PDL-109 — Temporary backup staging
Staging parts/archive temp files are protected and removed according Backup run state; failure cannot leave public orphan.

### PDL-110 — Private derivative files
Watermarked/rendered/private derivatives follow source ownership/access/retention and cannot outlive source indefinitely without policy.

### PDL-111 — Browser/client persistence
Sensitive admin data is not stored indefinitely in localStorage/session caches or frontend bundles beyond required UX contract.

### PDL-112 — CDN/object cache invalidation
Where private/protected content uses external cache/object delivery, revoke/erase invalidation behavior is explicit and evidence-scoped.

---

## H. Import, export package, backup & restore reconciliation — PDL-113…PDL-128

### PDL-113 — Imported classification inheritance
Imported field/record receives same classification/owner/access/retention rules as native-created equivalent.

### PDL-114 — Import cannot bypass P3 routing
CSV/XML/package cannot insert secret plaintext into ordinary field when schema declares Vault/P3 reference.

### PDL-115 — Import consent metadata truth
Imported consent/reference metadata is preserved as source fact but never upgraded into universal legal consent claim.

### PDL-116 — Import duplicate personal identity
Identity mapping/conflict is explicit; wrong existing user/site record is not overwritten through email/name guess.

### PDL-117 — Configuration export excludes secrets
Portable configuration replaces Vault values with references/placeholders and does not leak P3.

### PDL-118 — Data export authorization
Bulk data export applies source Policy, site/network scope, redaction and bounded job semantics.

### PDL-119 — CSV injection safety
CSV-like exports neutralize spreadsheet formula injection while preserving data meaning.

### PDL-120 — Export temporary retention
Generated package/file has explicit private storage/download authorization and expiry/cleanup.

### PDL-121 — Backup classification
Backup manifest marks that archive may contain P2/P3/P4; catalog does not expose archive contents.

### PDL-122 — Backup encryption/credential boundary
Archive/destination security follows Backup/Vault contracts; privacy protocol does not falsely infer secure deletion from encryption.

### PDL-123 — Live erasure vs existing backups
Erase of live data reports that existing backups may retain prior copy until backup retention expires; no false retroactive-erasure claim.

### PDL-124 — Restore reintroduction detection
Restore of old backup can reintroduce previously erased/anonymized data and triggers post-restore privacy reconciliation mechanism/report where supported.

### PDL-125 — Restore cannot erase current privacy history
Restore operation/reconciliation remains visible in current Audit/lifecycle chronology and does not silently reset privacy state claims.

### PDL-126 — Destination deletion semantics
Backup/provider deletion is reported according provider capabilities; “secure erase” is not claimed without evidence.

### PDL-127 — Cross-site package mapping
Package imported/restored to another site/network remaps subject/scope/owner references explicitly; no sibling-site data merge by numeric ID alone.

### PDL-128 — Clone/staging privacy
Staging clone receives environment-specific outbound/credential/access safeguards and does not silently process copied personal data as production authority.

---

## I. External processors, AI, telemetry, support & remote boundary — PDL-129…PDL-144

### PDL-129 — No hidden telemetry
Fresh Free/local usage emits no analytics/telemetry solely from local privacy/Audit/module activity; RS remains remote-network proof owner.

### PDL-130 — Telemetry future-gate
If telemetry feature exists later, separate disclosure/consent/schema/destination/retention control is required; account link is not consent.

### PDL-131 — Diagnostics preview
Before Support upload, user can see categories/sensitivity/redaction summary according PLT/RS contract; generating locally is distinct from transmitting.

### PDL-132 — Diagnostics redaction
Synthetic P2/P3/P4 content is excluded/redacted by default from remote-support bundle unless explicitly necessary/authorized.

### PDL-133 — External connection purpose
Sending Form/Member/Chat/other content through Connection requires explicit integration/action definition and destination scope.

### PDL-134 — Provider field mapping
Only selected/required fields are transmitted; connector does not append unrelated site/user/content inventory.

### PDL-135 — Provider credentials
Outbound processor credential remains Vault-owned and is never copied into payload/log/evidence export.

### PDL-136 — External deletion truth
Local UI distinguishes `local deleted`, `remote deletion requested`, `remote deleted/confirmed`, `retained by provider policy`, and `unknown` where applicable.

### PDL-137 — External export truth
Local WordPress exporter cannot claim to export every remote provider/account record without explicit provider/service contract.

### PDL-138 — AI P0/P1 opt-in context
Definition/schema context may be transmitted only under explicit AI feature/connection policy; local AI capability does not imply background upload.

### PDL-139 — AI P2/P4 context
Personal/private content requires explicit purpose/scope controls and normal resource authorization before outbound transmission.

### PDL-140 — AI P3 prohibition
P3 secrets are prohibited from generic model prompt/context/tool arguments except narrowly reviewed connector mechanics that never expose secret to model content.

### PDL-141 — AI result storage
AI output containing/derived from P2/P4 inherits appropriate classification/retention rather than being treated public because model generated it.

### PDL-142 — Remote search privacy
Docs/Support/remote-search query does not silently append current editor content, diagnostics, user data or site inventory.

### PDL-143 — RS handoff
Any WPE-controlled remote-service privacy claim is delegated to RS-001…RS-030 and cannot be promoted by local PDL pass.

### PDL-144 — Processor outage/retry
Retry queue preserves minimized payload/reference, authorization/preconditions/idempotency and retention; outage cannot create hidden indefinite private-data queue.

---

## J. Multisite, site lifecycle & identity isolation — PDL-145…PDL-160

### PDL-145 — Site-owned privacy inventory
Same user/resource IDs on Sites A/B remain separate by explicit scope and module ownership.

### PDL-146 — Network-owned privacy inventory
Network-owned resources are classified once and site visibility/use rights do not create independent site ownership copies.

### PDL-147 — Site-specific exporter
Privacy export for Site A does not include Site B user-linked records merely because WordPress user is global.

### PDL-148 — Network-level exporter
Any network-wide export requires explicit network authority and clearly enumerated included sites/categories.

### PDL-149 — Site-specific eraser
Site A erase/anonymize does not remove Site B membership/profile/role/message/form data unless explicit network-owned identity policy requires it.

### PDL-150 — Global WP user deletion
Global user deletion/reassignment is handled separately from WPE site-level privacy actions and does not guess cross-site ownership.

### PDL-151 — Site deletion is not erasure
Deleting/uninitializing site invokes lifecycle retention/cleanup policy but is not labeled universal personal-data erasure.

### PDL-152 — Site archive
Archive changes runtime access/processing but retained data follows configured privacy policy rather than immediate purge.

### PDL-153 — Site transfer
Transfer remaps scope/owner/privacy settings explicitly; old network-shared secret/provider references are not blindly copied.

### PDL-154 — Deleted/recreated numeric site ID
New site with reused numeric ID cannot inherit old site's personal-data inventory/retention authority solely by ID match.

### PDL-155 — Network retention default
Network default/lock/site override provenance is visible; site cannot weaken locked network security/privacy policy where not allowed.

### PDL-156 — Network cleanup fan-out
100+ site cleanup uses bounded child jobs/checkpoints and per-site target Policy; no one-request unbounded switch loop.

### PDL-157 — One-site failure
Failure on Site B does not falsely mark network privacy operation complete; A/C results remain independent.

### PDL-158 — Cross-site cache/search isolation
Erase/revoke on one site invalidates its derived data without exposing or deleting sibling site's private caches/indexes.

### PDL-159 — Clone/restore Multisite
Copied site/network personal data, OAuth/Vault refs, jobs and external processors are revalidated before active processing.

### PDL-160 — MSI/LC certification separation
PDL pass cannot promote MS1+/SL certification; MSI-01…160 and LC-01…96 remain independent prerequisites for Multisite claims.

---

## K. Concurrency, failure injection, scale & composite safety — PDL-161…PDL-176

### PDL-161 — Concurrent export + mutation
Record updated while export runs yields defined snapshot/version behavior and no unauthorized new data inclusion.

### PDL-162 — Concurrent erase + write
New write/interaction overlapping erase follows owner preconditions/generation and cannot resurrect deleted authorization/cache silently.

### PDL-163 — Concurrent two erase requests
Duplicate privacy requests converge idempotently and do not double-delete shared resources.

### PDL-164 — Cleanup vs restore
Retention cleanup overlapping Restore is serialized/guarded by lifecycle/recovery state to prevent wrong-generation deletion.

### PDL-165 — Cleanup vs import
Import-created records carry correct timestamps/policies and are not immediately purged due stale eligibility snapshot unless truly eligible.

### PDL-166 — DB failure mid-erasure
Partial DB failure records resumable per-owner state and retained categories; no false completion.

### PDL-167 — Object-cache failure
Canonical privacy decision/erase does not fall back to unsafe stale allow; derived invalidation failure is visible/recoverable.

### PDL-168 — Job crash
Long-running export/erase/retention job resumes from durable checkpoint with target subject/site/policy fingerprint.

### PDL-169 — Policy change mid-job
Long job detects retention/privacy policy revision where required and pauses/replans instead of using unsafe stale policy silently.

### PDL-170 — Large subject export
Synthetic high-record user export measures memory/query/job behavior; no unbounded single-request materialization.

### PDL-171 — Large subject erasure
Synthetic high-record erase uses bounded owner batches and records counts/failures without private payload logs.

### PDL-172 — High-volume retention
1M/10M+ candidate operational records benchmark selection/delete/checkpoint/index cost; thresholds are measured, not promised.

### PDL-173 — 100/1k/10k-site privacy fan-out
Where environment permits, measure coordinator enumeration/queue/backpressure and record practical limits; no unsupported large-network claim.

### PDL-174 — Error/privacy regression
Injected exception containing token/private content is normalized/redacted in UI/REST/Job/Audit/diagnostics and not persisted in privacy report.

### PDL-175 — Backup resurrection composite
Erase subject → create/retain old backup → Restore old backup. System must surface/reconcile reintroduced data and must not claim erasure remained universally effective.

### PDL-176 — Stop-the-line composite
Inject wrong-site target + P3 secret field + partial erase failure + stale cache + remote deletion timeout. Any cross-site mutation, secret exposure, stale authorization, fabricated complete erasure, hidden remote-success claim or silent indefinite retention is Critical.

---

## 6. Required evidence artifact per future run

Each executed fixture records:
- PDL fixture ID;
- WPE commit/version and Free/Pro pair;
- WordPress/PHP/DB versions;
- single-site/Multisite topology;
- object-cache/search/provider profile where relevant;
- subject identity fixture using synthetic data only;
- actor + target site/network scope;
- module/storage owner;
- P0–P4 field/category classification;
- purpose + retention policy revision;
- source/derived store inventory;
- expected export/erase/retain/unlink behavior;
- actual per-owner result;
- authorization/redaction assertions;
- Job/checkpoint/correlation/Audit IDs where relevant;
- backup/remote implications;
- timing/query/memory metrics where relevant;
- pass/fail/blocked/skipped/not-executed;
- known limitations and retained categories/reason.

Never use real customer secrets/payment data/private content in certification fixtures.

---

## 7. MUST NOT / stop-the-line rules

Stop the line on any of the following:
- P3 secret/password/token/card/reusable private URL enters generic table/log/Event/Audit/export/diagnostic bundle;
- wrong-user/site/network export or erase;
- UI hiding is treated as privacy authorization;
- Site Admin gains sibling/network personal data through global WP user identity/current blog/modified coordinates;
- module disable/site delete is falsely labeled complete personal-data erasure;
- erase deletes another module's/private shared data by direct bulk-table shortcut;
- shared history is corrupted to satisfy simplistic deletion without declared owner policy;
- search/cache/projection continues exposing erased/revoked P2/P4 beyond defined invalidation boundary;
- local erase is reported as confirmed remote provider deletion without authority/evidence;
- live erase is described as retroactively removing old backup copies;
- restore reintroduces erased data silently while system still claims complete erasure;
- privacy/export action disables Membership/security protections or leaves stale access token/entitlement/cache;
- hidden telemetry/AI/support upload is triggered by local feature use;
- retention/cleanup silently expands target scope or drops required records under failure;
- secure/cryptographic erasure is claimed where storage/provider semantics do not prove it.

---

## 8. Current evidence state

- Protocol documented: **PDL-01…PDL-176**.
- Executed: **0/176**.
- `PDL-C/M/A/E/R/D/B/X/S/O` certifications: **0**.
- exact jurisdiction-specific retention periods: **OUT OF SCOPE / NOT SELECTED**.
- product/default retention durations per category: **OPEN where not already fixed by domain contract**.
- WordPress exporter/eraser runtime registrations: **0 certified**.
- cross-module privacy coordinator implementation: **NOT IMPLEMENTED**.
- derived-store invalidation registry implementation: **NOT IMPLEMENTED**.
- retention cleanup runtime certifications: **0**.
- Multisite/privacy runtime certifications: **0**.
- remote WPE service privacy remains **RS 0/30**, independently certified.

## 9. Development gate

This protocol authorizes **no executable work**.

Do not register WordPress exporters/erasers, create privacy coordinator/retention jobs, mutate DB/files/caches/search indexes, execute imports/exports/backups/restores, invoke remote providers/AI/support services, create Multisite fixtures or benchmark storage until explicit owner development/executable-evidence consent is recorded under ADR-0014 and the Approval Ledger.
