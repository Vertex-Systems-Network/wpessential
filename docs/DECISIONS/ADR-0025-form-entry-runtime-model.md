# ADR-0025 — Form Entry Runtime Architecture

Status: **Accepted architecture / physical schema pending evidence**  
Date: 2026-08-27

## Decision

Forms configuration lives in Definition Repository; submissions live in a dedicated Forms Runtime.

A Form Entry pins the Form published revision and uses:
- normalized Entry core identity/state;
- canonical versioned typed value document;
- selected explicit typed query/search projections only for fields that need indexing/reporting;
- first-class protected/public file references;
- separate save/resume draft/token lifecycle;
- links to Workflow Runtime rather than embedding workflow execution state.

Passwords/password-reset tokens and equivalent credentials are never stored in Entry values.

## Why

Entries are usually consumed as complete submissions, so unlimited EAV rows for every value are not automatically desirable. At the same time, one opaque serialized blob cannot efficiently support selected admin filters, privacy handling or reporting. A hybrid model keeps canonical submission truth compact while allowing explicit projections.

## Guardrails

- no one-WordPress-post-per-entry universal assumption;
- submitted Entry preserves revision semantics even after Form changes;
- duplicate submit uses idempotency;
- workflow enqueue failure after Entry commit is recoverable and does not erase accepted submission;
- no-storage Forms still require bounded idempotent processing envelope where necessary;
- private uploads never rely on permanent public URLs;
- query projections are derived/rebuildable, not separate source of truth.

## Evidence pending

Exact tables/JSON representation/index projections, large-entry scale, privacy batching, save/resume concurrency and file lifecycle require consent-gated benchmark.

Supporting: `docs/ARCHITECTURE/FORM-ENTRY-RUNTIME-STORAGE-CANDIDATE.md`.

ADR-0014 remains controlling; no runtime code/table is authorized.