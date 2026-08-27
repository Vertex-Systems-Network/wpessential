# WPEssential — Engineering Checkpoint

Checkpoint date: **2026-08-27**  
Branch: `planning/master-architecture`  
Project state: **PARTIALLY COMPLETE — Phase 0 planning only**

## Current objective

Complete and review the durable product/architecture plan before production feature development begins.

No WPEssential production plugin source has been implemented in this repository yet.

## Verified completed work

### Repository / reference audit

- Target repo was verified to contain only its initial minimal README before planning work.
- Planning work is isolated on `planning/master-architecture`.
- Legacy reference repo `wpessential/wpessential-dashboard-builder` was inspected for structure, dependencies, recent history and representative module code.
- Useful legacy areas identified: REST, database migration, backup storage adapters, cron, reset, Elementor, forms/chat assets.
- Legacy drift recorded: PHP requirement mismatch, PSR-4 `daabase` typo, mixed Laravel Mix/Vite dependencies, unconditional admin timing log, low-information commit history.

### Public/current research

Official/current sources were reviewed for:
- WordPress 7.1/current platform
- WordPress Abilities API and AI/MCP direction
- REST authentication
- WP-Cron semantics
- Action Scheduler
- WordPress.org trialware/distribution rules
- WordPress testing/Playground/accessibility
- Untitled UI/Lucide/WordPress UI packages and licensing
- CPT UI / SCF / ACF / Meta Box / JetEngine
- Admin Columns
- admin menu/dashboard tools
- roles/capabilities tools
- Gravity Forms / Fluent Forms / AutomatorWP
- backup/import tools
- notification/email/chat tools
- Gutenberg/Elementor/WPBakery extension APIs
- settings/profile/status/custom-table/Cron/REST/Reset/Protector/Watermark/XML-RPC benchmark products and APIs

Research notes live in:
- `docs/RESEARCH/COMPETITIVE-LANDSCAPE.md`
- `docs/RESEARCH/MODULE-BENCHMARK-MATRIX.md`

### Product / architecture docs

Completed:
- `README.md`
- `AGENTS.md`
- `docs/PRODUCT-MASTER-PLAN.md`
- `docs/ARCHITECTURE.md`
- `docs/MODULE-CATALOG.md`
- `docs/SECURITY.md`
- `docs/COMMERCIAL-DISTRIBUTION.md`
- `docs/QUALITY-GATES.md`
- `docs/ROADMAP.md`

### Accepted architecture/product decisions

- **ADR-0001:** Free WordPress.org plugin + separate Pro add-on; 30-day trial applies to Pro entitlement/add-on.
- **ADR-0003:** typed WordPress Abilities are the primary reusable action contract where applicable.
- **ADR-0004:** no standard arbitrary PHP `eval()` / unrestricted destructive SQL primitive.
- **ADR-0007:** license expiry preserves data and safe deployed runtime; editing/creation/unsafe operations can lock/pause rather than breaking public sites.

### Proposed Phase 0 blockers

Need evidence/acceptance before production source work:
- **ADR-0002:** compatibility floor
- **ADR-0005:** UI/design system
- **ADR-0006:** background job implementation
- **ADR-0008:** concrete definition storage schema
- **ADR-0009:** secret/key/recovery model
- **ADR-0010:** Free↔Pro compatibility protocol
- **ADR-0011:** CI/test matrix
- **ADR-0012:** canonical build toolchain

## Product scope captured

All originally requested modules are represented in `MODULE-CATALOG.md`:

- Admin Columns Builder
- Dashboard Widgets Manager
- Backup Manager
- Cron Job Builder
- Custom Admin Menu Builder
- Custom Fields Builder
- Custom Query Builder
- Custom Tables Builder
- Dashboard Builder
- Emails Builder
- Builder Widgets Builder
- Forms & Workflow Builder
- Message & Chat System
- Notification System
- Custom Post Types Builder
- Reset Manager
- REST API Builder
- Role & Capability Manager
- Settings Page Builder
- Status Manager
- Taxonomy Builder
- User Profile Builder
- XML-RPC Manager
- Import / Export
- Protector
- Watermarker / Media Rules

Strategic gaps added to the product plan:
- Relations Builder
- Dynamic Listings / Template Builder
- Webhooks & Connections Manager

