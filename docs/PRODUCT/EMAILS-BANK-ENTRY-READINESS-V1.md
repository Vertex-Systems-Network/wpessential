# Emails — Bank Entry Readiness V1

Surface: **20 / Emails**  
Planning issue: **#586**  
Supervisor wave: **#583**  
Exact-main claim anchor: `40a56e1da1ae59c4441176eda59d43eabe0cd8bc`

Status: **BANK ENTRY READY FOR RESEARCH/SEEDING — NOT BANK_REVIEWED — NO OPTION CONTRACT PROMOTION**

This document is planning evidence only. It does not seed/review the Master Options Bank, create schema-valid Atomic Option Contracts, send mail, change sender/provider configuration or authorize runtime implementation.

## Current machine truth

- Surface 20 `emails` is `UNSEEDED` with **0 normalized Master Options Bank records**.
- The atomic planning ledger records Surface 20 as `ATOMIC_INVENTORY_COMPLETE`, which is a pre-contract planning state, not `OPTION_CONTRACT_COMPLETE` or `UX_CONTRACT_COMPLETE`.
- The canonical ownership map assigns email-safe template/render semantics to Surface 20. Notifications Surface 19 owns recipient/event routing; delivery transport/provider credentials remain external connection/adapter concerns.
- Exact-main `frameworks/Modules` has no dedicated Emails runtime module.

The correct next gate is therefore Bank seeding + native/market review, not renderer/provider implementation.

## Existing in-repo source evidence

Primary planning sources already exist:

- `docs/MODULES/EMAILS-BUILDER-EXHAUSTIVE-SPEC.md`;
- `docs/MODULES/OPTION-INVENTORY.md`;
- `docs/PRODUCT/56-SURFACE-COMPETITOR-PARITY-MATRIX.md`;
- `docs/MODULES/CANONICAL-OPTION-OWNERSHIP-INDEX-56-SURFACES.md`.

The exhaustive spec already draws critical boundaries: Email Builder creates **email-safe transactional/event templates**, is not a general webpage builder, does not promise arbitrary frontend-builder markup compatibility, and does not own recipient/event delivery policy.

## Candidate Bank families

A future normalized Bank should independently classify at least:

1. **Template identity/lifecycle** — stable ID/key, name, description, category, draft/published/archived, revisions.
2. **Event/rule binding** — optional Notification/Event refs without recipient ownership leakage.
3. **Metadata** — subject, preheader, purpose/category, locale, HTML/plaintext mode.
4. **Reusable layouts** — shell/header/body slot/footer, revision pin/follow policy, affected-template dependencies.
5. **Branding** — logo, safe color/type/contact/social tokens and organization profile refs.
6. **Email-safe blocks** — section, columns, text, heading, button, image, divider, spacer, lists, bounded tables/repeaters, legal/preferences, dynamic and conditional blocks.
7. **Block presentation** — compatibility-bounded spacing, typography, backgrounds, borders, mobile stacking and safe widths.
8. **Dynamic tokens** — typed allowlisted providers, privacy class, escaping, null fallback and safe formatters.
9. **Conditional content** — shared declarative Condition Engine only; no executable template code.
10. **Localization** — locale variants, default/fallback, RTL/render ownership.
11. **Plaintext** — deterministic auto-generation + optional override.
12. **Responsive/client behavior** — renderer-supported controls only, with explicit client-compatibility limits.
13. **WordPress email overrides** — stable adapter-registry events, template selection, restore default and partial/unsupported state.
14. **Third-party overrides** — certified adapters with version/schema/fallback contracts.
15. **Sender profiles** — safe display/reference only; credentials and verification truth external.
16. **Attachments** — approved media/document/event refs, count/size/MIME and private-file exposure policy.
17. **Tracking/preferences block** — privacy-sensitive tracking off-by-default candidate; category/preferences links where appropriate.
18. **Preview/test** — desktop/mobile/plaintext/source/context inspector, preflight and explicitly marked test send.
19. **Diagnostics** — size/unsupported block/token/dependency/sender/renderer health.
20. **Permissions/Abilities** — read/draft/publish/layout/branding/override/source/test/export/import boundaries.
21. **Portability** — secret-free schema, layout/brand/token/locale dependencies, conflict preview.
22. **Performance** — compile/cache by revision, bounded repeaters/queries, async send handoff.

