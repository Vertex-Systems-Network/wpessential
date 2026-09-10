# Dashboard Widgets — UX Contract V1

Surface: **10 / Dashboard Widgets**  
Issue: **#493**  
Lifecycle target: planning-only UX contract; no runtime certification.

## Information architecture

The editor uses the shared WPE builder grammar: list screen → definition editor → validation/diagnostics → preview. Default mode is **Essential**, with **Advanced** and **Expert** progressively revealing configuration while preserving hidden stored values.

### Essential

- Widget title and stable key.
- Status: draft/published/disabled/archived.
- Widget type: info, KPI/stat, chart, listing/query, activity, quick links, form/action, diagnostic, announcement, registered provider.
- Dashboard context/area and order.
- Basic width/height.
- Primary capability/visibility rule.
- Content or data source summary.
- Save + Validate actions.

### Advanced

- Role/user/conditional visibility composition.
- Collapsible/dismissible/default-collapsed behavior.
- Density, icon/help link, empty/loading/error states.
- Refresh interval/manual/background refresh.
- Cache TTL/scope, stale state, retry behavior and last-updated display.
- Per-user reorder/hide/dismiss/reset preferences.
- Site/network default inheritance and override diagnostics.

### Expert

- Registered provider selection and provider health.
- Query/Listing/Analytics/Ledger integration references.
- Ability-backed actions, confirmation and audit requirements.
- Import/export conflict and environment-reference diagnostics.
- Performance/query-budget diagnostics.
- Compatibility provider mappings.
- WPE-exceed/deferred options only where their Bank disposition permits exposure.

## Interaction rules

- Hidden Advanced/Expert values remain intact when changing tiers.
- Inherited/default/effective values are visually distinct from explicit overrides.
- Reset field/section/all returns to canonical defaults without silently deleting unrelated user preferences.
- Find Setting searches friendly labels plus canonical option/native names.
- Validation errors include summary + keyboard focus/jump to the exact control.
- Unsaved changes trigger a navigation guard.
- Any action with side effects is Ability/Policy-backed and server-authoritative.

## Preview and diagnostics

Read-only preview must expose, when available:

- target dashboard area and final ordering;
- effective site/network/user visibility;
- effective refresh/cache policy;
- provider health and missing integration references;
- effective permissions for the current diagnostic actor without leaking private target-user data;
- performance warnings and stale data state.

Preview must never execute the configured action merely to demonstrate it.

## Accessibility

Before runtime certification the complete editor requires packaged browser + axe evidence for tier navigation, keyboard operation, focus management, form labels, validation announcements, dismiss/reorder controls and non-color-only statuses.

## Multisite

Site and network defaults must be shown separately. Network policy cannot silently overwrite a site/user explicit value without displaying effective precedence. Super Admin visibility is not equivalent to ordinary capability grants.

## Security

- UI visibility is not authorization.
- No arbitrary PHP/callback/class text.
- Provider identities are registered/allowlisted.
- Action buttons require canonical Ability/Policy checks and nonce/CSRF protection through shared platform invocation.
- Widget output uses context-correct escaping/sanitization.

## Exit

This UX contract becomes promotable only after the 123-record machine option contract maps every reviewed Bank record with `missing=0` and `unclassified=0`. Runtime implementation remains a later explicit gate.
