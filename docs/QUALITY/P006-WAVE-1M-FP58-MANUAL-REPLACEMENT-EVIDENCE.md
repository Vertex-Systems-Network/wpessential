# P-006 Wave 1M — FP-58 WordPress Manual Replacement Evidence

Issue: #1046

Authorization: `GOV-P001-CF-TEMP-011`

Fixture: **FP-58 — manual upload/replacement order gets the same compatibility guarantees as automated update**

Status before execution: **AUTHORIZED / NOT YET TERMINAL**

## Scope

This tranche executes only FP-58 through the WordPress-owned local ZIP overwrite path already validated as a prerequisite by Issue #1043 / PR #1045:

`Plugin_Upgrader::install($localZip, ['overwrite_package' => true])`

The live plugin roots are real directories and CI uses `FS_METHOD=direct`.

The Wave 1K/1L symlink-target transport is not used as FP-58 evidence.

## Runtime cells

- WordPress 6.9 / PHP 8.2 / MySQL 8.4
- WordPress 7.1 / PHP 8.5 / MySQL 8.4

## Authorized subpaths

Each runtime cell runs four isolated disposable scenarios:

1. compatible Free-first: F0/P0 → F1/P0, expected `compatible`;
2. compatible Pro-first: F0/P0 → F0/P1, expected `compatible`;
3. breaking Free-first: F1/P0 → F2/P0, expected `free_version_too_new`;
4. breaking Pro-first: F1/P0 → F1/P2, expected `free_version_too_old`.

Every scenario starts from a fresh disposable WordPress + MySQL environment and uses a fresh PHP process for baseline and post-overwrite observations.

## Deterministic graph

The exact-source NON-RELEASE / TEST-ONLY graph contains:

- F0 — Free 0.1.0-dev / Platform API 0.1.0 / schema 1;
- F1 — Free 0.1.1-test-overlap / Platform API 0.1.0 / schema 1;
- F2 — Free 0.2.0-test-breaking / Platform API 0.2.0 / schema 1;
- P0 — Pro 0.1.0-dev supporting Free 0.1.0-dev…0.1.1-test-overlap / Platform API 0.1.0 / schemas 1;
- P1 — Pro 0.1.1-test-overlap with the same overlap contract;
- P2 — Pro 0.2.0-test-breaking supporting exact Free 0.2.0-test-breaking / Platform API 0.2.0 / schemas 1.

Only the main plugin entry may differ from the canonical source package for each test variant.

## Required compatible guarantees

For compatible manual replacement paths:

- compatibility state remains `compatible`;
- premium boot remains allowed;
- premium migration admission remains allowed;
- expected premium module set remains registered;
- Free `custom-post-types` and `taxonomies` remain registered;
- Free and Pro remain active;
- exact artifact and payload-tree identity matches the pinned graph;
- no compatibility persistence keys appear;
- outbound WordPress HTTP attempts remain zero.

## Required breaking guarantees

For breaking manual replacement paths:

- exact mismatch state is reported;
- premium boot is denied;
- premium migration admission is denied;
- premium modules remain inert/empty;
- Free kernel remains booted;
- Free `custom-post-types` and `taxonomies` remain registered;
- Free and Pro remain active as plugin registrations while Pro fails closed internally;
- exact artifact and payload-tree identity matches the pinned graph;
- no compatibility persistence keys appear;
- outbound WordPress HTTP attempts remain zero;
- no fatal is observed.

## Transport evidence

Each scenario records:

- exact target ZIP SHA-256;
- exact before/after live payload-tree digest;
- counterpart payload-tree preservation;
- exact Free/Pro pair identity;
- WordPress/PHP/MySQL versions;
- WordPress filesystem method;
- `Plugin_Upgrader::install()` result;
- active plugin state;
- non-symlink live plugin roots;
- temporary plugin-backup residue;
- network attempts;
- compatibility persistence scan.

## Timeout-safe CI

- combined graph build: <=15 minutes;
- each scenario/runtime cell: <=18 minutes;
- `fail-fast:false`;
- stale-run cancellation;
- immutable per-scenario artifacts;
- terminal FP-58 marker only after the full matrix succeeds.

## Non-promotion boundary

This tranche does not authorize or certify:

- FP-49…52;
- partial/truncated/missing-file interruption handling;
- automatic updater/TUF;
- rollback/downgrade;
- migration recovery;
- stale/concurrent requests;
- provider/license/billing/allocation behavior;
- multisite;
- production/live systems;
- destructive actions;
- deploy/release;
- a certified Free/Pro pair;
- runtime certification;
- ADR-0010 acceptance.

Before terminal execution the formal P-006 accounting remains:

**144 documented / 45 executed / 44 PASS / 0 FAIL / 1 INCONCLUSIVE / 0 certified pairs / 0 runtime certifications**.

If and only if the entire authorized FP-58 matrix reaches bounded PASS, closeout accounting becomes:

**144 documented / 46 executed / 45 PASS / 0 FAIL / 1 INCONCLUSIVE / 0 certified pairs / 0 runtime certifications**.
