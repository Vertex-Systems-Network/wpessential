# ADR-0123 — P-001 Compatibility Floor Executable Evidence Protocol

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28  
Work package: `P0-M00-WP07`

## Context

ADR-0002 keeps the WPEssential compatibility floor Proposed pending executable proof. The existing `P-001 — Compatibility Floor Spike` in `docs/QUALITY/CONSENT-GATED-TECHNICAL-SPIKE-PROTOCOLS.md` defines the right broad goal but is not sufficiently fixture-driven for autonomous, repeatable, adversarial execution across modern governance requirements.

Current static planning evidence on 2026-08-28 confirms:
- WordPress 7.1 is the current release/reference target;
- WordPress 6.9 remains the current WPE minimum candidate because native Abilities are a first-class architectural dependency;
- WordPress Requirements recommends PHP 8.3+, MySQL 8.0+ or MariaDB 10.11+;
- WPE is intentionally allowed to choose a narrower, more modern floor than WordPress's absolute backward-compatible runtime minimum;
- the actual launch floor still requires runtime, dependency, Multisite, existing-project and artifact evidence.

## Decision

P-001 compatibility-floor acceptance requires the applicable fixtures in:

`docs/QUALITY/P001-COMPATIBILITY-FLOOR-EXECUTABLE-EVIDENCE-PROTOCOL.md`

The protocol fixes **CF-01…CF-112** evidence covering:
- official-source/version/lifecycle refresh and immutable matrix provenance;
- plugin header/Composer/runtime/CI/docs/diagnostics floor consistency;
- unsupported-environment preflight before unsafe mutation;
- clean install/activation/deactivation/uninstall/interruption paths;
- minimum/current/forward WordPress + PHP matrix;
- MySQL/MariaDB/charset/collation/sql-mode/migration behavior;
- Multisite network activation/site lifecycle/scope isolation;
- native Abilities, REST, object cache, WP-Cron, loopback and WP-CLI profiles;
- existing-project baseline/coexistence/adoption safety;
- Free↔Pro compatible/mismatch/rollback behavior;
- release ZIP/dependency/build metadata/CI/resource evidence;
- explicit MUST-NOT/stop-the-line gates.

## Candidate floor truth

This ADR does **not** accept a compatibility floor.

Current planning candidates remain:
- WordPress minimum candidate: **6.9**;
- current/reference WordPress: **7.1** at this planning snapshot;
- PHP minimum candidate: **8.3**;
- PHP forward-compatibility targets: **8.4 / 8.5** at this planning snapshot;
- database floor: **not yet finalized**; current WordPress recommendation evidence starts at MySQL 8.0+ / MariaDB 10.11+, with modern forward profiles compared at execution.

All versions must be refreshed from authoritative sources when P-001 is actually run and before public beta/stable support claims.

## Negative requirements locked

A future compatibility claim MUST NOT:
- partially mutate WPE data/schema before rejecting an unsupported environment;
- disagree across plugin headers, Composer, runtime guard, CI, diagnostics and documentation;
- hide required-cell deprecations/warnings simply because the request did not fatal;
- inherit a hidden stricter dependency floor without updating public support metadata;
- treat MySQL and MariaDB as identical evidence profiles;
- claim single-site success proves Multisite compatibility;
- require object cache/loopback/WP-Cron/WP-CLI when those are documented as optional/degradable;
- break unrelated existing-project data/schedules/routes during adoption;
- treat a pre-existing `BASELINE FAILURE` as a WPE regression/pass;
- certify source checkout while the distributable release artifact remains untested;
- market environments outside the exact passed matrix as certified.

## Current state

CF fixtures documented: **112**.  
CF executed: **0/112**.  
P-001 certified: **no**.  
ADR-0002 remains **Proposed / Phase 0 blocker**.

No WordPress/PHP/database environment, install, activation, dependency solve, migration, WP-CLI command, CI job, package build or runtime test was executed.

## Development gate

This is planning-only acceptance. Execution remains blocked until explicit scoped owner consent under ADR-0014 / `DEVELOPMENT-CONSENT.md` / `docs/APPROVAL-LEDGER.md`.