## Native WordPress audit required before Bank review

The native audit must explicitly classify current WordPress email mechanisms rather than treating global `wp_mail()` interception as a safe product contract. At minimum inspect:

- core transactional/account/comment/admin notification APIs and stable hooks/filters;
- `wp_mail()` and PHPMailer integration points only as transport/output primitives, not as a generic semantic event catalog;
- current WordPress email-change/password-reset/new-user flows;
- multisite/network email events and site/network ownership;
- escaping/header injection requirements;
- native media/file access constraints for attachments;
- core locale switching behavior for user-facing mail;
- fallback semantics when a stable override adapter is unavailable.

Any core email override must map through a reviewed event adapter. Unsupported or partial native events should stay explicitly Partial/Unsupported rather than being captured via fragile string interception.

## Market audit required before Bank review

A future market audit should compare current specialist email/template and transactional-email products for:

- email-safe block/render breadth;
- reusable layouts/branding;
- WordPress/WooCommerce-like event adapters;
- dynamic data/token safety;
- localization/plaintext;
- responsive/client compatibility strategy;
- test/preview/client testing;
- sender/provider health;
- revision/export/import;
- privacy/tracking/preferences support;
- diagnostics and deliverability-evidence boundaries.

Market features are benchmarks, not automatic acceptance criteria. Frontend-builder freedom, arbitrary HTML/script execution, secret exposure and false deliverability scoring must be rejected or bounded.

## Canonical ownership decisions

| Concern | Canonical owner |
|---|---|
| Email template structure, email-safe renderer, reusable layouts, branding, subject/preheader/plaintext, locale variants | **Surface 20 Emails** |
| Notification event/recipient/channel routing and logical delivery occurrence | **Surface 19 Notifications** |
| Connection credentials/OAuth/provider transport/Safe HTTP/webhook semantics | **Surface 23 Connections/Webhooks or certified transport adapter** |
| Query-backed data collections | **Surface 6 Query**, consumed under delivery Policy |
| Shared conditions | Shared Condition/Decision owner, consumed declaratively |
| Media/files | Owning media/document/resource surface; Email stores refs only |
| Generic import/export package orchestration | **Surface 26 Import/Export** |

## Rejected-unsafe / bounded candidates

The Bank should reject or tightly bound:

- arbitrary frontend Elementor/WPBakery/Gutenberg widget markup as email-safe content;
- script, iframe, browser form, autoplay video, canvas or arbitrary untrusted HTML;
- raw PHP/template-language execution;
- arbitrary callback/class names or formatters;
- password/API/OAuth/secret tokens or raw protected user meta;
- generic plugin-wide email string interception;
- sender credentials inside templates or exports;
- private/protected media exposure without explicit authorization design;
- unbounded Query/repeater rows or attachment bytes;
- automatic tracking without explicit site policy;
- claiming universal client pixel parity or universal deliverability scores.

## Readiness decision

Surface 20 has enough in-repo planning material to begin a disciplined Master Options Bank seeding/native/market review, but exact-main `UNSEEDED / 0` blocks canonical option-contract and UX lifecycle promotion.

Next gate:

1. normalize Bank records;
2. complete native WordPress email/event audit;
3. complete market/specialist audit;
4. resolve semantic duplicates, ownership and unsafe/deferred entries;
5. promote Bank only with zero unresolved review items;
6. derive schema-valid Atomic Option Contracts;
7. re-review the provisional UX before runtime authorization.
