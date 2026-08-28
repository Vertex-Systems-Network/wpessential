# ADR-0157 — Role & Capability Executable Evidence Refinement

Status: **Accepted evidence refinement / execution pending**  
Date: 2026-08-28  
Work package: `P0-M00-WP40`

## Context

ADR-0032, ADR-0097 and ADR-0114 establish WordPress native authorization authority, WPE Change Plans, anti-lockout, recovery-principal invariants, Multisite/Super Admin boundaries and the original RA-01…RA-48 executable protocol.

Subsequent KPA, CAC, VER, MLC, PDL, ERR, Membership, REST and Import/Export contracts require deeper explicit evidence for native/meta capability semantics, concurrent plans, revocation propagation, third-party drift and lifecycle safety without creating a parallel authorization engine.

## Decision

Refine `docs/QUALITY/ROLE-CAPABILITY-EXECUTABLE-EVIDENCE-PROTOCOL.md` in place to **RA-01…RA-176**.

Original RA-01…RA-48 semantics remain preserved. Added evidence covers:
- native role/capability and mapped/meta-cap behavior;
- user roles, overrides and identity/provenance boundaries;
- Change Plan fingerprinting/concurrency/bulk targets;
- recovery-principal depth and self-lockout safety;
- partial native mutation, verification, reconciliation and reverse diff;
- Multisite and Super Admin authority;
- capability/Policy/cache revocation across consumers;
- third-party coexistence, versioning, disable/expiry/uninstall;
- CSRF/IDOR/privacy/Audit and large-user/network performance.

Independent certification classes remain `RA-N/U/P/L/X/M/C/E/S/O`.

## Preserved boundaries

- WordPress remains effective authorization authority;
- WPE control-plane plans/snapshots are guards/metadata, not parallel authority;
- Site Admin cannot grant/remove Super Admin through ordinary site-role path;
- anti-lockout uses effective authority and viable recovery principals, not role-name heuristics;
- metadata/audit failure after native mutation never causes blind destructive retry;
- recovery mode never bypasses WordPress login/capability checks or mints authority;
- KPA/CAC/VER/MLC and other shared certifications do not promote RA runtime certification.

## Current execution truth

- RA fixtures documented: **176**.
- RA fixtures executed: **0/176**.
- RA runtime certifications: **0**.
- zero-recovery-principal ordinary UI commits permitted by contract: **0**.
- unauthorized Super Admin/network grants permitted by contract: **0**.

## Development gate

No role/capability/user-role/Super Admin/cache/recovery/bulk/Job/Multisite mutation or runtime test was executed by this refinement.

Execution and implementation remain prohibited until explicit scoped owner consent under ADR-0014 and the Approval Ledger.