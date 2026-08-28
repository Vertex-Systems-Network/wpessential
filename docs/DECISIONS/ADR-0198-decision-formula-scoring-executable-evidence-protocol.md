# ADR-0198 — F04 Decision, Formula, Scoring & Ranking Detailed Executable Evidence Protocol

Status: **Accepted — planning/evidence only**  
Date: **2026-08-29**  
Work package: **WP66**  
Supersedes: none

## Context

ADR-0177 accepted F04 — Decision, Formula, Scoring & Ranking Studio as a reusable universal foundation. The universal technical evidence master plan reserved `DEC-001…DEC-176` as 16 groups × 11 fixtures, but those IDs remained group-level envelopes.

WP66 must freeze the fixture-level evidence before implementation so formula correctness, score/ranking semantics, high-risk governance, Policy isolation and deterministic behavior cannot later be claimed from UI completeness or a few happy-path tests.

## Decision

Accept `docs/QUALITY/DECISION-FORMULA-SCORING-EXECUTABLE-EVIDENCE-PROTOCOL.md` as the canonical detailed evidence protocol for F04.

The protocol fully enumerates **DEC-001…DEC-176** across:

1. typed formula AST/parser/versioning;
2. decimal precision, rounding, overflow and divide-zero;
3. currency, unit, date and duration correctness;
4. input source/null/default/range validation;
5. lookup tables/effective dates/version pinning;
6. scorecards/weights/normalization/missing factors;
7. decision-table overlap/hit/priority/no-match/unreachable rows;
8. ranking eligibility/ties/diversity/manual pins;
9. simulation/version comparison/sensitivity/no-write;
10. publish/approval/high-risk financial and risk policy;
11. Query/Workflow/Placement/Search/Analytics/Ledger/etc. consumer boundaries;
12. malicious-expression/no-eval/execution budgets;
13. cache/version/invalidation/Audit/explanations;
14. Multisite templates and site isolation;
15. 10K/100K/1M-scale batch/performance evidence;
16. deterministic golden vectors, cross-runtime and AI/adversarial regression.

## Architecture invariants

- Formula/score/decision/rank is derived data, **not authorization**.
- F04 cannot directly become ledger/payment/order/inventory/reservation mutation authority.
- Expressions use a registered typed grammar/AST. Arbitrary PHP, JavaScript, SQL, shell and hidden provider execution are prohibited.
- Binary floating point is not canonical money arithmetic.
- Cross-currency computation requires explicit conversion source, effective time and provenance; missing rates are never invented.
- Unit operations require compatible dimensions and registered conversion semantics.
- `missing`, `null`, `denied`, `invalid`, `unavailable`, `no-match`, false and numeric zero remain semantically distinct where applicable.
- Published revisions are immutable evaluation identities; approvals bind to exact revision fingerprints.
- High-risk financial/risk definitions may require maker-checker/re-auth Policy and AI cannot self-approve them.
- Consumer modules remain authoritative for their own Policy and mutations. A high score or manual pin cannot resurrect an ineligible candidate.
- Explanations/Audit must redact protected facts; explanation visibility never grants input visibility.
- Protected cache keys include all required revision/scope/authorization dimensions.
- Multisite ownership is durable and server-resolved; site/network context cannot be supplied as authority by the caller.
- AI/MCP-generated formulas/tables receive exactly the same grammar, type, budget, Policy, revision and approval gates as human-authored definitions.

## Evidence truth

At acceptance:

- DEC documented: **176/176**;
- DEC executed: **0/176**;
- F04 runtime certification: **0**;
- implementation authorization: **not granted**;
- current product denominator remains **56/56 planned, 0/56 authorized**.

No parser/evaluator, formula, lookup, scorecard, decision table, ranking engine, simulation, benchmark, cache, provider, AI/MCP runtime or database mutation was executed by this ADR.

## Consequences

WP66 is complete as a detailed planning/evidence package. Future implementation cannot claim F04 runtime readiness until the applicable DEC fixtures are executed with retained evidence under separate development authorization.

The universal evidence sequence may advance to **WP67 — F05 Ledger, Balance & Movement (`LED-001…LED-176`)** without changing the reserved meanings of WP67…WP74.

## Next safe planning action

Start WP67 by expanding the fixed LED group envelope into a fixture-level executable-evidence protocol. This remains documentation/specification only until explicit scoped development consent is recorded.
