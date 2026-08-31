# ADR-0194 — Access, Admin Experience, Media Performance & Safe Code Market Expansion Scope

Status: **Accepted planning architecture / evidence pending / no development authorization**  
Date: 2026-08-29

## Context

The owner requested a combined competitive audit of Members, WP-Members, User Role Editor, Admin Color Schemes, Admin Color Schemer, Image Prioritizer, Auto Sizes / Enhanced Responsive Images and Insert Headers and Footers / WPCode-style functionality.

The audit found three cases where existing WPE surfaces should be expanded rather than duplicated, and two genuinely missing reusable user-facing primitives.

## Decision 1 — Existing-surface expansions

Accept:
- ADR-0189 — Membership Competitive Parity Expansion on Surface 15;
- ADR-0190 — Role & Capability Competitive Parity Expansion on Surface 30;
- ADR-0192 — Media Performance / Responsive Delivery Expansion on Surface 28.

These expansions do not add denominator rows.

## Decision 2 — New user-facing surfaces

Accept:
- ADR-0191 — **Surface 49: Admin Theme, Branding & Experience Manager**;
- ADR-0193 — **Surface 50: Safe Script, Tag & Code Injection Manager**.

Therefore the canonical planned surface denominator becomes:
- original pre-expansion scope: 31;
- universal foundations added by ADR-0177: +12 → 43;
- market expansion ADR-0183…0187: +5 → 48;
- current access/admin/code expansion: +2 → **50 surfaces**.

Historical 31/31, 43/43 and 48/48 milestones remain true for their earlier scopes.

Implementation authorization becomes **0/50**. No authorization is implied by this scope acceptance.

## Decision 3 — Multisite and AI Prompt coverage

Accept `docs/MODULES/ACCESS-ADMIN-MEDIA-CODE-MULTISITE-AI-ADDENDUM.md`.

Product-level mappings now cover:
- **50/50 logical Multisite scope**;
- **50/50 module-wide AI Prompt contract**.

These are product mappings only; runtime Multisite and AI/MCP certifications remain zero/unexecuted.

## Decision 4 — Evidence

Accept `docs/QUALITY/ACCESS-ADMIN-MEDIA-CODE-MARKET-EVIDENCE-MASTER-PLAN.md`.

New/supplemental evidence counters:
- MPR **0/176** — Membership parity;
- RPR **0/176** — Role parity;
- ATM **0/176** — Admin Theme;
- MDP **0/176** — Media Performance;
- STM **0/176** — Safe Script/Tag.

Existing MBR/MB-F/PC-F, RA and WM protocols remain separately authoritative and are not promoted by this ADR.

## Decision 5 — Product differentiation

WPE intentionally exceeds simple competitor parity through shared platform contracts:
- Membership uses Enrollment/Entitlement/Policy rather than roles as the canonical access engine;
- Role management stays native-WordPress authoritative with target-role hierarchy, recovery and explainability;
- Admin theming is semantic-token/version adaptive rather than only a color-picker stylesheet;
- media performance detects Core ownership and can use privacy-aware field evidence rather than blindly forcing priority/lazy heuristics;
- custom browser code is CSP/consent/environment/version governed and never becomes a PHP/eval console.

## Decision 6 — Work coordination

Owner-requested interrupt planning work is complete:
- WP83 — source/market audit — DONE;
- WP84 — Membership parity — DONE;
- WP85 — Role parity — DONE;
- WP86 — Admin Theme/Branding — DONE;
- WP87 — Media Performance/Delivery — DONE;
- WP88 — Safe Script/Tag — DONE;
- WP89 — consolidated scope/governance synchronization — **DONE**.

Canonical Checkpoint, Work Ledger, Option Maturity, Readiness, Open Decisions, ADR index, Approval/Consent summaries, README and Draft PR are synchronized to the 50-surface state.

After this interrupt, resume the pre-existing current planning package:
**WP65 — F03 Search & Indexing detailed executable-evidence specification**.

Reserved WP66…WP74 retain their original F04→WooCommerce Adapter meanings and are not reused.

## Development gate

No WordPress registration/login flow, role mutation/recovery, admin theme, RUM/media optimization, code/tag injection, provider call, migration, build, test, benchmark or runtime execution is authorized.

Project execution remains `PLANNER_ONLY` until explicit owner development consent under ADR-0014.