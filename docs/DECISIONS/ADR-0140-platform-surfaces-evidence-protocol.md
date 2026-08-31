# ADR-0140 — Platform Account / Docs / Support / Diagnostics Executable Evidence Protocol

Status: **Accepted evidence protocol / execution pending**  
Date: 2026-08-28  
Work package: `P0-M00-WP23`

## Decision

Accept `docs/QUALITY/PLATFORM-SURFACES-EXECUTABLE-EVIDENCE-PROTOCOL.md` as the canonical future executable-evidence contract for the cross-surface behavior of WPEssential Home, Modules, Onboarding, Account/License, Documentation, Changelog, Support and Diagnostics.

The protocol freezes **PLT-01…PLT-176**.

It is intentionally a **composition protocol**, not a replacement for the already-canonical lower-level evidence contracts.

## Existing protocols remain authoritative

PLT does not duplicate or supersede:

- FP-01…FP-144 — Free↔Pro artifact/API/schema/entitlement compatibility;
- OA-01…OA-32 — OAuth Account Link security/token lifecycle;
- TU-01…TU-44 — TUF updater trust/anti-rollback;
- RS-001…RS-030 — remote-service privacy/minimization/retention/deletion/clone semantics;
- VT-01…VT-128 — Vault secret handling;
- UI/BT/CI/CF and Multisite/Site Lifecycle evidence.

A PLT fixture that depends on uncertified lower-level evidence remains BLOCKED/NOT CERTIFIED. PLT cannot promote an unexecuted dependency to green.

## Accepted truth boundary

The following remain distinct:

`local onboarding state ≠ remote Account connection ≠ OAuth credential validity ≠ commercial Account/Plan state ≠ Site/Network Allocation ≠ signed Product Entitlement ≠ Free/Pro binary compatibility ≠ TUF update authority ≠ local module enabled state ≠ module runtime health ≠ remote Support authority ≠ local Support cache/draft ≠ Diagnostics generated ≠ Diagnostics transmitted ≠ Docs/Changelog cache freshness ≠ remote service status ≠ local service health ≠ certified platform behavior`

No generic `Connected`, `Active`, `Healthy`, `Sent`, `Deleted`, `Current` or `Verified` label may collapse materially different facts.

## Fixed evidence coverage

- first-run/onboarding/platform shell — PLT-01…PLT-16;
- Home and Modules composition — PLT-17…PLT-32;
- Account/License/Plan/Entitlement presentation — PLT-33…PLT-48;
- Docs/Changelog/release content — PLT-49…PLT-64;
- Support ticket/thread lifecycle — PLT-65…PLT-80;
- Support attachments/downloads/concurrency — PLT-81…PLT-96;
- Diagnostics/System Status/report/repair — PLT-97…PLT-112;
- remote transport/cache/schema/degraded composition — PLT-113…PLT-128;
- Multisite/environment/clone/restore/allocation composition — PLT-129…PLT-144;
- trust/privacy/update separation/failure containment — PLT-145…PLT-160;
- authorization/accessibility/operability/observability/scale — PLT-161…PLT-176.

## Certification classes

Certify independently:

- `PLT-H` onboarding/Home/platform shell;
- `PLT-MOD` Modules lifecycle/dependency/degraded state;
- `PLT-A` Account/License/Plan/entitlement presentation;
- `PLT-D` Docs/Changelog/release content;
- `PLT-S` Support ticket/message/attachment composition;
- `PLT-X` Diagnostics/System Status/report/repair;
- `PLT-R` remote transport/cache/error/degraded composition;
- `PLT-MS` Multisite/environment/allocation/clone/transfer composition;
- `PLT-P` trust/privacy/consent/update separation;
- `PLT-O` authorization/accessibility/operability/performance/observability.

There is no umbrella Platform certification until all required classes and dependencies for the claimed profile are actually proven.

## Accepted invariants

1. Free CPT/Taxonomy remains usable without WPE Account linkage or hidden remote activation.
2. onboarding completion is local UX state, not proof of Account/Allocation/Entitlement success.
3. Account connection never implies signed Product Entitlement or child-site Allocation.
4. Product Entitlement never implies WordPress/Membership authorization.
5. service outage is not expiry/revocation.
6. ordinary Account/Catalog/Docs/Support/Status JSON cannot grant Pro or update trust.
7. TUF/update trust remains separate from release notes/catalog display.
8. Support remote service is authoritative for submitted tickets; local cache/draft is not remote truth.
9. Diagnostics generation is local until explicit transmission approval; Diagnostics never auto-upload with Support.
10. remote content cannot inject arbitrary HTML/JS/PHP/package/repair execution into wp-admin.
11. stale cache is labeled and never extends security/commercial authority beyond its owning contract.
12. Network connection does not silently allocate every child site or expose Network secrets to Site Admins.
13. module disable is not data deletion; remote state changes do not destructively remove local data.
14. Account disconnect, allocation release, Account deletion and local cache deletion remain separate actions.
15. cross-surface errors degrade the affected service/surface where safe rather than trapping global wp-admin.
16. every platform mutation remains server-authorized by current capability/Policy/target scope.

## Current evidence state

- PLT documented: **176**.
- PLT executed: **0/176**.
- all `PLT-*` certification classes: **0**.
- FP remains **0/144**.
- OA remains **0/32**.
- TU remains **0/44**.
- Remote privacy RS remains **0/30**.
- VT remains **0/128**.
- UI remains **0/104**.
- Multisite remains **0 MS1+**.
- Site Lifecycle remains **0/40**.
- no platform runtime/service certification exists.

## Rejected shortcuts

- remote Account requirement for Free local use;
- onboarding success used as Account/License success;
- Account connection used as entitlement/allocation authority;
- service outage labeled license expiry;
- stale cache shown as fresh verified state;
- local Support cache treated as remote ticket authority;
- auto-uploaded Diagnostics;
- support retry that creates duplicate messages/tickets;
- local cache deletion labeled remote erasure;
- arbitrary remote HTML/JS/PHP/repair execution;
- ordinary release/catalog JSON used as package/update trust;
- cross-site/network private Account/Support leakage;
- hostname/blog ID used as sole commercial identity;
- module disable causing data deletion;
- PLT pass manufactured from unexecuted OA/FP/TU/RS prerequisites.

## Development gate

No source implementation, remote service endpoint, OAuth/account request, entitlement refresh, docs/support/status call, support mutation, attachment transfer, diagnostics generation/upload, repair action, package/update operation, browser/runtime test, clone/restore or benchmark is authorized by this ADR.

ADR-0014 and the Approval Ledger still require explicit scoped owner consent before executable evidence or implementation.

Current execution count remains **0/176**.