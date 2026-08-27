# WPEssential — Admin Menu Executable Evidence Protocol

Status: **Phase 0 consent-gated protocol / 0 fixtures executed**  
Date: 2026-08-28  
Related: Admin Menu Transformation/Conflict/Safe Mode, Role Manager, Multisite, ADR-0014.

## 1. Purpose

Define the evidence required before Custom Admin Menu Builder can claim safe compatibility with WordPress/core/third-party admin navigation.

The accepted architecture remains:

`WordPress/plugin runtime registration → normalized discovered menu registry → stable WPE transformation rules → capability/recovery validation → transformed presentation → diagnostics`.

Menu presentation never becomes authorization.

## 2. Current WordPress behavior to preserve

Future implementation must compose with current WordPress admin-menu lifecycle and ordering semantics rather than replacing raw global menu arrays as an authoritative clone.

Evidence must separately cover:
- Site Admin;
- Network Admin;
- User Admin only if supported;
- `custom_menu_order` enablement;
- `menu_order` ordering;
- late/early third-party menu registration;
- submenu registration;
- direct-screen authorization independent of navigation visibility.

Unmentioned menu items must not disappear merely because WPE supplied an ordering rule.

## 3. Transformation classes

### AM-T1 — Rename
Presentation label only unless owning page adapter explicitly supports heading/title change.

### AM-T2 — Reorder
Stable before/after/group/order constraints; unmatched entries preserve deterministic original relative behavior.

### AM-T3 — Hide
Presentation only. Direct URL remains governed by original screen capability/Policy.

### AM-T4 — Move
Only when page/parent semantics remain compatible; otherwise degraded/unsupported.

### AM-T5 — Add WPE page
Explicit capability/Policy + registered renderer.

### AM-T6 — Add link
Safe wp-admin/WPE/frontend/external link resolver; no page capability implied.

### AM-T7 — Group/separator
Presentation only where WordPress model supports.

## 4. Rule conflict model to prove

Fixtures must demonstrate deterministic handling of:
- missing target;
- ambiguous slug/parent match;
- two rules target same label/order/visibility;
- role/user/global precedence;
- another plugin overwrites WPE change later;
- plugin update changes slug/parent;
- Site vs Network same-looking slug;
- WPE recovery-critical entry hidden by rule.

No ambiguous external target gets an arbitrary silent winner.

## 5. Fixture matrix

### AM-01 — Site Admin basic discovery
Core + WPE + third-party menu tree normalized correctly.

### AM-02 — Network Admin discovery
Network tree is separate from site tree.

### AM-03 — User Admin unsupported/supported distinction
No accidental rule leakage.

### AM-04 — Rename core/third-party menu
Only intended presentation changes.

### AM-05 — Reorder with `custom_menu_order`
WPE composes with WordPress ordering correctly.

### AM-06 — Unmentioned menu items
They remain available and preserve relative behavior according to WordPress semantics.

### AM-07 — Multiple ordering plugins
Detect/diagnose overwritten-by-later-hook/conflict rather than claim guaranteed order.

### AM-08 — Late registered plugin menu
Discovered/applied or diagnosed according to chosen hook profile.

### AM-09 — Missing target
Rule ignored/degraded; no nearest-match mutation.

### AM-10 — Ambiguous slug
Block/diagnose.

### AM-11 — Parent slug changed after plugin update
No accidental move/hide of unrelated item.

### AM-12 — Hide menu item
Sidebar item disappears for target audience, but direct URL capability behavior stays unchanged.

### AM-13 — Unauthorized actor direct URL
Still denied by owning screen; menu visibility irrelevant.

### AM-14 — Authorized actor hidden link direct URL
If original screen still allowed, hiding must not falsely claim it is disabled.

### AM-15 — Add WPE page
Explicit capability required; unauthorized direct URL denied.

### AM-16 — External link
HTTPS/scheme validation, safe target/rel; no callback authority.

