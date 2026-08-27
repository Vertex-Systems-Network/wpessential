# WPEssential — Engineering Checkpoint

Checkpoint date: **2026-08-27**  
Branch: `planning/master-architecture`  
Project state: **PARTIALLY COMPLETE — Phase 0 planning only**

## Current objective

Finish evidence-backed architecture decisions after completing option-level behavioral planning for the entire WPEssential product. Production feature development has **not** started.

## Verified completed work

### Repository/reference audit
- Target repo was verified from its initial minimal state and all planning work is isolated on `planning/master-architecture`.
- Legacy `wpessential/wpessential-dashboard-builder` was inspected for structure, dependencies, history and representative module code.
- Useful experiments identified: REST, migrations, backup adapters, cron, reset, Elementor/forms/chat assets.
- Legacy drift recorded and must not be copied blindly: PHP-version mismatch, `inc/daabase` PSR-4 typo, mixed Laravel Mix/Vite tooling, unconditional admin timing log and weak historical commit messages.

### Public/current research
Official/current sources were reviewed for WordPress core/platform APIs, Abilities/AI direction, REST auth, WP-Cron, Action Scheduler, plugin-directory commercial rules, testing/accessibility, UI licenses, page-builder extension APIs and competitor modules.

Membership research now additionally covers official/current documentation from MemberPress, Paid Memberships Pro, WooCommerce Memberships/Subscriptions, SureMembers and Restrict Content Pro.

Research notes:
- `docs/RESEARCH/COMPETITIVE-LANDSCAPE.md`
- `docs/RESEARCH/MODULE-BENCHMARK-MATRIX.md`
- `docs/RESEARCH/MEMBERSHIP-LANDSCAPE.md`

## Product scope status

### 31/31 module/platform surfaces inventoried
`docs/MODULES/OPTION-INVENTORY.md` records screens and small controls/options for all product surfaces, including:
- CPT/Taxonomy/Fields/Relations/Status;
- Query/Tables/Admin Columns/Listings;
- Dashboard Widgets/Admin Menu/Settings/Dashboard/Builder Widgets;
- User Profile/**Membership**/Roles;
- Forms/Workflow/Cron/Notifications/Email/Chat;
- REST/Webhooks/Import Export;
- Backup/Reset/Protector/Watermark/XML-RPC;
- onboarding/Home/Modules/Account/Plans/Docs/Changelog/Support/Diagnostics.

### 31/31 behaviorally specified
Detailed Phase 0 behavioral specifications exist in:
- `docs/MODULES/SPECIFICATION-STANDARD.md`
- `docs/MODULES/COMMON-OPTION-CONTRACTS.md`
- `docs/MODULES/CONTENT-MODEL-SPECS.md`
- `docs/MODULES/DATA-QUERY-SPECS.md`
- `docs/MODULES/ADMIN-EXPERIENCE-SPECS.md`
- `docs/MODULES/IDENTITY-ACCESS-SPECS.md`
- `docs/MODULES/MEMBERSHIP-SYSTEM.md`
- `docs/MODULES/AUTOMATION-COMMUNICATION-SPECS.md`
- `docs/MODULES/INTEGRATION-DATA-SPECS.md`
- `docs/MODULES/OPERATIONS-PROTECTION-SPECS.md`
- `docs/MODULES/PLATFORM-SURFACES-SPEC.md`

`docs/MODULES/README.md` is the detailed-spec index and maturity ledger.

### Meaning of “Specified”
Specified means intended screens, controls, defaults, validation, permissions, lifecycle, dependencies, security/failure behavior, assets and acceptance tests are documented at Phase 0 behavioral level.

It does **not** mean:
- runtime DB schemas are benchmarked;
- implementation dependencies are accepted;
- code exists;
- tests passed;
- a provider integration works;
- production readiness is established.

Those require Accepted ADRs, implementation and verification.

## Membership System added

Membership is a full Pro module, not a role/paywall checkbox.

Accepted product architecture in **ADR-0013**:
- WordPress User = identity;
- Role/Capability = WordPress authorization primitive;
- Membership Plan = configured product/access package;
- Enrollment = user's membership lifecycle instance;
- external Subscription/Purchase = billing source/reference;
- Entitlement = normalized grant/benefit;
- Access Rule/Policy = resource/action decision logic.

Consequences:
- multiple simultaneous memberships can exist where group policy permits;
- billing providers are adapters, not membership source of truth;
- role sync is optional side effect and disabled by default;
- WPEssential does not process/store payment-card credentials;
- access can consistently protect content, partial components, downloads, dashboards, forms, listings and approved REST/Abilities;
- WPEssential Pro expiry/module management failure must not expose protected member resources.

