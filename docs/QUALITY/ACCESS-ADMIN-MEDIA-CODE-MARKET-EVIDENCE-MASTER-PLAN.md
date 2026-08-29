# WPEssential — Access/Admin/Media/Code Market Expansion Evidence Master Plan

Status: **Exact evidence expansion COMPLETE / ADR-0209 / NOT EXECUTED**  
Date: 2026-08-29

## 1. Purpose

Reserve and now record the exact executable-evidence contract for the owner-requested competitor parity/expansion work without executing runtime behavior.

The 16 group ownership below remains fixed. ADR-0207 identified these five namespaces as exact-fixture planning gaps. WP114 has now expanded every namespace below into exact individual fixtures without renumbering or repurposing groups; ADR-0209 accepts those protocols.

All counters remain zero executed.

## 2. Evidence namespaces

| Namespace | Scope | Fixture envelope | Exact status | Executed |
|---|---|---:|---|---:|
| `MPR` | Membership competitive parity / registration / private-site / migration presets | MPR-001…MPR-176 | **176/176 exact / ADR-0209** | 0/176 |
| `RPR` | Role hierarchy / rescue / surface-policy integration / compatibility | RPR-001…RPR-176 | **176/176 exact / ADR-0209** | 0/176 |
| `ATM` | Admin Theme, Branding & Experience Manager | ATM-001…ATM-176 | **176/176 exact / ADR-0209** | 0/176 |
| `MDP` | Media Performance, Responsive Delivery & Field Optimization | MDP-001…MDP-176 | **176/176 exact / ADR-0209** | 0/176 |
| `STM` | Safe Script, Tag & Code Injection Manager | STM-001…STM-176 | **176/176 exact / ADR-0209** | 0/176 |

WP114 total: **880/880 exact individual fixtures documented / 0 executed**.

Canonical exact protocols:
- `MEMBERSHIP-COMPETITIVE-PARITY-EXECUTABLE-EVIDENCE-PROTOCOL.md`
- `ROLE-CAPABILITY-COMPETITIVE-PARITY-EXECUTABLE-EVIDENCE-PROTOCOL.md`
- `ADMIN-THEME-BRANDING-EXECUTABLE-EVIDENCE-PROTOCOL.md`
- `MEDIA-PERFORMANCE-DELIVERY-EXECUTABLE-EVIDENCE-PROTOCOL.md`
- `SAFE-SCRIPT-TAG-CODE-INJECTION-EXECUTABLE-EVIDENCE-PROTOCOL.md`

Existing protocols remain separate:
- Membership core `MBR`, billing `MB-F`, protected files `PC-F`;
- Role runtime `RA`;
- Media derivative/watermark `WM`;
- UI/Build/Assets/Policy/Privacy/Rate/Cache/Versioning evidence.

No supplemental evidence namespace upgrades an existing certification automatically.

## 3. MPR groups — 16 × 11

1. Site lockdown/public-route exclusions
2. Registration Flow identity/schema
3. account creation/native auth boundary
4. email verification
5. admin approval
6. Plan selection/enrollment qualification
7. login/register/profile rendering adapters
8. default restrictions/resource overrides
9. teaser/excerpt safety
10. navigation visibility vs direct authorization
11. messages/dialogs/email composition
12. legacy Members/WP-Members detection and mapping
13. migration dry-run/replay/recovery
14. abuse/privacy/retention
15. Multisite/network/user-identity boundaries
16. scale/regression/coexistence

## 4. RPR groups — 16 × 11

1. Role Administration Policy identity
2. list/assign/remove/edit target-role enforcement
3. Users/Add User/Edit User/bulk parity
4. REST/Ability/Workflow parity
5. rescue token eligibility/generation
6. rescue replay/rate/enumeration/recovery
7. capability provenance/orphan handling
8. role diff/snapshot/rollback
9. admin/menu/widget/editor-feature delegation
10. object-level Policy delegation
11. plugin/form integration boundaries
12. effective capability explain/meta-cap mapping
13. import/export/migration
14. Multisite role template/sync/Super Admin
15. anti-lockout/privilege escalation/concurrency
16. large user/role networks/regression

## 5. ATM groups — 16 × 11

1. Theme definition/version profile
2. native `wp_admin_css_color()` registration
3. WordPress 7.1 `wp-theme` token integration
4. legacy/fallback generated CSS
5. palette/icon/state mapping
6. typography/geometry/density
7. accessibility/contrast/focus
8. user/role/site assignment precedence
9. network assignment/enforcement
10. environment identity
11. branding/admin bar
12. login presentation
13. preview/revision/rollback
14. import/export/lifecycle
15. third-party admin/style/editor conflicts
16. performance/RTL/browser/WP-version regression

