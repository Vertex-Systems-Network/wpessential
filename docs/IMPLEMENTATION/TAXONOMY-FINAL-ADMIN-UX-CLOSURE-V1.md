# Taxonomy Builder — Final Admin UX Closure V1

Surface: **2 / Taxonomy Builder**  
Parent: **Issue #474**  
Implementation issue: **#629**  
Source audit: **Issue #626 / merged PR #628**

## Scope closed by this slice

This implementation is intentionally limited to the three reviewed V1 admin-UX gaps proven by the exact-main certification audit:

1. **Canonical role-impact preview** — the Taxonomy admin now parses and renders the already-promoted Surface 30-backed `role_impact` read model returned by Taxonomy validation/list responses. The preview is read-only, preserves explicit allow / explicit deny / absent role-entry truth and shows the canonical caveat that role-entry diagnostics are not final user authorization.
2. **Unsaved-change guard** — editor state is compared against a canonical client baseline derived from the existing Taxonomy editor request. Meaningful form changes arm a `beforeunload` guard; successful save, cancel/reset-to-new, canonical edit hydration, and status-driven editor reset establish a new clean baseline. Search/tier-only controls that do not change the collected editor request do not create false dirty state.
3. **Sticky Validate / Save commands** — the existing command area receives a bounded sticky presentation hook. No new mutation path is introduced; Validate and Save continue through the existing AJAX Ability/Policy and revision/CAS flows.

## Ownership and safety invariants

- Surface 30 remains the sole role/capability truth owner.
- Taxonomy does not call `wp_roles()`, `get_role()`, raw role options, or create a peer-private role engine.
- The role-impact preview never grants/revokes roles or capabilities and never claims final effective-user authorization.
- Existing Taxonomy server validation, Ability/Policy, Definition ownership, revision/CAS, provider allowlisting, and migration safety remain unchanged.
- Taxonomy-key migration execution remains separately gated and is not authorized here.
- Generic package orchestration remains outside Surface 2.

## Focused evidence

A packaged browser/accessibility spec covers all three closure behaviors:

- sticky command bar exists and computes to `position: sticky`;
- clean editor baseline becomes dirty after meaningful payload changes;
- dirty editor dispatches a prevented `beforeunload` event;
- successful save restores a clean baseline;
- edit hydration begins clean, a meaningful edit becomes dirty, and Cancel restores clean state;
- validation renders a canonical healthy role-impact preview with the `manage_terms` capability entry plus Allow / explicit deny / absent role-entry summaries;
- the canonical role-impact caveat that role-entry truth is not final user authorization is visible;
- Axe reports zero violations for the WPE-owned Taxonomy root while the final role-impact/sticky UX is visible.

## Lifecycle boundary

This implementation slice **does not** promote `RUNTIME_CERTIFIED` or `PRODUCT_PARITY_CERTIFIED` and does not close parent Issue #474 by itself.

After this slice is accepted on exact main, run a fresh exact-main Surface 2 certification audit. Only that later audit may determine whether the reviewed V1 runtime certification threshold is finally satisfied.
