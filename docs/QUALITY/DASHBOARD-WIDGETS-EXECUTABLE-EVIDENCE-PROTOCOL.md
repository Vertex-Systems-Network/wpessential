# WPEssential — Dashboard Widgets Executable Evidence Protocol

Status: **Phase 0 consent-gated protocol / 0 fixtures executed**  
Date: 2026-08-28  
Related: Dashboard Widgets Content Trust Runtime, Component Blueprint, Safe HTTP, Multisite, ADR-0014.

## 1. Purpose

Define the evidence required before WPEssential can claim Dashboard Widgets are safe, performant and compatible with WordPress site/network Dashboard behavior.

The existing architecture remains:

`Widget Definition → compiled descriptor → server visibility Policy → trusted renderer → WordPress Dashboard adapter`.

No Dashboard hook/widget/remote request is authorized by this protocol.

## 2. WordPress adapter surfaces to certify

Future runtime must distinguish:
- Site Dashboard registration through current WordPress Dashboard setup lifecycle;
- Network Dashboard registration through its network-specific lifecycle;
- User Admin Dashboard only if WPE explicitly supports/certifies it;
- widget contexts/priority recognized by WordPress;
- user-specific metabox/layout state;
- screen options/hidden state;
- WPE shared Definition order vs native/user order.

WPE must not assume one Dashboard hook covers every admin context.

## 3. Content-source profiles

### DW-S1 — Component Blueprint
Preferred source. Must prove output escaping, asset scoping, actions and access Policy.

### DW-S2 — Query/Listing summary
Must prove bounded Query, no N+1, truthful counts and authorization.

### DW-S3 — registered server-rendered block
Must prove block availability, render context and output trust class.

### DW-S4 — registered shortcode
Advanced. Must prove bounded/side-effect-safe Dashboard mode and sanitized output.

### DW-S5 — structured rich message/banner
Must prove HTML sanitization/URL/media rules.

### DW-S6 — remote structured data
Must use Safe HTTP/Connection adapter and schema normalization. Remote HTML/JS is not trusted admin content.

### DW-S7 — trusted iframe profile
Advanced/high-risk. Must prove exact origin, sandbox/CSP/referrer/navigation/token behavior.

## 4. Refresh/cache profiles

### DW-R0 — request render
No persistent widget data cache.

### DW-R1 — bounded server cache
Key includes widget revision + site/network scope + audience/access generation + relevant source generations.

### DW-R2 — async refresh
Uses a registered authorized endpoint/Ability; browser never holds provider secrets.

### DW-R3 — Job-produced snapshot
Expensive metrics calculated asynchronously; widget reads last verified snapshot + timestamp/state.

Stale data policy is explicit per widget. Stale authorization-sensitive data cannot expose previously allowed content after access revocation.

## 5. Dismiss/layout state

Dismiss and placement are separate state classes.

Required semantics:
- per-user dismiss by default;
- widget UUID + dismiss generation/revision binding;
- optional expiry/reset;
- admin shared order does not blindly overwrite unrelated user layout state;
- mandatory security notices use a separate notice mechanism rather than abusing widget undismissability.

## 6. Fixture matrix

### DW-01 — Site Dashboard registration
Widget appears only in intended Site Dashboard context.

### DW-02 — Network Dashboard registration
Network-scoped widget uses network context/Policy and does not leak to ordinary Site Dashboard.

### DW-03 — Unsupported User Admin Dashboard
If not certified, widget does not accidentally register there.

### DW-04 — Context/priority mapping
Normal/side/other supported contexts and priority behavior match current WordPress semantics.

### DW-05 — Two WPE widgets stable order
Published order deterministic while preserving native/user constraints.

### DW-06 — Core/third-party widgets coexist
WPE registration does not remove/duplicate unrelated dashboard widgets.

### DW-07 — Per-user hidden/layout state
One user's hide/order state does not become another user's global definition state.

### DW-08 — Dismiss until revision
Publish new configured dismiss generation/revision and prove intended reappearance semantics.

### DW-09 — Capability denied
Widget is not fetched/rendered when actor lacks visibility Policy.

### DW-10 — Source-field denied
Widget cannot fetch protected source data then merely hide it client-side.

