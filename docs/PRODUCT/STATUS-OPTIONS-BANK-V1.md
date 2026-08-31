# Status Options Bank — Candidate Seed V1

Snapshot: 2026-09-01  
Surface: 5 — `status`  
Work mode: **SURFACE_WORK**  
Branch: `planning/master-options-bank-status-v1`  
Synced repository base: `923197edd27ac96a8e7cccc145ab6f139f93b6b9`

Candidate record count: **129**

## Lifecycle truth

This branch is a **surface-local candidate**, not a shared lifecycle promotion.

Canonical shared progress has not yet been reconciled to this candidate by the designated integrator. This worker does not edit shared/global progress, README/STATUS rollups, Composer wiring, generic smoke aggregation, or the cross-surface semantic registry. The candidate shards use the canonical Bank schema so the integrator can certify them without re-transcribing discovery, but lifecycle promotion remains exact-head gated and serialized.

| Gate | Candidate state |
| --- | --- |
| Repository/current-state audit | complete |
| Current WordPress/native research | complete for candidate |
| Current market/provider research | complete for candidate |
| Candidate Options Bank | 129 classified records / 129 unique option paths |
| Ownership / duplicate resolution | complete for candidate |
| Native Audit | `NATIVE_AUDIT_IN_PROGRESS` — 35 dispositions, zero unresolved research; surface validator prepared, shared execution/promotion pending |
| Market Audit | `MARKET_AUDIT_IN_PROGRESS` — nine capability families, zero unresolved research; surface validator prepared, ordered shared execution/promotion pending |
| Bank Review | `REVIEW_BLOCKED` — 129 records, zero Status semantic relationships, zero unreviewed/deferred records, four explicit safety rejections; two certification gates unresolved |
| UX projection | **not started**; `BANK_REVIEWED` prerequisite not met |
| Implementation contract | **not started**; `BANK_REVIEWED` prerequisite not met |

Surface-local executable gates now prepared:

- `tests/Smoke/options-bank-status-native-audit-contract.php`;
- `tests/Smoke/options-bank-status-market-audit-contract.php`;
- `tests/Smoke/options-bank-status-review-contract.php`.

The formal review candidate is `config/product/options-bank-reviews/status-bank-review-v1.json`. It must remain `REVIEW_BLOCKED` until native and market certification plus canonical progress agree on the same exact head.

## Current repository audit

Repository truth establishes:

- Surface 5 is the canonical `status` surface at `/content-schema/status`.
- Shared ownership says Surface 5 owns **state/status transitions**. Consumers may request transitions; they may not bypass Status and mutate protected state directly.
- Surface 17 Forms / Workflow owns orchestration around transition requests/events, not the state mutation itself.
- The dependency matrix gives Status no peer-hard surface dependency. Soft interactions include CPT, Tables, Forms/Workflow, Notifications, Emails, Solutions and shared systems.
- No existing Status branch, Status-specific PR, or approved Status work package/task ID was present when this branch was created.
- No existing Status runtime implementation was found by repository search; this work therefore remains planning/research and does not claim runtime parity.

## Native research findings

WordPress core exposes a real status registry through `register_post_status()`, including:

- key + label and count labels;
- `public`, `internal`, `protected`, `private`;
- `publicly_queryable`, `exclude_from_search`;
- `show_in_admin_all_list`, `show_in_admin_status_list`;
- `date_floating`;
- Core-derived defaults when those flags are omitted.

Important native constraints and behavior captured in the candidate Bank:

- registration must not occur before `init`;
- `_builtin` is Core-only and is explicitly rejected as an authorable WPE option;
- Core sanitizes the status key;
- the Core database schema stores `wp_posts.post_status` as `varchar(20)`, so WPE must reject non-persistable keys even though registration itself can accept a longer string;
- `register_post_status()` is independent from post-type applicability, so per-post-type applicability and editor exposure are WPE/market-layer contracts rather than invented native registration arguments;
- status transition hooks can also fire during same-status updates, so WPE must distinguish a real state change from a content update;
- Core REST status discovery is read-oriented through `/wp/v2/statuses`; there is no native `show_in_rest` status registration argument;
- future publication, trash/untrash, and attachment `inherit` have special Core lifecycle semantics and cannot be flattened into a generic custom-state switch.

Primary current evidence:
- https://developer.wordpress.org/reference/functions/register_post_status/
- https://developer.wordpress.org/reference/functions/wp_get_db_schema/
- https://developer.wordpress.org/reference/hooks/transition_post_status/
- https://developer.wordpress.org/reference/classes/wp_rest_post_statuses_controller/
- https://developer.wordpress.org/reference/classes/wp_rest_posts_controller/handle_status_param/
- https://developer.wordpress.org/reference/functions/create_initial_post_types/
- https://developer.wordpress.org/reference/functions/check_and_publish_future_post/
- https://developer.wordpress.org/reference/functions/wp_trash_post/
- https://developer.wordpress.org/reference/functions/wp_untrash_post/

## Current market research findings

Current generic/editorial status providers:

- **PublishPress Statuses**: custom publication/visibility statuses, labels, color/icon, post-type applicability, role restrictions and richer capability integrations.
- **Extended Post Status**: generic custom status management plus editor/quick-edit exposure and native status settings.
- **Edit Flow**: workflow-stage custom statuses and editorial integration.

