# ADR-0185 — Dummy Data, Synthetic Dataset & Fixture Studio

Status: **Accepted planning architecture / evidence pending / no development authorization**
Date: 2026-08-29

## Context

The owner requested a generator capable of producing dummy data for effectively every WPE/WordPress object type. Market evidence from FakerPress confirms strong developer demand for posts/CPTs, meta, users, terms, comments, media, data-provider types, batching, REST and cleanup.

## Decision

Accept new Pro/developer module surface:

**Dummy Data, Synthetic Dataset & Fixture Studio**.

Canonical spec:
`docs/MODULES/DUMMY-DATA-FIXTURE-GENERATOR-EXHAUSTIVE-SPEC.md`

## Architecture

The module composes registered Data Sources and owning APIs rather than writing directly into every table. It supports deterministic seeds, scenarios, relations/status distributions, media, synthetic PII, negative/adversarial datasets, scale profiles and generated-data ownership/cleanup.

## Accepted semantics

- native WordPress + WPE entities;
- adapters for Woo/external domains;
- deterministic seed/version manifest;
- locale/Unicode/RTL profiles;
- realistic but fictional PII;
- relation graphs/cardinality;
- lifecycle/status/time distributions;
- scenario/Blueprint datasets;
- XS→1M+ volume profiles;
- resumable JobService Runs;
- explicit generated-data ownership;
- safe cleanup/regeneration;
- REST/Abilities/MCP/CLI/AI.

## Evidence

Future namespace: **DMY-001…DMY-176**, executed **0/176**.

## Safety

No real payment/email/SMS/provider side effects by default, no real secrets, no scraped personal data, no cleanup by value-pattern guessing, no known/default insecure admin credentials, and no direct bypass of owning module validators.

## Development gate

No fixture generation, media download, user creation, domain-adapter mutation or cleanup is authorized.
