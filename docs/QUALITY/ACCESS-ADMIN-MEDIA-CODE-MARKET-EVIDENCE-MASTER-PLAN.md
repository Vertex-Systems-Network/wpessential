# WPEssential — Access/Admin/Media/Code Market Expansion Evidence Master Plan

Status: **Planning-only executable-evidence contract / NOT EXECUTED**  
Date: 2026-08-29

## 1. Purpose

Reserve bounded, explicit future evidence for the owner-requested competitor parity/expansion work without executing any runtime behavior.

All counters start at zero.

## 2. Evidence namespaces

| Namespace | Scope | Fixture envelope | Executed |
|---|---|---:|---:|
| `MPR` | Membership competitive parity / registration / private-site / migration presets | MPR-001…MPR-176 | 0/176 |
| `RPR` | Role hierarchy / rescue / surface-policy integration / compatibility | RPR-001…RPR-176 | 0/176 |
| `ATM` | Admin Theme, Branding & Experience Manager | ATM-001…ATM-176 | 0/176 |
| `MDP` | Media Performance, Responsive Delivery & Field Optimization | MDP-001…MDP-176 | 0/176 |
| `STM` | Safe Script, Tag & Code Injection Manager | STM-001…STM-176 | 0/176 |

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

Each group reserves eleven fixed cases including happy, boundary, denied, malicious, concurrent, stale/replay, failure/recovery, migration, Multisite and performance variants where meaningful.

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

## 8. Stop-the-line examples

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

## 9. Execution gate

All five namespaces are **documented only**. No fixture, WordPress request, user/role mutation, registration, rescue email, admin theme, RUM metric, image rewrite, script injection, benchmark or compatibility test has executed. ADR-0014 owner consent remains mandatory.