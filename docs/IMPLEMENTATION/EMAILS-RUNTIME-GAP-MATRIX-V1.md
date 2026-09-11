# Emails — Runtime Gap Matrix V1

Surface: **20 / Emails**  
Planning issue: **#586**  
Supervisor wave: **#583**  
Exact-main claim anchor: `40a56e1da1ae59c4441176eda59d43eabe0cd8bc`

Status: **PLANNING GAP MATRIX / RUNTIME NOT AUTHORIZED**

Surface 20 remains `UNSEEDED / 0` in the Master Options Bank and exact main has no dedicated Emails runtime module. This matrix describes future prerequisites and boundaries only.

## Gap matrix

| Area | Exact-main state | Future requirement | Boundary / gate |
|---|---|---|---|
| Master Options Bank | **BLOCKED — UNSEEDED / 0** | Normalize native/market reviewed records. | Product gate before option contracts/runtime. |
| Atomic Option Contract | **PLANNING INVENTORY ONLY** | Schema-valid per-option contracts after Bank review. | No lifecycle promotion from inventory alone. |
| Emails runtime module | **ABSENT** | Canonical template/layout/branding/render owner. | Runtime issue only after product gates. |
| Template persistence/revision/CAS | **ABSENT** | Stable definitions, draft/published/archive, immutable published revision. | Surface 20. |
| Email-safe renderer/schema | **ABSENT** | Structured safe blocks -> deterministic HTML/plaintext output. | No arbitrary frontend DOM/JS/PHP. |
| HTML sanitizer/inliner | **ABSENT** | Allowlisted tags/attributes/CSS with renderer compatibility profile. | Security/client evidence required. |
| Layouts/branding | **ABSENT** | Reusable shell/header/footer and structured brand tokens with dependency impact. | Surface 20. |
| Tokens/data providers | **ABSENT** | Typed allowlisted providers with privacy/escaping/fallback metadata. | Data owners stay authoritative. |
| Conditions | **DEPENDENCY** | Declarative shared conditions. | No raw template language. |
| Query/repeater rendering | **DEPENDENCY** | Bounded results under Policy/performance budget. | Query Surface 6 executes; Email renders. |
| Localization | **ABSENT** | locale variants/fallback and deterministic locale source. | Surface 20 rendering semantics. |
| Plaintext | **ABSENT** | deterministic generation + override/preview. | Surface 20. |
| Responsive/client compatibility | **ABSENT** | renderer-supported mobile controls and compatibility reporting. | Do not promise universal parity. |
| WordPress event override registry | **ABSENT** | stable certified adapters + restore default + partial/unsupported states. | No global fragile interception. |
| Third-party override adapters | **ABSENT/DEFERRED** | versioned semantic adapters with fallback. | Per-adapter evidence. |
| Sender profiles | **DEPENDENCY** | safe reference/display and health. | Credentials/transport owned externally. |
| Test send | **ABSENT** | explicit preflight and test-only evidence. | Real delivery path remains Notification/transport. |
| Attachments | **ABSENT** | authorized refs, limits and private-file safety. | Resource/file owner authorizes. |
| Tracking | **ABSENT** | explicit privacy policy/provider capability, off-by-default candidate. | Never hidden WPE telemetry. |
| Preferences/unsubscribe block | **DEPENDENCY** | signed/approved preference links for optional classes. | Notification/preferences policy owns classification. |
| Diagnostics | **ABSENT** | dependency, render, size, block, token, sender and compatibility health. | Read-only. |
| Delivery logs | **CROSS-SURFACE** | template/revision/render metadata correlated to delivery attempts. | Notification/transport owns attempt state. |
| Import/export | **ABSENT** | secret-free schema + dependency conflict preflight. | Generic package orchestration Surface 26. |
| Multisite | **UNSPECIFIED** | explicit template/layout/brand/sender/override scope. | Must be contracted before implementation. |
| Accessibility | **NO SURFACE UI/RENDERER** | accessible editor and output semantics. | Browser + output evidence later. |
| Compatibility | **NO SURFACE RUNTIME** | WP/PHP plus renderer/client/adapter degradation evidence. | Exact-head compatibility gates. |
| Performance | **NO SURFACE RUNTIME** | compiled revision cache, bounded data, server render, async sends. | Deterministic budgets required. |
| Privacy/security | **NO SURFACE RUNTIME** | token denylist, URL/header safety, attachment auth, secret isolation. | Security tests required. |

