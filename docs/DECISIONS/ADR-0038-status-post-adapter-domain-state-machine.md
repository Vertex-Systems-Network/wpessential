# ADR-0038 — Status Manager: Post Status Adapter vs Domain State Machine

Status: **Accepted architecture / adapters and schema evidence pending**  
Date: 2026-08-27

## Decision

Status Manager has two distinct engines:

1. **WordPress Post Status Adapter** over core `post_status` registration/storage/UI/query semantics.
2. **Generic Domain State Machine** for WPE/custom Data Source entities with explicit states, transitions, guards and structured history.

Specialized module lifecycles such as Membership Enrollment remain owned by their module and are not replaced by generic Status Manager.

## Why

WordPress post statuses are globally registered and have core-specific limitations/UI behavior. Generic business states need typed storage, transition guards, concurrency and history that should not be forced into `wp_posts.post_status`.

## Consequences

- post-status registration and per-CPT availability/UI integration are separate;
- WPE-owned status key changes/deletion require data migration;
- core/third-party statuses are not destructively removed by ordinary WPE UI;
- managed generic state changes occur through transition actions rather than free field writes;
- current-state storage is Data Source-specific; transition history is structured runtime data;
- transition concurrency/idempotency must be proven.

## Evidence still required

After explicit consent: WordPress editor/list/quick-edit fixtures, status migration, generic state storage/indexes, concurrent transition/history consistency and direct-write bypass tests.

Supporting doc: `docs/ARCHITECTURE/STATUS-MANAGER-STATE-MACHINE-RUNTIME.md`.