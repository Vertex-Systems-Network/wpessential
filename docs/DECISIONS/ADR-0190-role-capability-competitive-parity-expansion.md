# ADR-0190 — Role & Capability Competitive Parity Expansion

Status: **Accepted planning architecture / evidence pending / no development authorization**  
Date: 2026-08-29

## Context

The owner requested a deep audit of User Role Editor and required WPE Role & Capability Manager to be fully featured and competitive without creating a second authorization system.

WPE already preserves WordPress Role/Capability authority and has stronger concepts for explicit deny/absent, meta-capability explanation, anti-lockout, snapshots, Multisite and Policy integration. The missing market-parity gaps are therefore extensions to Surface 30, not a new role module.

## Decision

Accept `docs/MODULES/ROLE-CAPABILITY-COMPETITIVE-PARITY-EXPANSION.md` as an authoritative addendum to Surface 30.

Add product behavior for:
- Assignable Role Policy / target-role hierarchy;
- server-side target-role enforcement across Users/Add User/Edit User/bulk/REST/Abilities/Workflow/import/Multisite;
- Administrator Recovery / Rescue with one-time expiring email artifact, enumeration-safe response, rate limiting and audit;
- capability provenance and orphan-capability diagnostics;
- linked admin/menu/widget/meta-box/form/plugin-operation restrictions delegated to owning Policy/surface adapters;
- object-level content access through Policy rather than synthetic capability explosion;
- large-user/multi-role/no-role administration;
- network role template/sync dry run;
- effective-access viewer/explainability;
- import/export/migration compatibility and drift handling;
- support impersonation remaining a separate protected account action.

## Authority boundary

WordPress remains the actual authorization authority. WPE may explain, plan and safely mutate native role/capability state only through accepted WordPress APIs and target-resource Policy.

Visibility restrictions never become authorization.

## Evidence

Reserve **RPR-001…RPR-176**, executed **0/176**.

Existing Role runtime evidence `RA-01…RA-176` remains separately authoritative and unexecuted.

## Development gate

No role mutation, rescue email/token, user bulk action, network synchronization, capability cleanup, impersonation or runtime test is authorized by this ADR.