### DW-11 — Blueprint XSS corpus
Text/URLs/media/component props escape/sanitize by output context.

### DW-12 — Rich HTML XSS corpus
Scripts, event handlers, unsafe schemes/attributes rejected by accepted sanitization profile.

### DW-13 — Remote HTML response
Remote endpoint returns `<script>`/admin-looking markup. Must remain untrusted data/rejected; never injected as admin HTML.

### DW-14 — Remote schema mismatch
Safe degraded state, no fatal Dashboard.

### DW-15 — Remote timeout/5xx
Bounded timeout + stale/error policy; Dashboard remains responsive.

### DW-16 — Redirect to internal/private SSRF target
Safe HTTP policy rejects according to Connections rules.

### DW-17 — Provider credential leakage
No token in browser JS/HTML/URL/log/support bundle.

### DW-18 — Shortcode capability behavior
Shortcode cannot gain permissions because rendered on Dashboard.

### DW-19 — Shortcode side effect
Redirect/header/form/destructive/expensive shortcode is blocked/degraded if it cannot meet certified list/dashboard safe mode.

### DW-20 — Server-rendered block unavailable
Widget degrades safely.

### DW-21 — Query summary 100/10k/100k source
Bounded rows/count; no N+1; budget warnings/Job snapshot where required.

### DW-22 — Relation-heavy summary
Batch relation path, not per-record loops.

### DW-23 — Cache cross-user attack
Private User A result cannot serve User B.

### DW-24 — Cache cross-site attack
Same numeric IDs on Site A/B cannot cross-hit.

### DW-25 — Access revoked while cache warm
New render/refresh cannot expose revoked data under accepted generation rules.

### DW-26 — Async endpoint forged target site
Request `site_id` cannot widen scope.

### DW-27 — Async endpoint CSRF/authorization
Mutation/refresh endpoint requires proper current authority/nonce/auth contract.

### DW-28 — Job snapshot stale/failed
Widget distinguishes last successful data from failed refresh, with timestamp.

### DW-29 — Iframe unregistered origin
Reject publish/render.

### DW-30 — Iframe sandbox escape/navigation
Trusted profile cannot open unintended top navigation/capabilities under configured sandbox.

### DW-31 — Iframe CSP/frame refusal
Safe user-facing degraded state, no weakening CSP to “make it work.”

### DW-32 — Assets only on Dashboard
Optional widget CSS/JS does not load across unrelated wp-admin screens.

### DW-33 — One widget renderer throws/fails
Other Dashboard widgets/page remain usable.

### DW-34 — Widget Definition missing dependency
Diagnostics + safe state.

### DW-35 — Pro expiry
Existing safe deployed widget follows ADR-0007; editor may lock without destroying Definition/data.

### DW-36 — Multisite network aggregate widget
Explicit network capability, bounded site fan-out and no site-to-site data leak.

## 7. Performance evidence

Measure on Dashboard fixtures:
- server render time per widget and total;
- DB query count;
- Query/Relation batch calls;
- remote request count/time;
- cache hits/misses;
- generated HTML bytes;
- JS/CSS bytes loaded;
- memory;
- async refresh duration;
- network aggregate fan-out.

One slow/failed remote widget must not monopolize the complete Dashboard request beyond accepted budget.

## 8. Security pass gates

Fail production profile if:
- arbitrary remote HTML/JS reaches admin origin;
- provider token appears client-side;
- Policy-denied data is fetched/rendered;
- cache crosses user/site/security context;
- iframe accepts arbitrary origin or unsafe sandbox combination;
- lower-privilege actor can publish high-risk remote/iframe widget;
- failed widget can fatal the whole Dashboard;
- source/shortcode can bypass owning capability rules.

## 9. Required future evidence report

Include:
- WordPress versions/admin contexts;
- widget adapter/version;
- DW-01…DW-36 pass/fail;
- source-class certification matrix;
- cache/refresh class results;
- XSS/SSRF/iframe findings;
- performance measurements;
- Multisite results;
- unresolved limitations.

## 10. Current state

**DW fixtures executed: 0/36.**

No WordPress Dashboard hook, widget registration, remote fetch, shortcode/block render, iframe, cache or async endpoint has run.

## 11. Development gate

Execution requires explicit owner consent under ADR-0014.