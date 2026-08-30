# ADR-0218 — Definition & Audit Persistence Adapters and MySQL Evidence

Status: **ACCEPTED**  
Date: **2026-08-29**  
Work package: **WP121 — Milestone 1 Platform Foundation**  
Approval authority: `GOV-OWNER-CONSENT-001` ACTIVE

## Context

WP121 already had a backend-neutral `PersistentDefinitionRepository`, Definition row codec, migration contracts/reference state stores, and an append-oriented Audit contract with metadata redaction. ADR-0217 additionally established a real database adapter boundary and verified MySQL 8.4 transactional persistence for compiled registrations.

The next platform gap was to replace reference-only Definition/Audit persistence evidence with bounded production-source database adapters and executable MySQL schema/runtime evidence without introducing a duplicate configuration or logging engine.

## Decision

Accept the Definition and Audit persistence tranche implemented on `implementation/baseline-adoption-gate`.

### Definition persistence

The accepted production-source path uses:
- explicit `DefinitionScope` with network/site identity;
- network-prefixed PT-D tables for Definitions and dependency edges;
- the existing canonical `PersistentDefinitionRepository` and `DefinitionRowCodec`;
- a `WpdbDefinitionTableGateway` implementing the existing `DefinitionTableGatewayInterface` rather than a parallel repository engine;
- transactional insert/update of the Definition row and its dependency projection;
- revision compare-and-swap on updates;
- scoped type and reverse-dependency queries;
- site-vs-site and network-vs-site isolation;
- canonical payload checksum verification on repository reads.

A Definition write conflict is explicit. A stale expected revision does not silently overwrite the committed revision or dependency graph.

### Migration persistence

A persistent network-level migration ledger is accepted:
- `${base_prefix}wpe_migrations`;
- migration ID primary key;
- applied UTC timestamp;
- second execution of the same registered migrations is idempotent;
- migration state survives creation of a new state-store instance.

This extends the existing migration runner; it does not replace its destructive-migration recovery-plan guard.

### Audit persistence

The accepted Audit PT-D source path uses:
- one network-prefixed append-oriented event table with explicit network/site fields;
- insert-only application logger contract; no ordinary update/delete API;
- unique event UUID to reject duplicate append identity;
- structured scope/action/actor/resource/outcome/correlation/retention indexes;
- sanitized metadata persisted only after the existing bounded `AuditMetadataSanitizer` has redacted secret-bearing keys;
- retention/privacy classes preserved in the event envelope;
- a deterministic SHA-256 `content_hash` over the canonical semantic event envelope for local integrity diagnostics.

The `content_hash` is **not** cryptographic non-repudiation and does not make the local database tamper-proof against a sufficiently privileged database/server actor. No stronger tamper-evidence claim is accepted by this ADR.

## Schema migrations

Accepted non-destructive migrations:
- `007.create-definition-persistence` / sequence 70;
- `008.create-audit-ptd-store` / sequence 80.

Both create InnoDB tables using the database adapter's network prefix and charset/collation profile.

No production/live WordPress database migration was executed as part of this evidence pass.

## Executable evidence

GitHub Actions run **33263291359 / #123** completed **SUCCESS** against source commit `5790ee7b69cb8ec37b17ed5815a2e4551623e248` on GitHub-hosted `ubuntu-24.04` with PHP 8.2 and MySQL 8.4.

Job-level PASS evidence includes:
- Composer metadata validation;
- canonical architecture validator;
- engineering-contract validator;
- PHP syntax;
- existing 9-suite smoke chain;
- MySQL compiled-registration integration;
- **MySQL Definition/Audit persistence integration**.

The new MySQL integration verifies:
- ordered non-destructive Definition/Audit migrations;
- persistent migration-ledger idempotency;
- Definition dependency round-trip and reverse lookup;
- Definition site isolation and network/site separation;
- transactional dependency replacement with revision advancement;
- stale Definition revision CAS rejection without committed-state mutation;
- Audit network/site scope persistence;
- sensitive metadata redaction before storage;
- deterministic Audit semantic content fingerprint;
- duplicate Audit UUID rejection without history rewrite.

## Boundaries / non-certifications

ADR-0218 does **not** certify:
- live production WordPress database migration or rollback;
- complete plugin bootstrap/service-container wiring of these adapters inside a real WordPress installation;
- network-owned Audit events without a site context; the current `ExecutionContext` requires a positive site ID;
- Audit query/UI authorization, export, privacy erasure/anonymization, retention purge execution or legal/security hold workflows;
- cryptographic tamper-proofing, signed checkpoints or external immutable Audit anchoring;
- million-row Audit performance, purge throughput or 10K-site network scale;
- real WordPress AJAX/nonce/Policy end-to-end behavior;
- Action Scheduler backend/coexistence certification;
- production deployment or separately privileged destructive/live-provider operations.

The Definition/Audit adapters are now MySQL-backed production-source implementations with executable database evidence. WordPress runtime wiring remains a separate integration gate.

## Consequences

WP121 may now move its next bounded tranche to real WordPress AJAX/nonce/Policy integration fixtures. Definition/Audit storage must continue through these canonical adapters/contracts; future modules must not create shadow Definition stores or ad-hoc Audit tables.

Repository evidence remains authoritative over conversational summaries.
