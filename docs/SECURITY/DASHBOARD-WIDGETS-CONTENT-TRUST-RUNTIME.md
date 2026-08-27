# WPEssential — Dashboard Widgets Content Trust & Runtime Model

Status: **Phase 0 security/runtime architecture / no implementation authorized**  
Related: Dashboard Widgets Manager Exhaustive Spec, Component Blueprint ADR-0035, Safe HTTP ADR-0040, ADR-0014.

## Core principle

wp-admin Dashboard content executes in a high-trust administrative origin. WPEssential therefore treats widget content as privileged UI and does not allow arbitrary PHP/JavaScript/unsandboxed remote HTML as a normal no-code widget source.

## Runtime model

`Dashboard Widget Definition → Compiled Widget Descriptor → server visibility Policy → trusted content renderer → WordPress Dashboard adapter`

A widget definition contains:
- stable UUID/key;
- title;
- status;
- widget/content type;
- visibility Policy;
- placement/default priority;
- content source/reference;
- refresh/cache policy;
- empty/error behavior;
- asset dependencies;
- revision/dependency metadata.

## Supported content-source classes

### 1. WPE Component Blueprint
Preferred flexible source.

Can render:
- text/heading;
- media/banner;
- buttons/links;
- stats/cards;
- Listing/query summaries;
- notices/CTA;
- bounded interactive registered components.

Uses shared server renderer/policy/assets.

### 2. Saved Listing / Query summary
Server-side Query with bounded result/count; no N+1/unbounded admin query.

### 3. Registered WordPress server-rendered block
Only registered/allowlisted blocks whose server render and capability expectations are compatible with wp-admin widget context.

### 4. Shortcode
Advanced source.

Rules:
- registered/allowlisted shortcode selector where possible;
- server-side execution under current user context;
- bounded output/time diagnostics;
- output sanitized according to trusted-source class;
- shortcode does not gain capabilities merely because Dashboard rendered it.

Unknown arbitrary shortcode text can be disabled by default or Developer capability only.

### 5. Sanitized rich HTML/message
Strict allowlist through WordPress-safe sanitization profile.

No script/event handlers/unsafe iframe/style injection.

### 6. Banner/announcement
Structured first-class type:
- title/text;
- media/image;
- CTA label/URL;
- notice severity/style token;
- optional dismiss behavior;
- start/end schedule;
- audience Policy.

No raw HTML needed for ordinary banners.

### 7. Remote content adapter
Remote data is fetched server-side through ADR-0040 Safe HTTP/Connections, normalized into a registered schema and rendered by WPE components.

Remote response is **data**, not trusted wp-admin HTML/JS by default.

## Remote content

Never inject arbitrary fetched remote HTML into wp-admin because a remote compromise would become administrator-origin XSS.

Remote adapter defines:
- trusted connection/provider;
- endpoint allowlist/template;
- response schema;
- timeout/size/cache;
- sanitization/normalization;
- renderer mapping;
- failure/stale behavior.

Secrets remain server-side.

## Iframe/embed policy

Ordinary arbitrary iframe is OFF by default.

A future/advanced Iframe Widget requires a **registered trusted embed profile** declaring:
- exact allowed origins/host patterns;
- HTTPS requirement;
- path policy;
- sandbox tokens;
- `allow` permissions;
- referrer policy;
- sizing;
- navigation/top-window policy;
- CSP/frame compatibility;
- authentication/token strategy with no secret in source URL where avoidable.

Default sandbox is restrictive. Capabilities such as `allow-scripts`, `allow-same-origin`, forms/downloads/popups are added only when the provider adapter justifies them.

Never combine `allow-scripts` + `allow-same-origin` for arbitrary same-origin/untrusted embed in a way that defeats sandbox intent.

WPE does not advertise “paste any URL iframe into wp-admin.”

## Links / CTA

Destination classes:
- wp-admin screen;
- WPE screen;
- frontend route;
- approved external HTTPS URL;
- registered action/Ability.

External links use safe rel/target behavior. Dynamic URL values are validated by declared URL schema.

Destructive action button invokes typed Ability with nonce/capability/confirmation; link visibility alone is not authorization.

## Visibility / audience

Widget visibility server-evaluated by:
- role/capability;
- specific user;
- Membership/Entitlement where relevant;
- site/network scope;
- Condition/Policy;
- schedule.

If actor cannot view source data, widget is not allowed to fetch/render it then hide client-side.

## Placement/order

WPE definition can specify recommended/default dashboard region/order.

WordPress/user-specific Dashboard layout/customization may also exist. Product must distinguish:
- shared definition order;
- user-specific preference;
- admin-forced/pinned widget only when explicitly supported.

Do not overwrite unrelated user Dashboard layout metadata globally without explicit action.

## Dismissible widgets

Dismiss state is per user by default, keyed by widget UUID + relevant published revision/dismiss generation.

Options:
- dismiss disabled;
- dismiss until next published revision;
- dismiss permanently until admin reset;
- optional expiry.

A security-critical mandatory notice is a different notice channel; do not abuse Dashboard widget “undismissable” behavior for everything.

## Refresh/cache

Modes:
- request render;
- bounded server cache;
- async refresh through registered REST/Ability endpoint;
- Job-produced snapshot for expensive metrics.

Cache key varies by all data/policy context. Private/user-specific data is never put into shared public cache.

Remote refresh cannot execute arbitrary browser fetch with provider token.

## Assets

Each widget declares asset dependencies through shared Asset Registry.

Load:
- Dashboard screen only;
- only when widget type/renderer present where feasible;
- no global admin CSS/JS from optional module.

No arbitrary remote JS CDN dependency field.

## Admin XSS boundary

All values from:
- widget Definition;
- remote provider;
- Query data;
- user-entered text;
- shortcode/block output

are treated according to their source trust class and escaped/sanitized at final output context.

`unfiltered_html` is not granted by WPE toggle.

## Failure states

- missing content dependency;
- remote timeout;
- invalid response schema;
- shortcode/block adapter unavailable;
- Policy denied;
- renderer failure;
- iframe profile unsupported/CSP blocked;
- stale cached data.

Widget failure never takes down the entire wp-admin Dashboard; render bounded error state and log safe diagnostic.

## AI exposure

AI may draft/update structured Widget definitions/components under permission.

AI cannot:
- create arbitrary JS/PHP;
- authorize new iframe origins automatically;
- bypass visibility Policy;
- reveal provider secrets.

Publishing a remote/iframe-capable widget is high-risk mutation and not autonomous by default.

## Future executable evidence — NOT AUTHORIZED

- WordPress Dashboard/meta-box registration/order behavior;
- user-specific layout/dismiss state;
- admin XSS payload suite;
- shortcode/block context/capability behavior;
- Component Blueprint rendering;
- Safe HTTP remote response normalization;
- iframe sandbox/CSP/navigation escape tests;
- cache/user isolation;
- multisite/network dashboard;
- asset scoping/performance.

No Dashboard widget hook, iframe or remote renderer has been implemented.