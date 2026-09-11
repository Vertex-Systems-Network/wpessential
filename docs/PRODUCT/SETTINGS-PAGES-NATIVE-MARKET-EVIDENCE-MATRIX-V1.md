# Settings Pages — Native & Market Evidence Matrix V1

Surface: **12 / Settings Pages**  
Issue: **#495**  
Research anchor: **`main @ 42303a97938f1a80f9c1c9088fa70d40ba359279`**  
Mode: planning/contract only; `runtime_allowed=false`; `product_parity_allowed=false`.

## Verified primary sources

| Source | Evidence | Seed families informed |
|---|---|---|
| WordPress Settings API / `register_setting()` | https://developer.wordpress.org/reference/functions/register_setting/ | registered option identity, data type/default/sanitize/show-in-REST contract |
| WordPress `update_option()` | https://developer.wordpress.org/reference/functions/update_option/ | site option mutation semantics and explicit capability responsibility around writes |
| WordPress `update_site_option()` | https://developer.wordpress.org/reference/functions/update_site_option/ | network-scoped option storage through the network option owner |
| WordPress `update_user_meta()` | https://developer.wordpress.org/reference/functions/update_user_meta/ | user-scoped metadata storage semantics distinct from site/network options |
| WordPress `add_options_page()` | https://developer.wordpress.org/reference/functions/add_options_page/ | Settings-menu page identity, capability gate and independent callback authorization |
| ACF Options Page | https://www.advancedcustomfields.com/resources/options-page/ | dedicated options-page UX and global option-backed field composition |
| Meta Box Settings Page | https://docs.metabox.io/extensions/mb-settings-page/ | top/submenu settings pages, tabs, sections/boxes, columns and help UI |

## Native decisions added in this pass

- Site options, network options and user metadata are distinct persistence scopes. Surface 12 must declare storage scope explicitly instead of treating all settings as one option table.
- `register_setting()` supplies canonical type/default/sanitization metadata for ordinary Settings API values; typed WPE contracts should preserve server-authoritative sanitization rather than rely on UI controls.
- Network settings must map to network option semantics, not silently reuse site options. User preferences/profile-owned values must remain user-scoped and respect the User Profile ownership boundary.
- WordPress option writes do not replace authorization. Page/menu capability and mutation capability/Policy checks remain explicit server gates.
- Secret material is not an ordinary Settings API portability value; Vault references remain the accepted ownership boundary.

## Seed disposition evidence

- `settings.page.identity` — supported by native Settings submenu/page identity and mature market page builders.
- `settings.page.scope` — site, network and user persistence primitives are now evidenced; precedence and UI ownership remain to be normalized.
- `settings.structure.tabs` — sections are native; tabs/panels are market UX conventions and should remain presentation composition.
- `settings.storage.mode` — option/network-option/user-meta modes are valid typed candidates; owner and scope must be explicit.
- `settings.storage.autoload` — expert performance-sensitive candidate; must follow current WordPress option/autoload semantics rather than expose raw storage internals casually.
- `settings.values.inheritance` — WPE effective-state candidate; precedence across defaults/site/network/user must be explicit and deterministic.
- `settings.controls.registry` — should consume the canonical Fields/Control Registry rather than create a private field engine.
- `settings.safety.secrets` — secret material must route to the canonical secret/Vault owner and stay excluded from ordinary portable option exports.

## Remaining gates

This matrix does **not** promote `BANK_REVIEWED`. Autoload/default behavior, Settings API error/help patterns, network-admin capability semantics, user-meta privacy ownership, broader market coverage, portability, secret handling, semantic deduplication and zero-unresolved review still need completion. No runtime implementation is authorized.
