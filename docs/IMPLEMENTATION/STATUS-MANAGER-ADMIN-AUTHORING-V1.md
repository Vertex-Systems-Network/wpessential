# Status Manager Admin Authoring V1

Issue: #365

This bounded Surface 5 admin slice exposes canonical Status and transition-policy Definition authoring through the existing shared Ability, WordPress Ability bridge, AJAX nonce, Definition repository and compiler contracts.

The certified path provides:

- list/get/save/lifecycle operations for only owner Surface 5 `status` and `status-transition-policy` Definitions;
- optimistic `expected_revision` conflict failure for updates;
- immutable Definition type/identity and immutable authored Status key after creation;
- server-side validation through `StatusDefinitionCompiler` and `StatusTransitionPolicyDefinitionCompiler`, including Draft candidates before persistence;
- explicit effective visibility/admin preview from the Status compiler rather than hidden Core defaults;
- canonical ordered transition-edge preview with Core lifecycle-reserved transitions rejected by the existing policy model;
- `manage_options` authorization, shared WordPress Ability exposure and AJAX nonce operations;
- strict admin input schemas with no public site/network/provider/implementation selectors;
- a semantic, escaped, JavaScript-independent fallback authoring shell with labels, fieldsets, error alert/focus target and visible Core lifecycle guidance.

The fallback renderer does not create an alternate mutation path: canonical mutations remain Ability-owned. This slice does not implement workflow routing, notifications, Cron/Jobs, bulk transitions, provider-domain statuses, portability, Event/Audit emission, product parity, deployment or release behavior.