## 6. MDP groups — 16 × 11

1. Core capability/version detection
2. field-metric collection/privacy/sampling
3. viewport evidence confidence
4. LCP image prioritization/preload
5. unjustified priority removal
6. occluded initial-viewport handling
7. lazy/eager behavior
8. `sizes=auto`/responsive sizes
9. Picture/source/art direction
10. background-image/video-poster behavior
11. AVIF/WebP/fallback format generation
12. placeholder/dominant-color/CLS dimensions
13. CDN/offload/cache/private media
14. coexistence with Performance Team/Core features
15. regeneration/lifecycle/Multisite
16. performance/regression/large media library

## 7. STM groups — 16 × 11

1. snippet definition/type parsing
2. frontend placements/hook adapters
3. admin/login advanced placements
4. ordering/dependency/cycle handling
5. Conditional Logic/context escaping
6. external script URL/origin/SRI attributes
7. inline JavaScript privilege/CSP
8. CSS/HTML/JSON-LD typed safety
9. consent category/blocking/withdrawal
10. CSP/nonces/hashes/security header integration
11. environment profiles/cache/minifier coexistence
12. validation/preview/diagnostics
13. revisions/rollback/emergency kill switch
14. import/export/WPCode/simple-script migration
15. Multisite/network policy/AI/MCP
16. adversarial security/performance/regression

## 8. WP114 non-negotiable truth boundaries

### Membership / MPR
- User ≠ Role/Capability ≠ Membership Plan ≠ Enrollment ≠ Entitlement ≠ Access Policy.
- Navigation/UI restriction ≠ protected-resource authorization.
- Registration/account creation ≠ verified email/admin approval/active membership/paid entitlement.
- Billing/provider facts remain external/provider-owned and cannot be fabricated by Membership parity.

### Role / RPR
- WordPress capabilities/meta-cap/Policy remain authorization authority; role labels/UI hiding are not authority.
- Target-role hierarchy and rescue mechanisms must not create privilege-escalation/anti-lockout bypass.
- Rescue tokens are scoped, expiring, single-use/replay-safe and must not permit account enumeration.

### Admin Theme / ATM
- Branding/theme assignment ≠ authentication/authorization.
- Accessibility/contrast/focus/recovery/login usability are correctness requirements.
- User/role/site/network precedence is explicit and cannot hide critical recovery/security controls.

### Media Performance / MDP
- Performance hint/priority inference ≠ proof of Core Web Vitals improvement until measured.
- Private/protected media must not leak through preload, metrics, placeholders, CDN or generated URLs.
- Core/Performance-Team ownership is detected and composed rather than duplicated/conflicted.

### Safe Script/Tag / STM
- Browser-side snippets only; **no PHP/eval/arbitrary SQL/shell/server code**.
- Consent/CSP/security-header policy cannot be silently weakened.
- No Vault/frontend secret interpolation.
- External origins/URLs/SRI/nonce/hash/environment placement remain typed/bounded.

### Shared
- Multisite/site/tenant ownership is server-resolved.
- AI/MCP follows the same Policy/approval gates and cannot create privileged write paths.
- Exact specification is planning only; execution remains 0 until explicit consent.

## 9. Stop-the-line examples

Future execution must stop on evidence of:
- protected resource exposed by membership/navigation fallback;
- rescue token replay/account enumeration/admin escalation;
- target-role hierarchy bypass through direct REST/Ability/bulk request;
- admin theme breaking recovery/login/admin access or producing inaccessible critical controls;
- image optimization causing LCP regression through incorrect lazy/priority behavior;
- private media URL leaked by preload/metric/placeholder;
- Script Manager executing PHP/server code;
- CSP/consent silently weakened;
- frontend secret interpolation;
- cross-site/network policy leakage.

## 10. Readiness effect / next package

ADR-0209 moves MPR/RPR/ATM/MDP/STM from `PLANNING GAP` to `NO GAP / READY AS PLAN` at evidence-design level. Operationally they remain `RUNTIME EVIDENCE PENDING`; applicable external authorities remain `PROVIDER CERTIFICATION PENDING`.

Known exact planning gap after WP114 is **3,696 definitions across 21 namespaces**.

Current safe work is **WP115 — Second Competitive exact executable-evidence specification** for `ORD/SEC/FNT/UDS/STG/BKX/MRL/PBX/JEX/LHX/HFC` — **1,936 fixtures**.

## 11. Execution gate

All five namespaces are **planning-only and 0 executed**. No WordPress request, user/role/membership mutation, registration, rescue email, admin theme runtime, RUM metric, image rewrite/regeneration, script injection, provider/API/AI/MCP call, test, benchmark or build may execute without explicit scoped owner consent under ADR-0014.