Shared infrastructure (not sold as isolated modules) includes Module Registry, Definition Repository, Data Source/Field/Relation/Query/Condition/Renderer engines, Policies, Abilities, Event Bus, Jobs, Secrets, Integrations, Audit, Assets, Diagnostics, Import/Export package service and extension SDK.

## Important decisions / corrections to preserve

1. WPEssential is not a loose collection of mini-plugins; modules compose shared engines.
2. Free CPT + Taxonomy remain permanently available without account creation.
3. A WordPress.org package must not contain locked trialware premium modules.
4. Existing safe public output should not disappear on license expiry by default.
5. User-entered arbitrary PHP execution is not a standard Cron/Workflow feature.
6. Custom Tables is not an unrestricted embedded phpMyAdmin clone.
7. Browser page-builder HTML is not automatically a valid cross-client email template.
8. WP-Cron timing is not exact; scheduling and durable job execution are separate.
9. AI/MCP is an allowlisted caller of permission-aware abilities, not a privileged backdoor.
10. Original media is preserved by the Watermarker; derived renditions are regenerated.
11. Login/admin URL hiding is optional obfuscation/noise reduction, not an authorization boundary.
12. Definition storage is for configuration, not a universal EAV replacement for WordPress/application data.
13. Every optional module's CSS/JS must load only on exact screens/runtime paths that use it.
14. A backup destination is not marketed as supported until upload/download/integrity/restore-related acceptance is verified.

## Verification performed in this checkpoint

Because this branch currently contains documentation/planning only:

### Verified
- target and reference repositories accessible through authenticated GitHub integration;
- branch creation succeeded;
- all listed planning files were written through GitHub and commits were returned successfully;
- current external research was actually performed and recorded;
- no feature implementation/testing was falsely claimed.

### Not Verified / not applicable yet
- Composer install/build
- PHP lint/static analysis
- React/TypeScript build
- PHPUnit
- Playwright/E2E
- database migrations
- plugin activation
- WordPress compatibility matrix
- security test suite
- Free/Pro artifact packaging

These cannot be executed because production source/tooling intentionally does not exist yet.

## Known risks / unresolved questions

### High
- Compatibility floor is not accepted; PHP 8.2 vs 8.3 has a security-lifecycle vs installable-market tradeoff.
- Definition tables/indexes/transaction model are not designed/benchmarked yet.
- Secrets key separation/migration/recovery is unresolved.
- Background queue coexistence/version strategy needs an Action Scheduler spike.
- Free/Pro update-order/bootstrap compatibility needs a concrete protocol proof.

### Medium
- Untitled UI licensing must be checked component-by-component; only explicitly MIT components are safe without a paid-license redistribution review.
- Vite vs WordPress scripts/externalization needs a spike to prevent duplicate React/WordPress runtime.
- Page-builder adapter support versions need a certification matrix.
- 30+ backup provider catalog is a target list, not implementation commitment; protocol adapters should be proved before expansion.
- Multisite semantics differ substantially across destructive/data modules and must be designed per module.

## Next recommended action

Remain in **Phase 0**.

Perform only architecture evidence spikes required to resolve the blocker ADRs, in this order:

1. **Compatibility + toolchain spike** — WP 6.9/current WP × candidate PHP versions; Vite vs `@wordpress/scripts`; React/WordPress externalization; bundle/asset manifest.
2. **Definition storage design/benchmark** — concrete schema, revisions, dependency edges, multisite scope, 10k–100k synthetic benchmark.
3. **Job engine spike** — Action Scheduler coexistence, retries, idempotency, system cron/WP-CLI runner.
4. **Secrets threat model/prototype** — key separation, migration/salt rotation/recovery; no production claims.
5. **Free↔Pro bootstrap compatibility spike** — mismatched versions fail safely.
6. Lock CI matrix based on accepted compatibility/toolchain decisions.
7. Update ADR statuses + this checkpoint.
8. Only then begin **Phase 1 — platform kernel + Free CPT/Taxonomy**.

## Resume instruction

Any future AI/engineer must read `AGENTS.md`, this checkpoint, the relevant master docs and ADRs before changing code. Do not treat Proposed ADRs as Accepted merely because they are recommended here.
