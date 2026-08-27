# WPEssential — CI / Test Matrix Plan

Status: **Phase 0 planning / Proposed / no CI workflow implemented**  
Related: ADR-0011, ADR-0002, ADR-0012, ADR-0014

This document defines what CI must eventually prove. It does not create GitHub Actions or execute tests.

## 1. Principles

- CI proves behavior; it is not a green-badge ceremony.
- Fast required checks belong on PRs.
- Expensive compatibility/provider/performance matrices run on scheduled/release lanes.
- Minimum-supported and current-supported environments are both first-class.
- No test is allowed to rely on production credentials.
- Provider integration tests use dedicated sandbox/test accounts and secret isolation only after those integrations exist.
- A release artifact is tested as an installable ZIP, not only source tree.

---

# 2. Proposed compatibility dimensions

Pending ADR-0002 acceptance:

## WordPress
- minimum candidate: 6.9;
- current stable: 7.1 at this planning date;
- latest development/nightly WordPress: scheduled compatibility lane only.

## PHP
- minimum candidate: 8.3;
- 8.4;
- 8.5.

## Database representative targets

Current WordPress recommended baselines suggest:
- MySQL 8.0;
- MariaDB 10.11.

Exact WPEssential DB minimum remains separate from WordPress's legacy ability to run on older versions and must be accepted before implementation.

## Site modes
- single site;
- multisite for modules declaring multisite support.

## Cache modes
- no persistent object cache;
- representative persistent object cache lane after cache-sensitive runtime exists.

---

# 3. PR lane — fast mandatory quality

Target: bounded enough to run on every meaningful PR.

Planned checks:

### Repository hygiene
- forbidden committed secrets scan;
- generated/binary artifact policy;
- version/metadata consistency;
- changelog/spec/ADR checks when required by change type.

### PHP
- Composer validate;
- PHP syntax/lint;
- coding standards;
- static analysis;
- focused PHPUnit unit tests;
- focused WordPress integration tests.

### JS/TS/CSS
- package/lock validation;
- formatting/lint;
- TypeScript typecheck;
- unit/component tests;
- production build;
- asset dependency/externalization validation;
- CSS/RTL build validation;
- bundle-budget check once baselines exist.

### Security/static
- dependency advisory checks;
- project-defined dangerous-pattern checks (`eval`, unsafe SQL patterns, secret exposure, global enqueue anti-patterns, etc.);
- REST/Ability permission-schema linting where machine-checkable.

### Install smoke
- build/install plugin ZIP into representative WordPress environment;
- activate/deactivate;
- verify no fatal;
- basic health/API response.

No command/tool choice is accepted yet; this lists required outcomes.

---

# 4. PR compatibility lane

At least two representative environments once runtime exists:

1. **minimum contract** — minimum WP + minimum PHP;
2. **current contract** — current WP + current preferred PHP.

Purpose:
- prevent developing only against newest environment;
- detect accidental dependency floor increases.

A third latest-PHP lane can be required on main/nightly if PR cost is excessive.

---

# 5. Main branch lane

Adds broader integration after merge:
- all PHP supported candidate versions on current WP;
- minimum WP on minimum/current PHP;
- MySQL + MariaDB representative lanes;
- broader integration tests;
- module enable/disable combinations;
- Free-only and Free+Pro fixtures when Pro code exists;
- migration upgrade fixtures;
- artifact/package inspection;
- E2E critical paths.

---

# 6. Nightly compatibility matrix

Proposed matrix after source exists:

### Core axes
- WP minimum supported;
- WP current stable;
- WP trunk/nightly non-blocking early-warning lane;
- PHP 8.3/8.4/8.5;
- MySQL 8.0;
- MariaDB 10.11.

Avoid full Cartesian explosion where it adds little evidence. Use pairwise/risk-based coverage plus key full combinations.

### Environment variations
- multisite;
- persistent object cache;
- `WP_DEBUG`/strict logging;
- pretty permalinks;
- low cron traffic simulation for scheduler health where relevant;
- large fixture dataset/performance lane.

---

# 7. Free ↔ Pro compatibility lane

After both artifacts exist, required combinations from compatibility state machine:
- Free current / Pro absent;
- Free absent / Pro current → no fatal/inert;
- compatible current/current;
- previous supported Free / current compatible Pro;
- current Free / previous compatible Pro;
- too-old Free / new Pro → degraded notice, no mutation;
- too-new incompatible Free / old Pro → degraded notice, no mutation;
- interrupted/missing migration;
- expired entitlement separate from binary compatibility.

Test both update orders:
- Free first;
- Pro first.

