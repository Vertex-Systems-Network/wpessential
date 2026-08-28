# ADR-0117 — Forms Runtime & Submission Evidence Protocol

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28  
Work package: `P0-M00-WP02`

## Context

Forms & Workflow Builder already has accepted Phase 0 behavioral and runtime-storage architecture:
- Form Definition remains separate from runtime Entry data;
- Entry core + canonical versioned submission document + selected typed projections is the current paper preference;
- Form revision is pinned for draft/final historical semantics;
- passwords/reset/security tokens never belong in canonical Entry data;
- destination Data Sources remain canonical for CRUD targets;
- Workflow Runtime is separate from Forms Runtime;
- FRT1/PT-D is the first future physical benchmark baseline and FRT2/PT-E is mandatory before a final topology decision.

The remaining gap was a bounded executable evidence contract that proves these semantics across access, submission, storage, concurrency, files, privacy, Workflow handoff and Multisite.

## Decision

Forms runtime production-readiness claims require the applicable fixtures in:

`docs/QUALITY/FORMS-RUNTIME-SUBMISSION-EXECUTABLE-EVIDENCE-PROTOCOL.md`

The protocol fixes **FM-01…FM-92** evidence covering:
- Draft/published Form and revision pinning;
- server-side access/Policy/CSRF/IDOR;
- typed validation, hidden fields, dynamic defaults and calculation safety;
- save/resume token security and draft concurrency;
- schedule/capacity/rate/spam/CAPTCHA behavior;
- upload MIME/script/SVG/private-delivery/file-lifecycle safety;
- canonical Entry persistence, projections, idempotency and crash windows;
- CRUD/relation/user/membership action authorization;
- Workflow handoff and no-long-term-storage recovery truth;
- redirects/partial-processing UX;
- privacy/retention/admin/export behavior;
- FRT1/PT-D and FRT2/PT-E Multisite/topology/scale comparison;
- explicit MUST-NOT/stop-the-line gates.

## Negative requirements locked

A certified Forms runtime MUST NOT:
- allow protected Form submission through direct URL/API when Policy denies the actor;
- expose/mutate another user/site's Entry, target or private upload by identifier manipulation;
- persist password/reset/security-token values in Entry/log/cache/export/Job data;
- mass-assign arbitrary request fields to a destination Data Source;
- let generic Form role input grant Administrator/Super Admin-equivalent authority;
- repeat accepted submission or unsafe side effect on duplicate/retry contrary to its idempotency contract;
- make unreconcilable external side effects before the required durable acceptance boundary;
- use site/table/prefix selection as authorization;
- reinterpret historical Entry data against an unrelated newer Form revision.

## Physical topology

This ADR does **not** finalize FRT1 vs FRT2.

- `FRT1/PT-D` remains first future benchmark baseline.
- `FRT2/PT-E` remains mandatory comparison.
- Final topology selection requires executed scope/privacy/noisy-neighbor/migration/lifecycle/scale evidence.

## Current state

FM fixtures documented: **92**.  
FM executed: **0/92**.  
Forms runtime/storage certification: **none**.

No Form runtime, Entry table/row, upload, CRUD mutation, Workflow dispatch, privacy operation, benchmark or migration was executed.

## Development gate

This is planning-only acceptance. Execution remains blocked until explicit scoped owner consent under ADR-0014 / `DEVELOPMENT-CONSENT.md` / `docs/APPROVAL-LEDGER.md`.