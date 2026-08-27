# ADR-0036 — Settings Page Scope & Storage Runtime

Status: **Accepted architecture / physical storage evidence pending**  
Date: 2026-08-27

## Decision

Settings Page Definition is separate from runtime Settings values.

WPE-owned ordinary values use bounded **Settings value documents per Settings Page + scope** with explicit modes:
- site;
- network;
- network default + site override.

Resolution for inherited mode is `site override → network default → definition default`.

Secrets are Vault references, not plaintext option values. Generic user preferences belong to User Profile rather than normal Settings Page storage.

## Why

- keeps layout/schema revisions separate from mutable configuration;
- enables deterministic multisite inheritance;
- avoids one giant global option or arbitrary option-key editing;
- supports atomic-ish page-level validation/save semantics;
- keeps secrets and large datasets out of ordinary options.

## Consequences

- external/native WordPress settings require explicit adapter descriptors;
- server-side WPE type/Policy validation is authoritative even when `register_setting()` is used;
- REST exposure off by default;
- ordinary admin-only settings should not be autoloaded by default;
- runtime value history/audit is separate from Definition revisions.

## Evidence still required

After explicit consent: grouped-option physical strategy, autoload behavior, multisite overrides, concurrency/stale edits, REST/Settings API integration, secret handling and migration tests.

Supporting doc: `docs/ARCHITECTURE/SETTINGS-PAGE-STORAGE-SCOPE-RUNTIME.md`.