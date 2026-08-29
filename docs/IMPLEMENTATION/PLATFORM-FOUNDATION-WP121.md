# WPEssential — WP121 Platform Foundation

Status: **CURRENT / IMPLEMENTING — shared platform foundation active**  
Date: **2026-08-29**  
Approval: `GOV-OWNER-CONSENT-001` ACTIVE  
Predecessors: WP119 PASS / ADR-0214; WP120 PASS / ADR-0215  
Engineering contract: ADR-0216 ACCEPTED  
Atomic compiled registrations: ADR-0217 ACCEPTED  
Definition + Audit persistence: **ADR-0218 ACCEPTED**

## Goal

Establish the shared production-source platform foundation required before business modules can safely exist. WP121 owns reusable kernel, security, persistence, jobs, integration, WordPress bridge and observability primitives; it is not a business-feature milestone.

## Tranches 1–8 retained

Previously implemented and still canonical:
- Bootstrap / Kernel / Service Registry / Module lifecycle;
- immutable Definition / ExecutionContext / Policy / Ability / Event core;
- Audit logical foundation + bounded secret-safe metadata sanitizer;
- backend-neutral JobService contracts;
- bounded Action Scheduler capability probe;
- Secrets Vault reference contract, Asset Registry and Integration Registry;
- persistence/migration abstractions;
- WordPress Capability + Abilities API bridge;
- owner engineering conventions under ADR-0216.

ADR-0216 continues to require namespace `WPEssential`, canonical `frameworks/` PSR-4 source root, `wpessential_*` globals, `WPE_*` constants, exact `wpesential/apply_*` filters, `wpessential/hook_*` actions, a single typed allowlisted AJAX front door, centralized nonce operations, compile-on-write registrations, bounded/redacted observability and machine guards.

## Tranche 9 — Persistent atomic compiled registrations / ADR-0217

Accepted implementation:
- explicit site/network compiled-registration scopes;
- immutable generation history;
- separate active/fallback pointers;
- InnoDB transaction + CAS publication;
- deterministic checksums;
- corruption quarantine + last-known-good recovery;
- historical generation high-watermark `MAX(generation) + 1` independent of pointer rollback;
- permanent non-reuse of corrupt/quarantined generation IDs;
- active compiled projection consumed at runtime instead of historical Definition scans.

## Tranche 10 — Definition + Audit persistence / ADR-0218

### Definition persistence

Implemented:
- `DefinitionScope` with explicit network/site identity;
- `DefinitionTableNames` using the WordPress network prefix;
- migration `007.create-definition-persistence` / sequence 70;
- scoped PT-D Definition table + dependency edge table;
- `WpdbDefinitionTableGateway` implementing the existing `DefinitionTableGatewayInterface`;
- canonical `PersistentDefinitionRepository` retained as repository owner;
- transactional Definition insert/update plus dependency projection;
- stale revision CAS rejection;
- scoped type and reverse-dependency lookups;
- site/site and network/site isolation;
- canonical payload checksum validation retained by `DefinitionRowCodec`.

This is not a parallel Definition engine. Business modules must use the canonical Definition contracts/repository.

### Persistent migration ledger

Implemented:
- `${base_prefix}wpe_migrations` state table;
- persistent `WpdbMigrationStateStore`;
- migration IDs recorded once with applied UTC time;
- a new state-store instance reads the same applied state;
- repeated runner execution skips already-applied migrations.

The existing MigrationRunner destructive-migration recovery-plan guard remains authoritative.

### Audit PT-D persistence

Implemented:
- `AuditTableNames` and migration `008.create-audit-ptd-store` / sequence 80;
- network-prefixed append-oriented Audit event table;
- explicit network/site identity for current site-scoped events;
- unique event UUID;
- actor/channel/correlation/owner/action/outcome/resource/reason metadata;
- retention and privacy classifications;
- structured hot indexes for scope/time/action/actor/resource/outcome/correlation/retention;
- `AuditRowCodec` that persists already-sanitized metadata;
- `PersistentAuditLogger` insert-only application path;
- deterministic SHA-256 `content_hash` over the canonical semantic event envelope.

The local hash is a diagnostic content fingerprint. It does **not** make a mutable WordPress/MySQL database tamper-proof and is not cryptographic non-repudiation.

## Hosted verification — current source GREEN

Evidence source commit:
`5790ee7b69cb8ec37b17ed5815a2e4551623e248`

GitHub Actions run **33263291359 / #123** completed **SUCCESS** on GitHub-hosted Ubuntu 24.04 with PHP 8.2 and MySQL 8.4.

Job-level PASS:
- Composer metadata validation;
- canonical architecture validator;
- engineering-contract validator;
- PHP syntax;
- existing **9/9 smoke suites**;
- MySQL compiled-registration transactional integration;
- **MySQL Definition/Audit persistence integration**.

The Definition/Audit MySQL integration verifies:
- ordered non-destructive migrations 007 then 008;
- persistent migration-ledger idempotency;
- Definition dependency round-trip and reverse lookup;
- site isolation and network/site separation;
- transactional dependency replacement with revision advancement;
- stale Definition CAS rejection without committed-state mutation;
- Audit network/site persistence;
- sensitive metadata redaction before storage;
- deterministic Audit content fingerprint;
- duplicate Audit UUID rejection without rewriting history.

## Current exclusions / not yet certified

- live production WordPress DB migration/rollback;
- complete real-WordPress bootstrap/service-container wiring of Definition/Audit DB adapters;
- network-only Audit events without site context; current `ExecutionContext` requires positive `siteId`;
- Audit viewer Policy/query API/admin UI;
- Audit retention purge, privacy erasure/anonymization, export, legal/security holds;
- signed/external Audit checkpointing or stronger tamper-evidence;
- large Audit dataset/write/query/purge performance evidence;
- real WordPress AJAX/nonce/Policy end-to-end fixtures;
- Action Scheduler coexistence/packaging/Multisite backend certification;
- durable Job attempt journal, leases/claims/heartbeat/fairness/backpressure/checkpoint persistence;
- Runtime Observatory admin graph/Policy/retention UI;
- minimal Platform admin shell;
- 10K/100K compiled-registration performance certification;
- business-facing CPT/Taxonomy/Metabox/Settings builders wired end-to-end;
- any business-module production tranche.

No production deployment, live provider call, destructive live-site/customer-data mutation, live production DB migration or irreversible external operation was performed.

## Next WP121 work

1. **execute real WordPress AJAX/nonce/Policy integration fixtures**;
2. execute bounded Action Scheduler coexistence/backend evidence;
3. deepen durable Job attempts/leases/checkpoints after backend evidence;
4. build minimal Platform admin shell + Runtime Observatory graph/diagnostic surface;
5. add executable 10K/100K compiled-registration scale evidence;
6. run shared-foundation readiness gate;
7. begin first business-module tranche only after that gate passes.

Every next tranche extends executable evidence and keeps separately privileged production/live-provider boundaries intact.
