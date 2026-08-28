# ADR-0181 — Solution Blueprint & Application Composer Executable Evidence Protocol

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-29

## Context

ADR-0177 accepted F01 — Solution Blueprint & Application Composer as the canonical application-composition layer. ADR-0180 reserved `SBP-001…SBP-176` as its fixed technical evidence envelope.

A high-level evidence envelope is insufficient for a security-sensitive installer/composer that can create definitions, map roles, bind existing resources, plan migrations, install routes and coordinate multiple modules. Exact executable fixtures must be fixed before implementation.

## Decision

Accept:

`docs/QUALITY/SOLUTION-BLUEPRINT-EXECUTABLE-EVIDENCE-PROTOCOL.md`

Evidence namespace:
- **SBP-001…SBP-176**.

Current truth:
- documented: **176**;
- executed: **0/176**;
- F01 runtime certification: **0**.

## Coverage

The protocol fixes executable proof for:
- Blueprint identity/schema/versioning;
- module/foundation/adapter dependency resolution;
- typed install variables and Vault references;
- existing-site inventory, ownership and collision handling;
- role/capability/Policy mapping and anti-lockout;
- routes/pages/navigation/placements;
- schema/data migration and recovery planning;
- dry-run/simulation and plan fingerprints;
- install execution, idempotency, partial failure and reconciliation;
- upgrade, three-way drift, fork/detach and deprecation;
- disable/uninstall/Pro-expiry/data preservation;
- package provenance/checksums/portability;
- adversarial Blueprint/security/privacy cases;
- Multisite rollout/site lifecycle;
- scale/performance;
- curated and AI-generated golden-system scenarios.

## Preserved boundaries

- Blueprint install does not create a second private runtime; components remain owned by their canonical modules.
- Existing/third-party ownership is never silently taken over.
- Partial install is not Complete.
- A dry-run is not runtime certification.
- AI-generated Blueprints pass the exact same deterministic validators as human-authored Blueprints.
- Missing or uncertified components remain visible blockers/gaps.
- Secrets never become Blueprint package values.
- Authorization remains Capability + target Policy.

## Development gate

No SBP fixture has executed. No Blueprint install/import/upgrade/detach/uninstall, role mutation, schema migration, route creation, package import or runtime verification is authorized by this ADR.

ADR-0014 explicit scoped owner development consent remains required.