Specialist evidence:

- **Oasis Workflow** demonstrates workflow-linked custom statuses and process history. Workflow routing/inbox ownership remains Surface 17; audit persistence remains Ledger-owned.
- **PublishPress Future** demonstrates scheduled moves to custom statuses. Status owns the requested target transition; Cron/Jobs own timed execution.
- **WooCommerce Order Status Manager** demonstrates next-status flows, bulk actions, icons, status-triggered emails and safe reassignment on deletion. Paid/payment/report/customer semantics are Woo-domain adapter concerns, not generic Status options.
- **JetEngine / JetFormBuilder** currently provide strong status *consumer* evidence (query filters, dynamic visibility, form mutation and expiration). Current primary evidence did not demonstrate a generic JetEngine status-definition registry, so the candidate records it as a provider-consumer mapping rather than inventing one.

Primary evidence:
- https://publishpress.com/statuses/
- https://publishpress.com/knowledge-base/statuses-options/
- https://wordpress.org/plugins/publishpress-statuses/
- https://wordpress.org/plugins/extended-post-status/
- https://wordpress.org/plugins/edit-flow/
- https://wordpress.org/plugins/oasis-workflow/
- https://wordpress.org/plugins/post-expirator/
- https://woocommerce.com/document/woocommerce-order-status-manager/
- https://crocoblock.com/knowledge-base/jetengine/post-expiration-period-add-on/
- https://crocoblock.com/knowledge-base/articles/query-builder-settings-overview-crocobuilder/

## Candidate shard layout

| Shard | Records | Scope |
| --- | ---: | --- |
| `status.json` | 29 | definition, labels, native visibility/registration, presentation, applicability, defaults |
| `status--editor-transitions-policy.json` | 33 | editor exposure, transition graph, permissions, integrity |
| `status--scheduling-api-lifecycle.json` | 34 | scheduling, events, REST/Abilities, query, trash/retirement/migration |
| `status--portability-audit-compatibility-exceed.json` | 33 | portability, multisite, audit emission, diagnostics, UX exceed, adapters, safety rejects |
| **Total** | **129** | all records classified; zero `UNREVIEWED` |

## Ownership and duplicate resolution

### Status-owned

- status definition identity and native registration projection;
- effective visibility/classification settings;
- allowed post types and safe default-status policy;
- editor availability of Status-owned transitions;
- allowed from/to transition graph;
- transition authorization requirements and owner-side guard;
- canonical mutation/transition validation;
- native/Core lifecycle bridges (`future`, trash/untrash, attachment inherit);
- canonical transition event semantics;
- status definition import/export, retirement and migration;
- transition audit *emission* semantics and Status-specific diagnostics.

### Delegated / consumer-owned

- **Forms / Workflow (17):** orchestration, routing, assignments, workflow branches/steps around transition requests.
- **Cron (18):** schedule execution/delivery; Status validates the requested transition.
- **Notifications (19) / Emails (20):** recipient, template, channel and delivery policy.
- **Query (6):** query composition; Status exposes registered/effective status predicates.
- **Ledger (36):** durable audit/history storage and retention.
- **Analytics (33):** reports/KPIs based on status.
- **Builder Widgets / Listings:** presentation conditions consuming a status predicate.
- **WooCommerce adapter/domain:** paid/requires-payment/order editability/report/customer semantics.

### Explicit no-bypass rules

- workflow/private code may not write protected state directly;
- direct SQL status mutation is rejected;
- arbitrary executable transition callback text is rejected;
- plugins may not author Core `_builtin` status semantics.

## Formal Bank Review findings

The current semantic registry contains six relationships, all owned by Fields; Status has **zero** alias/effective-derivation entries. The Status review therefore does not invent semantic registry rows solely to satisfy a review ceremony.

The blocked review also verifies the candidate policy posture:

- zero `UNREVIEWED` records;
- zero `DEFERRED` records;
- exactly four explicit `REJECTED_UNSAFE` records;
- all WPE-exceed adoption is future-only and `P1_EXCEED`;
- native research unresolved count is zero;
- market research unresolved count is zero;
- no record-count inflation occurred after market evidence was attached.

The review remains blocked because research completeness is not equivalent to certification. Native and market gates must execute through the designated shared integration lane, in order, with fresh exact-head CI before the review can be changed to `BANK_REVIEWED`.

## Candidate design direction

Essential authoring should expose: label, key, classification, post types, color/icon, editor visibility, and common transition restrictions.

Advanced authoring should expose: native visibility flags, transition graph, capability/role policy, scheduling/expiration target, REST/Ability policy, retirement/migration and audit emission.

Expert/diagnostic views should expose: effective Core defaults, native compatibility details, transition simulator, lockout explanation, unreachable/dead-end states, impact preview and provider mappings.

The UX must distinguish **authored configuration** from **effective native values**. Derived Core defaults and downstream consumer integrations are diagnostics/references, not duplicate controls.

## What this candidate does not certify

It does not certify `BANK_SURFACE_SEEDED`, `NATIVE_AUDITED`, `MARKET_AUDITED` or `BANK_REVIEWED` in shared repository truth. It does not create runtime registration, mutation APIs, UI, storage migrations or production behavior.

See `STATUS-INTEGRATION-REQUIREMENTS-V1.md` for the only safe promotion path.
