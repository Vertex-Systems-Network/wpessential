# Settings Pages — Native & Market Evidence Matrix V1

Surface: **12 / Settings Pages**  
Issue: **#495**  
Research anchor: **`main @ fad3b2f37bd44a01d77438e71877d7990b4bfe2c`**  
Mode: planning/contract only; `runtime_allowed=false`; `product_parity_allowed=false`.

## Verified primary sources

| Source | Evidence | Seed families informed |
|---|---|---|
| WordPress Settings API | https://developer.wordpress.org/plugins/settings/using-settings-api/ | registered settings, sections, fields and canonical option-backed storage |
| WordPress `add_options_page()` | https://developer.wordpress.org/reference/functions/add_options_page/ | Settings-menu page identity, capability gate and independent callback authorization |
| ACF Options Page | https://www.advancedcustomfields.com/resources/options-page/ | dedicated options-page UX and global option-backed field composition |
| Meta Box Settings Page | https://docs.metabox.io/extensions/mb-settings-page/ | top/submenu settings pages, tabs, sections/boxes, columns and help UI |

## Seed disposition evidence

- `settings.page.identity` — supported by native Settings submenu/page identity and mature market page builders.
- `settings.page.scope` — site scope is native; network/user scope needs separate native storage/API evidence before classification.
- `settings.structure.tabs` — sections are native; tabs/panels are market UX conventions and should remain presentation composition.
- `settings.storage.mode` — option-backed site storage is native; network/user variants require canonical ownership and scope validation.
- `settings.storage.autoload` — expert performance-sensitive candidate; must follow current WordPress option/autoload semantics rather than expose raw storage internals casually.
- `settings.values.inheritance` — WPE effective-state candidate; precedence across defaults/site/network/user must be explicit and deterministic.
- `settings.controls.registry` — should consume the canonical Fields/Control Registry rather than create a private field engine.
- `settings.safety.secrets` — secret material must route to the canonical secret/Vault owner and stay excluded from ordinary portable option exports.

## Remaining gates

This matrix does **not** promote `BANK_REVIEWED`. Network Settings APIs, user-meta scope, autoload semantics, market coverage, portability, secret handling, semantic deduplication and zero-unresolved review still need completion. No runtime implementation is authorized.