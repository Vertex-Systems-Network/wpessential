# Summary

<!-- What changed and why? Keep this concrete. -->

## Work / Approval

**Work ID:**

**Milestone / Module:**

**Lifecycle State:**

**Approval Scope / ID:**

<!-- Use N/A only for documentation/planning work that genuinely requires no development approval. -->

## Requirement / Problem

<!-- Link the issue/spec/module requirement. Classify discovered gap where relevant: CORRECTION / COMPLETION / HARDENING / OPTIMIZATION / NEW_PRODUCT_SCOPE. -->

## Research

<!-- List actual official/current sources reviewed when external decisions mattered. Write N/A only when genuinely not needed. -->

## Change Budget / Parallelism

**Expected files/modules/APIs/migrations/dependencies:**

**Actual expansion vs estimate:**

**Critical-path class:**

**Parallelism class:** `PARALLEL_SAFE / COORDINATED_PARALLEL / SERIALIZE / BLOCKED`

**Shared surfaces / owner / merge order:**

## Change Impact

**Affected:**

**Unaffected:**

**Risk:**

**Migration:**

**Rollback / Recovery:**

**Recovery Class:** `SIMPLE_ROLLBACK / ROLLBACK_WITH_COMPATIBILITY / FORWARD_FIX_PREFERRED / IRREVERSIBLE / N/A`

**Verification:**

## Architecture

<!-- Which existing WPEssential services/contracts are reused? Any new pattern/dependency/ADR? -->

## Security / Negative Requirements

<!-- Authentication, authorization, CSRF, XSS, SQLi, SSRF, secrets, uploads, IDOR, rate limits, destructive operations, multisite as applicable. List important MUST-NOT behavior and its evidence/test. -->

## Data / Migrations

<!-- Tables/options/files/definitions changed; existing-data impact; indexes; concurrency; deployment ordering; rollback/recovery. -->

## Performance / Assets

<!-- Query count, payload/bundle cost, background work, caching, exact screens where CSS/JS loads. -->

## UX / Accessibility

<!-- Loading/empty/error/disabled/success states; keyboard/focus/screen readers/responsive behavior. -->

## FAST Gate Executed

- [ ] Relevant formatting/coding standards
- [ ] Targeted static analysis/typecheck
- [ ] Targeted lint
- [ ] Targeted unit tests
- [ ] Targeted integration/permission tests
- [ ] Affected production build
- [ ] Targeted security/static checks

<!-- Mark only checks actually run. -->

## FULL Gate Executed / Required

- [ ] Broad unit tests
- [ ] WordPress integration tests
- [ ] REST/API permission tests
- [ ] E2E tests
- [ ] Migration/upgrade/recovery tests
- [ ] Security regression tests
- [ ] Compatibility tests
- [ ] Dependency audit
- [ ] Production build/package checks
- [ ] Performance/regression evidence

<!-- Explain applicable unchecked items and whether this boundary requires the FULL Gate. -->

## Existing / Unstable Failures

**BASELINE FAILURE:**

**FLAKY / INVESTIGATING:**

<!-- Do not rerun-until-green and hide instability. Include IDs/evidence. -->

## Review Classification

- [ ] INDEPENDENT REVIEW
- [ ] SELF REVIEW
- [ ] AUTOMATED REVIEW

**Reviewer / tool / notes:**

## Evidence

**Verified:**

**Not Verified:**

**Known Risk:**

## Release State

**State:** `NOT_RELEASED / BUILT / DEPLOYED / RELEASED / PRODUCTION_VERIFIED`

**Exact revision/artifact/environment:**

**Post-deployment verification:**

## Documentation / History

- [ ] relevant docs updated
- [ ] project/adoption baseline updated if state/capability changed
- [ ] approval ledger updated if approval state changed
- [ ] ADR added/updated if architecture changed
- [ ] changelog/release note updated when user-visible
- [ ] `CHECKPOINT.md` updated for a meaningful milestone
- [ ] commits are coherent and explain intent

## Final Adversarial Review

<!-- If deployed today and it failed at 3 AM: what breaks, what could be exposed/corrupted, how is the failure detected, and how do we recover? Any stop-the-line trigger? -->