## Hard safety requirements

Future code must reject or fail closed on:

- raw PHP/JS/template-language execution;
- arbitrary callback/class/formatter input;
- script, iframe, form, browser widget or untrusted raw HTML blocks;
- header/from/reply-to injection;
- secret-bearing tokens or exports;
- sender credentials inside templates;
- arbitrary global `wp_mail()` interception without semantic adapter;
- private attachment exposure without resource authorization;
- unbounded Query/repeater/attachment output;
- unsafe URL protocols or secret/session values in generic CTA tokens;
- publishing/tracking/test-send actions without server-side Policy.

## Policy / Ability model required later

Separate abilities should cover:

- template list/get;
- create/update/validate draft;
- publish/unpublish/archive;
- layout/brand management;
- preview/render with authorized context;
- generated source view;
- test send;
- WordPress/third-party override configure/restore;
- import/export owner seam.

Sender-profile management remains external. AI exposure should default to read/explain/preview and optionally draft generation; publish/test-send/override mutations are not default AI capabilities.

## Accessibility evidence required later

- keyboard editor and block operations without drag-only dependency;
- semantic editor fieldsets/headings;
- focus after add/move/delete/save/validation;
- linked error summaries;
- heading/alt/contrast warnings;
- plaintext preview;
- rendered-output semantic checks where automatable;
- representative axe checks for Essential/Advanced/Expert/System editor states.

## Multisite evidence required later

Contracts/tests must cover:

- site vs network templates/layouts/brands;
- sender/provider scope;
- network email override policy;
- site/user locale resolution;
- cross-site resource/token access;
- network admin permissions;
- export/import dependency mapping by site/network.

No network behavior should be inferred from Super Admin presentation alone.

## Compatibility evidence required later

- supported WordPress/PHP matrix;
- core override adapters on supported WP versions;
- third-party adapter absence/version mismatch fallback;
- renderer/sanitizer deterministic output;
- email-client compatibility matrix for supported block/style subset;
- missing sender/Notification/Connection dependencies degrade without fatal errors.

Client previews are not certification of every mail client.

## Reliability / performance evidence required later

1. published revisions compile/cache deterministically;
2. render output is stable for the same revision/context;
3. token escaping prevents injection in HTML/text/URL/header contexts;
4. bounded Query/repeater rows prevent N+1/unbounded render growth;
5. large/live sends hand off to Notifications/Job rather than loop in request;
6. sanitizer/inliner behavior is covered by fixtures;
7. plaintext generation remains readable and strips raw HTML artifacts;
8. attachment totals enforce hard caps before send;
9. missing layout/token/provider produces explicit degraded state;
10. WordPress/third-party adapter failure falls back safely where contracted;
11. test send does not mutate live workflow state;
12. full sensitive rendered bodies are not retained indefinitely by default.

## Future implementation order

Only after Bank review + option contracts:

1. Template/Layout definitions + Policy/revision/CAS;
2. structured schema + validator/sanitizer;
3. deterministic HTML/plaintext renderer;
4. token/condition/data-provider read seams;
5. preview/diagnostics/accessibility;
6. branding/layout dependency lifecycle;
7. native WordPress override adapters;
8. sender/Notification integration for explicit test/live routing;
9. portability/multisite/degraded-state closure;
10. exact-head security/compatibility/performance audit.

Provider delivery and bulk notification orchestration must not be pulled into Surface 20.

## Exit decision

Exact main has a detailed Email Builder exhaustive specification and canonical ownership model, but the Master Options Bank is still `UNSEEDED / 0` and no dedicated Emails runtime exists. `runtime_allowed=false` remains correct.

The next valid product action is a separate Bank seeding/native/market review, followed by schema-valid option contracts and UX re-review — not email renderer/provider implementation from this planning lane.