---

# 8. Migration lane

Every schema release includes fixtures from supported historical versions.

Test:
- fresh install;
- one-version upgrade;
- oldest supported upgrade path;
- interrupted migration recovery where relevant;
- idempotent rerun;
- invalid/corrupt migration metadata;
- downgrade/read-only behavior where downgrade is supported;
- backup/restore requirement for irreversible migrations.

Never write a migration without a fixture representing existing data.

---

# 9. Module security regression lanes

Examples once modules exist:

## Definition builders
- capability failure;
- CSRF/nonce failure;
- invalid payload/schema;
- stored XSS attempts;
- dependency deletion conflict.

## Query/REST
- SQL injection payloads;
- data-scope bypass;
- IDOR;
- public expensive query abuse;
- CORS/auth misconfiguration.

## Forms/uploads
- MIME spoofing;
- hidden field tampering;
- role escalation;
- CSRF/replay;
- spam/rate limit.

## Membership
- stale-cache access after revoke;
- deny/allow precedence;
- protected-file direct access;
- webhook replay/out-of-order events;
- manual override capability.

## Backup/import
- archive traversal;
- unsafe file paths;
- oversized/decompression abuse;
- restore privilege checks.

## Connections
- SSRF/private IP/redirect/DNS rebinding fixtures;
- secret redaction.

---

# 10. E2E lanes

Use representative product workflows rather than screenshot-only tests.

Reference E2E scenarios:

1. create CPT + taxonomy → publish → edit content;
2. fields + relation + query + listing;
3. role + dashboard + profile + protected route;
4. membership enrollment → entitlement → access → expiry/revoke;
5. form → workflow → job → notification/webhook;
6. backup → fixture corruption/change → restore verification;
7. import package with dependency conflict and dry-run.

Include keyboard/accessibility critical flows where practical.

---

# 11. Performance lanes

Not every PR, but scheduled/release gates for performance-sensitive services.

Fixtures:
- 10k/100k definitions;
- large dependency graph;
- large relation dataset;
- expensive query candidate;
- 50k queued jobs;
- large import chunks;
- large backup file counts;
- high Membership access-check volume.

Measure:
- DB queries;
- p50/p95/p99 where test harness is stable;
- memory;
- serialized payload size;
- asset size;
- queue lag/throughput;
- cache hit/invalidation behavior.

Budgets are established from evidence, not guessed universal numbers.

---

# 12. Provider/integration lanes

Provider tests are isolated from general PR CI.

For each supported provider:
- test sandbox credentials;
- connection/auth;
- upload/request/create operation;
- read/status;
- failure/auth-expiry;
- retry/rate-limit where provider supports test;
- cleanup.

Backup providers require stronger acceptance:
- upload;
- list/find;
- download;
- checksum/integrity;
- interrupted/resume where claimed;
- restore-path fixture where applicable.

A provider is not marketed supported before its acceptance lane passes.

---

# 13. Release-candidate lane

Before release:
- all required PR/main checks green;
- compatibility matrix green;
- migration fixtures green;
- security regressions green;
- installable Free ZIP verified;
- Pro ZIP verified separately when applicable;
- Free artifact contains no Pro locked runtime code;
- plugin metadata/header/Composer/Platform API versions consistent;
- no dev dependencies/source maps/secrets accidentally shipped unless release policy permits source maps;
- license notices complete;
- POT/translations generation checks;
- asset scope/budget checks;
- changelog/release notes/migration notes present;
- rollback procedure documented.

---

# 14. WordPress.org/Free artifact lane

When Free submission/release becomes relevant:
- Plugin Check or current official equivalent;
- directory guideline review;
- no trialware/premium code leakage;
- no unexpected remote calls before consent/action;
- assets/readme requirements;
- stable tag/package checks.

Exact tooling is re-researched at release time.

---

# 15. Test evidence reporting

Every CI failure must be classifiable:
- product regression;
- compatibility regression;
- flaky/infrastructure;
- provider outage;
- expected upstream/trunk break.

Do not mark red checks ignored without issue/decision evidence.

Provider/trunk informational lanes may be non-blocking; security/minimum/current artifact lanes are blocking.

---

# 16. Future CI implementation — NOT AUTHORIZED

No `.github/workflows` implementation, dependency install, container matrix or test scaffold may be created under this plan until explicit owner development consent.

After consent, ADR-0011 should be accepted only after:
- ADR-0002 compatibility floor accepted;
- ADR-0012 toolchain accepted;
- representative CI prototype passes;
- execution cost/time is measured;
- branch-protection required checks are defined.
