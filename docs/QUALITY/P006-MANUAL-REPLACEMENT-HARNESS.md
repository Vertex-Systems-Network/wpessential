# P-006 WordPress-Owned Manual Replacement Harness

Issue: #1043

Authorization: `GOV-P006-B3-MANUAL-REPLACEMENT-HARNESS-001`

Status: implementation prerequisite for future FP-58 evidence.

## Purpose

This harness validates that WPEssential Free and Pro deterministic complete ZIP artifacts can be overwritten through the WordPress-owned administrator upload/overwrite transport represented by:

`Plugin_Upgrader::install($localZip, ['overwrite_package' => true])`

It does not execute FP-58 and does not promote any P-006 fixture result.

## Runtime cells

- WordPress 6.9 / PHP 8.2 / MySQL 8.4
- WordPress 7.1 / PHP 8.5 / MySQL 8.4

Free and Pro overwrite paths are validated independently from a fresh disposable F0/P0 baseline in each cell.

## Transport contract

The harness:

1. installs F0/P0 as real plugin directories, not symlinks;
2. sets `FS_METHOD=direct` inside the disposable site;
3. activates F0/P0;
4. invokes WordPress core `Plugin_Upgrader::install()` against a local deterministic ZIP with `overwrite_package=true`;
5. starts a fresh PHP process after the overwrite;
6. verifies exact before/after payload-tree identities;
7. verifies active plugin state and compatible local Free/Pro state;
8. records WordPress filesystem method and temporary-backup residue;
9. blocks/logs WordPress outbound HTTP.

The candidate graph reuses the accepted Wave 1K deterministic F0/F1/P0/P1 construction logic only as artifact-generation logic. The historical Wave 1K temporary grant is not reused as authorization for this harness or for FP-58.

## Explicit boundaries

This harness does not:

- inject interruption or corruption;
- certify WordPress updater/TUF trust;
- certify rollback or migration behavior;
- certify a Free/Pro pair or runtime;
- execute or certify FP-58;
- mutate production/live sites;
- change product runtime source;
- accept ADR-0010.

P-006 accounting remains 144 documented / 45 executed / 44 PASS / 0 FAIL / 1 INCONCLUSIVE until a separately authorized fixture execution changes it.