Detailed Membership planning includes plans/groups, enrollment states, free/manual/paid-source grants, eligibility, capacity/approval, lifetime/fixed/source-controlled duration, trial/grace, access rules, unauthorized behavior, partial-content protection, protected downloads, benefits/discount adapters, drip, upgrades/downgrades, billing event mapping, webhooks/reconciliation, members/enrollments, manual overrides, seats/teams, invitation promotions, member pages, role sync, abilities/capabilities, performance/security/audit/import/expiry and acceptance tests.

## Accepted architecture/product decisions
- **ADR-0001:** Free WordPress.org plugin + separate Pro add-on; Pro trial belongs to external Pro entitlement/add-on.
- **ADR-0003:** typed WordPress Abilities are reusable action contract where applicable.
- **ADR-0004:** no standard arbitrary PHP `eval()` or unrestricted destructive SQL primitive.
- **ADR-0007:** WPE Pro expiry preserves data and safe deployed runtime.
- **ADR-0013:** Membership, billing subscriptions, roles and entitlements are separate domains.

## Proposed Phase 0 platform blockers
Need evidence/acceptance before production platform source work:
- **ADR-0002:** compatibility floor;
- **ADR-0005:** UI/design system;
- **ADR-0006:** background job implementation;
- **ADR-0008:** concrete Definition Repository schema/indexes;
- **ADR-0009:** secrets/key/recovery model;
- **ADR-0010:** Free↔Pro compatibility protocol;
- **ADR-0011:** CI/test matrix;
- **ADR-0012:** canonical build toolchain.

## Membership-specific implementation blockers
Before Membership runtime implementation, resolve through ADR/spikes:
1. entitlement schema/storage/derivation;
2. allow/deny precedence and explainability;
3. enrollment/runtime tables and indexes;
4. entitlement cache invalidation/revocation latency;
5. protected-file delivery across Apache/Nginx/CDN/media offload;
6. initial billing adapter contracts and reconciliation;
7. role-sync conflict/anti-lockout semantics;
8. privacy/retention for enrollment and external-event history;
9. migration fidelity from major membership products;
10. seat/team concurrency model.

## Important rules to preserve
1. WPEssential is one platform of shared engines, not isolated mini-plugins.
2. Free CPT + Taxonomy work permanently without an account.
3. No WordPress.org locked trialware.
4. No production module implementation while behavior is only an unresolved feature list.
5. Every new option discovered during implementation must be documented before/in the same coherent change.
6. Optional module CSS/JS loads only on exact screens/runtime paths that use it.
7. Arbitrary PHP/SQL is not a standard power-user primitive.
8. WP-Cron schedule UI and durable background Job Service remain distinct.
9. AI/MCP can call permission-aware typed Abilities only; it is not a privileged backdoor.
10. Definition Repository stores platform configuration, not all WordPress/runtime data.
11. Secrets use Vault references; no plaintext ordinary config/export.
12. Backup providers are “supported” only after integrity/restore-oriented adapter tests.
13. Original media remains unchanged by Watermark standard flow.
14. Menu visibility/login URL changes are not authorization.
15. Membership is not a WordPress role and WPEssential is not a card processor.
16. License/module failure cannot silently expose protected content or files.

## Verification in this checkpoint

### Verified
- authenticated target/reference GitHub repositories accessible;
- planning branch writes/commits succeeded;
- detailed module option inventory exists;
- suite/module behavioral specs exist for all 31 surfaces;
- Membership research/spec/ADR were actually created;
- no production implementation/testing success is claimed.

### Not verified / intentionally not applicable yet
- Composer installation/build;
- PHP lint/static analysis;
- React/TypeScript build;
- PHPUnit;
- Playwright/E2E;
- DB migrations;
- plugin activation;
- WordPress/PHP compatibility matrix;
- security test suite;
- Membership runtime/access checks;
- billing/webhook integrations;
- protected-file delivery;
- backup/restore implementation;
- Free/Pro artifact packaging.

## Next recommended action

Remain in **Phase 0** and resolve evidence blockers rather than starting feature code.

Order:
1. Compatibility + toolchain spike.
2. Definition Repository schema/index benchmark.
3. Job Service / Action Scheduler coexistence spike.
4. Secrets threat model/key/recovery prototype.
5. Free↔Pro bootstrap/update-order compatibility spike.
6. CI matrix acceptance.
7. Entitlement/Policy precedence and Membership runtime-data design spike.
8. Protected-file + billing-adapter/reconciliation threat-model spikes before Membership phase implementation.
9. Update ADR statuses/checkpoint after evidence.
10. Only after platform blockers are Accepted begin Phase 1 kernel + Free CPT/Taxonomy.

## Resume instruction

Future AI/engineer must read `AGENTS.md`, this checkpoint, `docs/PRODUCT-MASTER-PLAN.md`, `docs/MODULES/README.md`, relevant detailed module specs and ADRs before making code changes. Repository evidence overrides conversational memory. Proposed ADRs are not Accepted merely because they are recommended.
