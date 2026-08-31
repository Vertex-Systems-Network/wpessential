# ADR-0202 — F08 Experimentation & Rollout Executable Evidence Protocol

Status: **Accepted**  
Date: **2026-08-29**

## Context

ADR-0177 accepted F08 Experimentation & Rollout as a universal WPEssential foundation. ADR-0180 reserved a fixed `EXP-001…EXP-176` technical evidence envelope organized as 16 groups × 11 fixtures. WP70 required that reserved envelope to be expanded into exact executable-evidence text before any implementation can later be considered runtime-ready.

F08 crosses several high-risk semantic boundaries: experiment assignment, actual treatment exposure, analytics events/metrics, statistical interpretation, progressive rollout, operational kill switches, personalization/cache behavior, privacy/consent and Multisite/tenant isolation. Without a detailed protocol, a future implementation could incorrectly call assignment an exposure, treat correlation as causal proof, let feature flags bypass Policy, leak variants through caches, contaminate anonymous/authenticated identities or claim rollout safety without stale-cache/edge evidence.

## Decision

Accept `docs/QUALITY/EXPERIMENTATION-ROLLOUT-EXECUTABLE-EVIDENCE-PROTOCOL.md` as the canonical detailed executable-evidence contract for F08.

The protocol fully enumerates **EXP-001…EXP-176** across the fixed evidence groups:

1. experiment/variant/hypothesis/metric schema;
2. eligibility/audience exclusions;
3. deterministic assignment/hash/stickiness;
4. allocation percentages/rebalance/new variant;
5. exposure event dedupe/first exposure/contamination;
6. primary/guardrail metric semantics;
7. statistical profile/sample/interval/error caveats;
8. schedule/stop/pause/rollout/kill switch;
9. cache/personalization/anonymous-login stitch;
10. experiment versioning/concurrent config changes;
11. privacy/consent/sensitive segmentation;
12. feature rollout safety and non-experiment flags;
13. Multisite/tenant assignment isolation;
14. analytics data quality/late events/refunds;
15. high-traffic assignment/exposure performance;
16. golden A/B/multivariate/rollout regression.

## Required truth boundaries

The accepted protocol makes the following architectural boundaries non-negotiable:

- experiment assignment ≠ authorization;
- assignment ≠ consent;
- assignment ≠ exposure;
- exposure ≠ conversion;
- observed association/statistical signal ≠ automatic causal proof;
- rollout cohort/feature flag ≠ role, capability, membership or entitlement;
- F07 Placement may consume assignments/exposure hooks but does not own F08 allocation/statistical truth;
- F02 Analytics remains authoritative for event/metric/data-quality contracts consumed by F08;
- primary/guardrail metric bindings and statistical profile are revision-pinned and cannot silently change after data is observed;
- deterministic assignment is namespaced by experiment revision, subject identity and required site/tenant scope;
- anonymous→authenticated stitching is explicit and cannot fabricate/double treatment history;
- kill switch is an operational safety mechanism, not statistical proof of winner/loser;
- non-experiment feature rollout must not fabricate A/B statistics;
- personalized/variant cache state cannot leak across user/session/consent/site/tenant boundaries;
- sensitive targeting remains Policy/consent/data-minimization governed;
- AI/MCP can draft/analyze/recommend only and cannot obtain a hidden publish/allocation/rollout path.

## Evidence state

At ADR acceptance:

- EXP documented: **176/176**;
- EXP executed: **0/176**;
- F08 runtime certification: **0**;
- product scope: **56/56 planned**;
- implementation authorization: **0/56**.

No experiment assignment engine, exposure collection, statistical evaluation, analytics recomputation, feature flag/rollout runtime, kill-switch propagation, placement/browser execution, provider request, AI/MCP call, build, test or benchmark was executed by creating or accepting this ADR.

## Consequences

- WP70 — F08 Experimentation & Rollout detailed evidence is **DONE as a planning/evidence package**.
- No runtime-ready claim for F08 is valid until applicable EXP fixtures actually execute and retain evidence.
- Future implementations must preserve F02/F07/Policy/privacy ownership boundaries and must not create duplicate analytics, placement or authorization engines.
- Performance and kill-switch propagation claims require measured evidence; planning text is not certification.
- Any new experiment/rollout profile that falls outside the accepted fixture applicability remains uncertified until evidence is expanded/superseded through ADR.

## Next safe planning work

Continue **WP71 — F09 Documents, Records & Templates detailed executable-evidence specification (`DOC-001…DOC-176`)**.

Production development remains **NOT GRANTED / 0/56**.