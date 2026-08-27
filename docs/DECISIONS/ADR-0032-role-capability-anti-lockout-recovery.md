# ADR-0032 — Role/Capability Anti-Lockout & Recovery

Status: **Accepted security architecture / executable verification pending**  
Date: 2026-08-27

## Decision

Role/capability mutation is governed by impact analysis and a recovery invariant. WPE must not knowingly commit an ordinary UI change that leaves the affected site/network with zero viable administrative recovery principals.

Administrative reach is classified by effective capabilities, not role names.

Primary break-glass recovery uses existing WordPress-authenticated authority or WP-CLI/server access. A WPE recovery-mode constant may disable WPE overlays/restrict WPE writes, but it never bypasses WordPress login or mints Administrator/Super Admin authority.

## Why

A valid role diff can still permanently lock administrators out. A secret public recovery URL would introduce a parallel backdoor and larger attack surface.

## Consequences

- high-risk changes require impact preview + stale-state recheck;
- self-lockout and role deletion receive stronger confirmation;
- multi-role and individual user-capability overrides are included in effective-cap simulation;
- multisite/Super Admin remains an outer WordPress authority boundary;
- targeted authorization snapshots/reverse diffs may support repair, but no blind stale overwrite.

## Evidence still required

After explicit consent:
- last-admin-equivalent fixtures;
- multi-role/user override tests;
- role delete/reassignment;
- multisite/Super Admin;
- WP-CLI repair;
- recovery-mode behavior;
- snapshot/reverse-diff conflicts.

Supporting doc: `docs/SECURITY/ROLE-CAPABILITY-ANTI-LOCKOUT-RECOVERY.md`.