### AM-17 — Malicious javascript/data URL
Rejected.

### AM-18 — Move incompatible third-party screen
Marked unsupported/degraded instead of forced array surgery.

### AM-19 — Role-specific transformation
Only presentation for intended role/context; authorization unchanged.

### AM-20 — User-specific preference
Cannot reveal Policy-denied item.

### AM-21 — Self-lockout/recovery path
Rule set attempting to hide all WPE recovery navigation from all recovery principals is blocked/warned according to invariant.

### AM-22 — Safe mode constant/recovery mode
Custom transformations skipped; original WordPress/plugin navigation restored without auth bypass.

### AM-23 — Corrupt WPE rule store
Fail-open to original navigation and diagnostics.

### AM-24 — Invalid capability condition
Fails safe, no unauthorized link exposure.

### AM-25 — Pro expiry
Safe deployed transformation follows ADR-0007; editing may lock without removing navigation/data.

### AM-26 — Import with missing target
Imported rule remains disabled/deferred.

### AM-27 — Import ambiguous target
Requires manual resolution.

### AM-28 — Site/Network import mismatch
Cannot bind site rule to network page silently.

### AM-29 — Nested/native depth
WPE does not fabricate unsupported multi-level core sidebar nesting.

### AM-30 — WPE parent invariant
Modules remain under canonical WPE parent unless accepted transformation preserves recovery/IA constraints.

### AM-31 — Third-party capability changes
Menu transformation does not rewrite owning plugin capability requirement.

### AM-32 — Role Manager change while menu rules active
Presentation recomputes from current effective authorization/audience without stale privilege inference.

### AM-33 — 100/500/1000 discovered entries synthetic scale
Transformation remains bounded and deterministic.

### AM-34 — Every-admin-request overhead
Measure server time/memory/no front-end asset load.

### AM-35 — Builder assets
React/editor assets load only on WPE menu-builder screen, not globally across wp-admin.

### AM-36 — Direct navigation bookmarked before rule change
Authorization remains correct; renamed/moved presentation does not create unsafe redirects.

### AM-37 — Plugin deactivation/reactivation
Target missing/deferred then rebinds only to same stable identity semantics.

### AM-38 — Site deletion/Multisite lifecycle
Scoped rule cleanup/retention does not affect other sites/network.

### AM-39 — Same slug in Site and Network Admin
Scope disambiguation prevents cross-context rule application.

### AM-40 — Competing WPE rules same specificity
Explicit priority + deterministic UUID tie-break and diagnostics match paper contract.

## 6. Performance pass gates

Record:
- discovered entry count;
- transformation rule count;
- normalization/match/apply duration;
- memory;
- number of menu hooks/filters invoked by WPE;
- cache use/invalidation if any;
- site/network context.

Runtime should not require full React application or remote/API call on every admin request.

Exact budgets remain executable evidence.

## 7. Security/recovery pass gates

Production profile fails if:
- hiding grants/denies actual screen authority incorrectly;
- a rule can create unauthorized page callback access;
- unsafe external URL schemes are allowed;
- corrupted rules make wp-admin navigation unusable without safe recovery;
- Site rules mutate Network Admin tree or vice versa;
- direct URL authorization changes merely because menu moved/hidden;
- recovery mode bypasses WordPress authentication/capabilities;
- ambiguous third-party identity silently targets wrong screen.

## 8. Required future evidence report

Include:
- WordPress versions/contexts;
- third-party plugin/menu fixtures;
- hook/priority profile;
- AM-01…AM-40 pass/fail;
- performance measurements;
- conflict/recovery results;
- Multisite results;
- unresolved unsupported menu patterns.

## 9. Current state

**AM fixtures executed: 0/40.**

No admin-menu hook/filter/transformation/safe-mode runtime has executed.

## 10. Development gate

Execution requires explicit owner consent under ADR-0014.