# ADR-0184 — Search, Replace & Data Transformation Engine

Status: **Accepted planning architecture / evidence pending / no development authorization**
Date: 2026-08-29

## Context

The owner requested a deep audit of Better Search Replace and a reusable WPE module. Market/source evidence shows serialization-safe replacement, table selection, Dry Run and Multisite are baseline expectations, while recent security fixes reinforce safe parameterization, validated table scope and non-instantiating serialization handling.

## Decision

Accept new Pro module surface:

**Search, Replace & Data Transformation Engine**.

Canonical spec:
`docs/MODULES/SEARCH-REPLACE-DATA-TRANSFORMATION-EXHAUSTIVE-SPEC.md`

## Core architecture

Separate Search Definition, Transform Definition, Scope, Dry Run fingerprint, reviewed Plan, Run, Checkpoint, Journal, verification and rollback truth.

Reuse DSR, Import/Export planning concepts, Backup, JobService, Field/Relation/Custom Table owners, Audit, Policy, Privacy, Versioning and Multisite.

## Accepted capabilities

- literal/case/URL/bounded-regex search;
- selected table/entity/field scope;
- format-aware PHP serialized/JSON/block/HTML/shortcode transforms;
- no PHP object instantiation during serialized processing;
- Dry Run default;
- exact change preview/redaction;
- URL migration wizard;
- resumable Job batches;
- stale-plan/concurrent-write detection;
- rollback classes and Backup requirements;
- REST/Abilities/MCP/CLI/AI with approval;
- Multisite/global-table semantics.

## Evidence

Future namespace: **SRT-001…SRT-176**, executed **0/176**.

## Safety

No arbitrary raw SQL replace UI, no generic secret/password/token mutation, no silent key/constraint mutation, no unsupported rollback claim, and no AI/MCP destructive run without reviewed Plan and authorization.

## Development gate

No database search, write, migration, backup, journal or runtime test is authorized by this ADR.
