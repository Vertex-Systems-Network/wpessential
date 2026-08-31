# Dashboard Widgets — Master Options Bank V1

Status: **surface-local candidate / shared lifecycle promotion pending integrator**
Snapshot: **2026-09-01**
Canonical surface: **10 — `dashboard-widgets`**

## Purpose

Normalize the current Dashboard Widgets discovery space without restarting the already-merged Wave 2 atomic inventory or claiming runtime implementation. The existing product contracts remain authoritative:

- `docs/PRODUCT/56-SURFACE-COMPETITOR-PARITY-MATRIX.md`;
- `docs/PRODUCT/ATOMIC-OPTION-CONTRACTS-WAVE2-EXPERIENCE.md`;
- `docs/ARCHITECTURE/CANONICAL-56-SURFACE-OWNERSHIP-REGISTRY.md`;
- `docs/ARCHITECTURE/CANONICAL-56-SURFACE-DEPENDENCY-RELATION-MATRIX.md`.

The canonical owner is Dashboard Widgets Manager: WordPress Dashboard widget inventory, presets and custom widget definitions. It does not own generic placement or authorization.

## Candidate Bank

This branch adds **125 classified discovery records** in four surface-owned shards:

| Shard | Records | Boundary |
| --- | ---: | --- |
| `dashboard-widgets.json` | 35 | definition, native registration/inventory, dashboard targets, presentation and visible states |
| `dashboard-widgets--content-data-actions.json` | 46 | widget types, typed data references, remote-content safety, refresh/cache and Ability-backed actions |
| `dashboard-widgets--preferences-multisite-portability.json` | 30 | presentation visibility, user preferences, role/network presets, multisite, lifecycle and portability |
| `dashboard-widgets--wpe-exceed.json` | 14 | future exceed/deferred diagnostics and reliability ideas |

All records are already classified; no record is `UNREVIEWED`.

## Native baseline

Current WordPress evidence covers:

- `wp_add_dashboard_widget()` ID, title, render callback, optional control callback, callback arguments, context and priority;
- site and Network Admin dashboard setup hooks;
- default/core widget inventory and `remove_meta_box()` removal;
- Screen Options / hidden meta-box user state;
- per-user reorder and collapse behavior;
- four Dashboard meta-box contexts;
- core nonce/control dispatch behavior;
- native user preference precedence over programmatic default ordering.

The machine-readable native disposition candidate is:
`config/product/options-bank-audits/dashboard-widgets-native-wordpress.json`.

Its status intentionally remains `NATIVE_AUDIT_IN_PROGRESS`: the current shared native-audit smoke contract is Fields-specific and the module worker cannot rewrite shared test wiring.

## Market baseline

Primary current evidence reviewed:

- Ultimate Dashboard: custom dashboard widgets, user/role access, global user-derived order, multisite blueprint/exclusions/subsite override/capability controls;
- WP Adminify: text/HTML, icon, video, shortcode, RSS and Script widget types, position and role targeting;
- White Label CMS: dashboard cleanup, custom welcome/dashboard panel, RSS and builder-template-backed welcome content.

Market-only mechanics are not copied blindly. In particular:

- WP Adminify's raw Script widget is discovery evidence but is `REJECTED_UNSAFE` for Surface 10; approved browser script execution belongs to Surface 50 Safe Script.
- Remote/RSS/iframe transport must use Surface 23 Connections/Safe HTTP contracts; Surface 10 stores references and display policy, not credentials/retry/SSRF engines.
- Query/listing semantics stay with Surfaces 6/9.
- Global wp-admin branding/styling stays with Surface 49 Admin Theme.
- Visibility is presentation targeting, never authorization.

## Lifecycle truth

Current shared truth on the branch base still says Surface 10 is `UNSEEDED / 0`. This branch prepares a **candidate** `BANK_SURFACE_SEEDED` surface but does not edit integrator-owned `config/product/options-bank-progress.json`.

No `MARKET_AUDITED`, `BANK_REVIEWED`, UX-contract, implementation-contract or runtime-parity claim is made.

## Next gate

After integration of shared progress/test/schema requirements and exact-head certification:

1. promote the merged seed truth only if global Bank contracts pass;
2. certify the native audit;
3. materialize a schema-valid Dashboard Widgets market provider matrix;
4. run semantic/ownership duplicate review;
5. close Bank Review;
6. only then derive UX projection and the downstream implementation contract.
