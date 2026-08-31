# ADR-0164 — Admin Menu Canonical Evidence Refinement

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28  
Work package: `P0-M00-WP47`  
Development authorization: **NOT GRANTED**

## Decision

Accept the in-place refinement of the canonical Admin Menu executable evidence protocol:

`docs/QUALITY/ADMIN-MENU-EXECUTABLE-EVIDENCE-PROTOCOL.md`

The canonical fixed matrix is now **AM-01…AM-176**. The original AM-01…AM-40 semantics remain preserved and traceable; the refinement adds bounded evidence for Definition/versioning, runtime discovery and hook ordering, stable external menu identity, transformation conflicts, direct-screen authorization, recovery/safe mode, role/membership audience changes, cache/asset behavior, third-party lifecycle drift, Multisite Site/Network Admin isolation, accessibility and large-menu/plugin-conflict scale.

## Preserved invariants

1. **Menu presentation never grants or revokes underlying authorization.** Direct screen access remains owned by WordPress/core/plugin capabilities and WPE target Policy where applicable.
2. Hiding, renaming, reordering, moving or grouping a menu item is presentation state, not a security boundary.
3. Ambiguous/missing third-party targets fail/degrade explicitly; WPE must not silently retarget an unrelated screen after plugin/version drift.
4. Site Admin, Network Admin and any supported User Admin contexts remain distinct; same-looking slugs do not authorize cross-context mutation.
5. Safe/recovery mode restores native WordPress/plugin navigation without bypassing WordPress authentication or capabilities.
6. Recovery-critical navigation cannot be silently made unreachable for every viable recovery principal.
7. Role/Capability or Membership audience changes cause presentation recomputation but never become independent authorization truth.
8. Admin Menu cache/asset optimizations remain subordinate to current authority, lifecycle and scope; stale visibility must not expose protected metadata.
9. Pro expiry/module disable/deactivation follow accepted lifecycle semantics and cannot strand wp-admin recovery.
10. No static evidence in this ADR promotes any runtime/plugin compatibility claim.

## Evidence status

- AM fixtures documented: **176**
- AM fixtures executed: **0/176**
- Admin Menu runtime certifications: **0**
- Third-party compatibility certifications: **0**
- Multisite runtime certifications from this protocol: **0**

No admin-menu hook/filter, WordPress runtime, asset load, cache mutation, role/membership mutation, Multisite action, browser test or benchmark was executed while accepting this ADR.

## Consequences

`P0-M00-WP47` is planning-complete once canonical registries/checkpoint/PR are synchronized to ADR-0164. Future implementation or executable evidence still requires explicit scoped owner consent under ADR-0014 and the approval ledger.
