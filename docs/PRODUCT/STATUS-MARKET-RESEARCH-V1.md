# Status Market Research / Audit — Candidate V1

Snapshot: 2026-09-01  
Surface: 5 — `status`  
Candidate status: **`MARKET_AUDIT_IN_PROGRESS`**  
Research unresolved: **0**

## Sequencing note

Market research is complete enough to populate and disposition the candidate Bank, but formal `MARKET_AUDITED` certification must not bypass the still-uncertified Status Native Audit gate. The machine-readable file therefore remains `MARKET_AUDIT_IN_PROGRESS`.

## Provider coverage

### Primary generic/editorial providers

1. **PublishPress Statuses** — definition/labels; colors/icons; publication and visibility statuses; post-type applicability; role restrictions and advanced status capabilities via the PublishPress permissions/capabilities ecosystem; editor workflow labels and alternate workflow presentation.
2. **Extended Post Status** — generic status creation/editing; native/system status settings; block/classic/quick-edit integration; broad post-type availability.
3. **Edit Flow** — custom editorial status stages; admin/editor workflow integration; status-driven editorial collaboration.

### Specialists / consumers

- **Oasis Workflow** — workflow-linked custom statuses, role routing and process history.
- **PublishPress Future** — scheduled changes to custom statuses.
- **WooCommerce Order Status Manager** — domain-specific status ordering, next statuses, bulk actions, presentation, emails and safe deletion/reassignment.
- **JetEngine / JetFormBuilder** — status selection during form mutation, status query filters, dynamic visibility predicates and expiration-to-status behavior.

## Critical market ownership decisions

### Workflow is not Status
Workflow routing, assignments, inboxes, branches and step orchestration remain Surface 17. Surface 5 owns the status definition, allowed transition contract and mutation validation. Workflow requests a transition and reacts to the resulting event.

### Notifications / email are not Status
Market products often bundle status-triggered email. WPE should not copy that ownership. Surface 5 emits a typed transition event; Surfaces 19/20 own recipients, channels, templates and delivery.

### Query / visibility consumers are not Status
JetEngine's status query and dynamic visibility features demonstrate market demand for status predicates, but the actual query/presentation configuration remains with Query/Builder-owned surfaces.

### WooCommerce order semantics are adapter-specific
Paid, requires-payment, analytics inclusion, customer actions and order editability are not generic Status semantics. The Woo adapter may map them onto a generic status transition but owns the commerce meaning.

## Evidence

- https://publishpress.com/statuses/
- https://publishpress.com/knowledge-base/statuses-options/
- https://publishpress.com/knowledge-base/statuses-and-permissions-pro/
- https://publishpress.com/knowledge-base/custom-visibility-statuses/
- https://wordpress.org/plugins/publishpress-statuses/
- https://wordpress.org/plugins/extended-post-status/
- https://wordpress.org/plugins/edit-flow/
- https://wordpress.org/plugins/oasis-workflow/
- https://wordpress.org/plugins/post-expirator/
- https://woocommerce.com/document/woocommerce-order-status-manager/
- https://woocommerce.com/products/woocommerce-order-status-manager/
- https://crocoblock.com/knowledge-base/jetengine/post-expiration-period-add-on/
- https://crocoblock.com/knowledge-base/articles/query-builder-settings-overview-crocobuilder/
- https://crocoblock.com/knowledge-base/features/dynamic-visibility-overview/

Machine-readable candidate: `config/product/options-bank-audits/status-market-ecosystem.json`.
