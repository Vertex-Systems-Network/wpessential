# Protector — Runtime Gap Matrix V1

Surface: **27 / Protector**  
Planning issue: **#593**  
Supervisor wave: **#583**  
Exact-main claim anchor: `588079965734c56b2bda783778c9efa4dbf63530`

Status: **PLANNING GAP MATRIX / RUNTIME NOT AUTHORIZED**

Surface 27 remains `UNSEEDED / 0` in the Master Options Bank and exact main has no dedicated Protector runtime module. This matrix records prerequisites and owner boundaries only.

## Gap matrix

| Area | Exact-main state | Future requirement | Boundary / gate |
|---|---|---|---|
| Master Options Bank | **BLOCKED — UNSEEDED / 0** | Native/market reviewed normalized records. | Product gate before option contracts/runtime. |
| Atomic Option Contracts | **PLANNING INVENTORY ONLY** | Schema-valid contracts after Bank review. | No lifecycle promotion from inventory alone. |
| Protector Rule runtime | **ABSENT** | canonical definitions, revision/CAS, lifecycle, compiled match plan. | Surface 27. |
| Request normalization/matching | **ABSENT** | exact/prefix/bounded pattern/resource matching. | No Host/header trust shortcuts. |
| Policy composition | **ABSENT** | outer deny/challenge/rate only; cannot grant underlying access. | Shared Policy remains authority. |
| Site Gate | **ABSENT** | password/auth/capability/Membership modes with safe recovery. | Password hash only; session/cookie contract required. |
| Login alias/protection | **ABSENT / HIGH RISK** | compatible login/reset/logout/multisite handling + anti-lockout. | Separate publish/re-auth gate. |
| Admin restrictions | **ABSENT** | route/screen/action-aware restrictions. | admin-ajax/admin-post/REST compatibility mandatory. |
| Rate limiting | **ABSENT** | race-safe backend, bounded profiles, trusted-proxy-aware keys. | No strict guarantees without backend evidence. |
| Trusted proxy/client IP | **ABSENT** | explicit proxy CIDRs/header allowlist/hop strategy/spoof resistance. | Forwarded headers untrusted by default. |
| IP/CIDR groups | **ABSENT** | validated IPv4/IPv6 sets, expiry/import/usage. | Supplemental only. |
| Security headers | **ABSENT** | structured conflict-aware CSP/HSTS/etc. | No auto HSTS preload/strict CSP. |
| REST outer protection | **ABSENT** | deny/rate/logging without replacing endpoint permission callback. | Surface 22/endpoint Policy authoritative. |
| XML-RPC integration | **DEPENDENCY** | delegated summary/hooks only. | Surface 29 owns truth. |
| Recovery architecture | **ABSENT** | safe-disable path, recovery principal/session, publish preflight. | Required before high-risk enforcement. |
| Logs/privacy | **ABSENT** | bounded denied/security evidence with redaction/IP retention. | No credentials/bodies. |
| Multisite | **UNSPECIFIED** | network/site login/admin/recovery/rules scope. | Contract before runtime. |
| Accessibility | **NO SURFACE UI** | simulation/rules/recovery/log accessibility. | Browser/axe evidence later. |
| Compatibility | **NO SURFACE RUNTIME** | WP/PHP/proxy/CDN/server/plugin/browser matrix. | Exact-head evidence later. |
| Reliability/performance | **NO SURFACE RUNTIME** | compiled rule indexes/cache, bounded rate writes/logs, no remote auth-path calls. | Deterministic tests later. |

## Security hard gates

Future implementation must reject or fail closed on:

- treating UI/menu hiding or login alias as authorization;
- Protector Allow bypassing core/WPE denial;
- trusting forwarded IP headers from untrusted proxies;
- blanket REST/admin-ajax/admin-post/wp-admin blocking without route-aware impact analysis;
- arbitrary PHP conditions or unsafe regex engine;
- permanent secret query-string recovery bypass;
- publishing high-risk rules without recovery/lockout preflight;
- automatic permanent IP bans;
- HSTS preload or strict CSP auto-enable;
- credentials/cookies/auth headers/query secrets/full bodies in logs;
- remote service dependency inside synchronous request authorization.

## Required Policy / Ability separation

Separate Rule read/edit/validate/simulate/publish/delete from rate/network/header/login-alias/recovery/bypass abilities. High-risk publish/login/recovery/network/header changes require elevated capability and may require recent re-auth. AI defaults remain read/simulate/explain only.

## Accessibility evidence required later

- keyboard rule/simulation/recovery/rate/header editors;
- linked validation/lockout/conflict errors;
- focus after simulation/publish/re-auth errors;
- non-color-only decisions and health states;
- accessible match explanation and logs;
- axe coverage for denied, recovery-risk, proxy-invalid and header-conflict states.

## Multisite evidence required later

- network vs site rules/gates;
- network login/admin behavior;
- Super Admin/recovery path;
- shared vs site-specific trusted proxy/network groups;
- REST and admin route ownership by site/network;
- log/privacy isolation.

## Reliability/performance evidence required later

1. direct-request authorization matches UI expectation;
2. normalization and specificity/deny precedence are deterministic;
3. current-admin/login/recovery lockout preflight catches unsafe publish;
4. login/reset/logout/multisite paths remain recoverable;
5. admin-ajax/admin-post/REST/Gutenberg compatibility regressions are covered;
6. spoofed forwarded headers do not alter client IP;
7. rate-limit writes are race-safe and backend degradation is fail-safe by target policy;
8. redirect loops and unsafe external targets are blocked;
9. CSP/HSTS/header conflict behavior is deterministic;
10. rule compilation avoids scanning every definition on every request;
11. deny decision does not depend on successful log write;
12. no external remote call occurs in request authorization path.

## Future implementation order

Only after Bank review + schema-valid option contracts:

1. Rule definitions + read-only simulation/explainability;
2. request normalization/compiled matcher + Policy composition;
3. Site Gate with recovery architecture;
4. login/admin protection + rate limiting/trusted proxies;
5. security headers + REST/XML-RPC integration boundaries;
6. logs/privacy/multisite/degraded closure;
7. exact-head security/accessibility/compatibility/performance certification audit.

Login alias, private recovery bypass mechanisms and high-impact network/header controls remain separately reviewed high-risk slices.

## Exit decision

Exact main has a mature exhaustive Protector specification and clear owner boundaries, but the Master Options Bank remains `UNSEEDED / 0` and no runtime module exists. `runtime_allowed=false` remains correct.

The next valid action is Bank seeding/native/market review, then schema-valid option contracts and UX re-review — not request enforcement from this planning lane.
