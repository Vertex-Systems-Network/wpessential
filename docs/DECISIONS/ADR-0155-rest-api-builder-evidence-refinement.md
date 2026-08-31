# ADR-0155 — REST API Builder Executable Evidence Refinement

Status: **Accepted evidence refinement / execution pending**  
Date: 2026-08-28  
Work package: `P0-M00-WP38`

## Context

ADR-0028/0094/0115 and the existing REST protocol established the core WordPress REST, authentication, Policy, schema, idempotency, rate, cache, CORS, Multisite and scale contract. The original fixed matrix had REST-01…REST-52.

Subsequent shared contracts introduced explicit DSR, KPA, CLG, DVR, RLT, CAC, VER, MLC, PDL and ERR boundaries that must be integrated into REST certification without treating those shared certifications as endpoint certification.

## Decision

Refine the existing canonical `docs/QUALITY/REST-API-BUILDER-EXECUTABLE-EVIDENCE-PROTOCOL.md` in place to **REST-01…REST-176**.

The original REST-01…REST-52 semantics remain preserved. REST-53…REST-176 add explicit evidence for:
- Definition compile/revision/version/lifecycle behavior;
- Data Source and Ability capability/mutation/precondition semantics;
- Query/filter/order/cursor/projection/cost boundaries;
- Dynamic Value and Conditional Logic integration;
- shared RLT/CAC integration without authorization shortcuts;
- HTTP/proxy/content-type/CORS/header behavior;
- async Job/Workflow/provider unknown-outcome reconciliation;
- privacy/audit/versioning/Multisite/scale.

## Preserved boundaries

- authentication, endpoint Policy and target resource Policy remain distinct;
- CORS/preflight, route visibility, rate-limit allow, cache hit and idempotency never authorize an operation;
- readable Data Source never implies writable capability;
- generic endpoint cannot expose arbitrary PHP/SQL/filesystem/Vault-secret execution;
- async accepted/queued is not completed;
- provider timeout with possible side effect remains unknown/reconciliation truth;
- shared DSR/KPA/CLG/DVR/RLT/CAC certification never promotes REST certification.

## Current execution truth

- REST fixtures documented: **176**.
- REST fixtures executed: **0/176**.
- REST runtime certifications: **0**.
- RLT/CAC/DSR/KPA/provider counters remain separate and unchanged.

## Development gate

No REST route, request, authentication flow, mutation, provider call, cache/rate/idempotency state, Job/Workflow handoff, fuzz/load test or Multisite runtime operation was executed by this refinement.

Execution and implementation remain prohibited until explicit scoped owner consent under ADR-0014 and the Approval Ledger.