# ADR-0208 — Market Expansion Exact Executable-Evidence Protocols

Status: **Accepted**  
Date: 2026-08-29  
Work package: **WP113**  
Decision type: **Phase 0 planning/evidence completion; no runtime authorization**

## Context

ADR-0207 / WP112 found that seven accepted Market Expansion namespaces were fixed only at 16-group envelope level. Under WPEssential’s owner requirement that every meaningful option/edge case be planned before development, this remained a genuine planning gap even though namespace IDs and high-level group ownership already existed.

The affected namespaces were:

- `RDR-001…RDR-176` — URL Redirection & Routing;
- `SRT-001…SRT-176` — Search, Replace & Data Transformation;
- `DMY-001…DMY-176` — Dummy Data / Fixture Studio;
- `LNK-001…LNK-176` — Link Health / Crawl Intelligence;
- `DBM-001…DBM-176` — Database Maintenance / Cleanup;
- `PDO-001…PDO-176` — Product Discovery & Planning Orchestrator;
- `MIR-001…MIR-176` — Market Intelligence Radar.

Total required exact definitions: **7 × 176 = 1,232**.

## Decision

Accept the following exact executable-evidence protocols as canonical planning evidence for their existing namespaces without renumbering or repurposing the master-plan groups:

1. `docs/QUALITY/URL-REDIRECTION-ROUTING-EXECUTABLE-EVIDENCE-PROTOCOL.md`
2. `docs/QUALITY/SEARCH-REPLACE-DATA-TRANSFORMATION-EXECUTABLE-EVIDENCE-PROTOCOL.md`
3. `docs/QUALITY/DUMMY-DATA-FIXTURE-STUDIO-EXECUTABLE-EVIDENCE-PROTOCOL.md`
4. `docs/QUALITY/LINK-HEALTH-CRAWL-INTELLIGENCE-EXECUTABLE-EVIDENCE-PROTOCOL.md`
5. `docs/QUALITY/DATABASE-MAINTENANCE-CLEANUP-EXECUTABLE-EVIDENCE-PROTOCOL.md`
6. `docs/QUALITY/PRODUCT-DISCOVERY-PLANNING-ORCHESTRATOR-EXECUTABLE-EVIDENCE-PROTOCOL.md`
7. `docs/QUALITY/MARKET-INTELLIGENCE-RADAR-EXECUTABLE-EVIDENCE-PROTOCOL.md`

Each protocol contains **176 exact numbered fixtures** across the original 16 canonical groups.

WP113 result:

- exact fixture definitions completed: **1,232/1,232**;
- executed: **0/1,232**;
- runtime/provider certification: **0**;
- production implementation authorization: **NOT GRANTED / 0/56**.

## Preserved boundaries

### Redirect / Routing

- redirect match/simulation ≠ authorization;
- exported Apache/Nginx config ≠ active server configuration;
- client-controlled conditions cannot grant protected access;
- unsafe schemes, CR/LF, open redirects, unbounded regex and SSRF remain stop-the-line.

### Search / Replace

- Dry Run ≠ mutation;
- successful mutation ≠ rollback/verification;
- no arbitrary raw SQL/code;
- no PHP object instantiation from serialized data;
- protected/secret/owner-managed storage remains owner-governed;
- concurrent newer data cannot be overwritten silently.

### Dummy / Synthetic Data

- generated data ≠ real/source truth;
- cleanup requires durable generated-object ownership, never value heuristics;
- no real payment/email/SMS/shipment/provider side effects;
- no real secrets/known insecure admin credentials;
- owner APIs/validators remain authoritative.

### Link Health

- timeout/403/429/WAF/policy-blocked response ≠ proven broken;
- Safe HTTP/SSRF/DNS/TLS/redirect controls apply per hop;
- protected-source existence cannot leak;
- scan result ≠ mutation permission;
- orphan status ≠ automatic SEO defect.

### Database Maintenance

- candidate/orphan suspicion ≠ deletion authority;
- unknown/probable third-party ownership remains non-destructive;
- generic cleanup cannot bypass Audit/privacy/legal hold/provider ownership;
- estimated reclaim ≠ measured reclaimed bytes;
- no arbitrary DELETE/TRUNCATE/SQL surface.

### Product Discovery / Planning

- planning request/output ≠ development consent;
- repository canonical state overrides conversation memory;
- popularity ≠ architecture authority;
- source facts/inference/WPE decisions remain provenance-separated;
- S07 cannot auto-merge or modify runtime/source under planning-only mode.

### Market Intelligence

- trend/score ≠ product acceptance;
- reviews/support are anecdotal signals, not authoritative facts;
- “daily” schedule is cadence semantics, not exact execution guarantee;
- Radar cannot auto-add modules, auto-merge, modify runtime code or grant consent;
- failed/missing sources remain unknown rather than fabricated no-change.

## Planning-gap effect

ADR-0207 recorded **5,808** exact fixture definitions remaining across 33 namespaces.

WP113 closes **1,232** of those definitions.

Remaining planning gap after this ADR:

- **WP114** — MPR/RPR/ATM/MDP/STM = **880**;
- **WP115** — ORD/SEC/FNT/UDS/STG/BKX/MRL/PBX/JEX/LHX/HFC = **1,936**;
- **WP116** — UAF/MIG/WLB/DUP/ALX/MBX/THM/RSX/RDX/CPTX = **1,760**.

Total remaining: **4,576 exact fixture definitions across 26 namespaces**.

P0 remains `SPECIFICATION` and is **not yet approval-ready**. After WP116, a fresh closure/readiness audit remains mandatory; completion of WP113–WP116 does not automatically grant development approval.

## Consequences

- The seven Market Expansion namespaces now move from `PLANNING GAP` to `NO GAP / READY AS PLAN` at the evidence-design layer.
- Their runtime state remains `RUNTIME EVIDENCE PENDING` because **0 fixtures executed**.
- Any external provider-specific requirement remains `PROVIDER CERTIFICATION PENDING` until separately executed/certified.
- ADR-0014 remains the hard owner-consent boundary.
- WP113 is DONE; WP114 becomes the next safe planning work package.

## Execution statement

No WordPress runtime, HTTP crawl, DB mutation, cleanup, data generation, scheduled workflow, GitHub automation runtime, external provider/API/AI/MCP call, test, benchmark, build, migration, package or deployment was executed while accepting this ADR.