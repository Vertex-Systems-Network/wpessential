# ADR-0209 — First Competitive Exact Executable-Evidence Protocols

Status: **Accepted — planning/evidence design only / NOT EXECUTED / NO DEVELOPMENT AUTHORIZATION**  
Date: 2026-08-29  
Work package: **WP114**

## Context

ADR-0207 classified 33 supplemental market/competitive namespaces as `PLANNING GAP` because their IDs and 16-group ownership were fixed but individual fixtures were not enumerated at the same exact depth as the detailed universal/adapter protocols. ADR-0208 closed the seven Market Expansion namespaces and reduced the known exact-planning gap to 4,576 definitions across 26 namespaces.

WP114 covers the First Competitive / Access-Admin-Media-Code family:
- `MPR-001…MPR-176` — Membership competitive parity;
- `RPR-001…RPR-176` — Role & Capability competitive parity;
- `ATM-001…ATM-176` — Admin Theme, Branding & Experience;
- `MDP-001…MDP-176` — Media Performance, Responsive Delivery & Field Optimization;
- `STM-001…STM-176` — Safe Script, Tag & Code Injection.

Canonical group ownership remains fixed by `docs/QUALITY/ACCESS-ADMIN-MEDIA-CODE-MARKET-EVIDENCE-MASTER-PLAN.md`. WP114 does not renumber or repurpose any namespace and does not replace existing canonical runtime/provider protocols.

## Decision

Accept the following exact executable-evidence planning protocols as canonical:

1. `docs/QUALITY/MEMBERSHIP-COMPETITIVE-PARITY-EXECUTABLE-EVIDENCE-PROTOCOL.md`
2. `docs/QUALITY/ROLE-CAPABILITY-COMPETITIVE-PARITY-EXECUTABLE-EVIDENCE-PROTOCOL.md`
3. `docs/QUALITY/ADMIN-THEME-BRANDING-EXECUTABLE-EVIDENCE-PROTOCOL.md`
4. `docs/QUALITY/MEDIA-PERFORMANCE-DELIVERY-EXECUTABLE-EVIDENCE-PROTOCOL.md`
5. `docs/QUALITY/SAFE-SCRIPT-TAG-CODE-INJECTION-EXECUTABLE-EVIDENCE-PROTOCOL.md`

Accepted counters:
- MPR: **176/176 documented / 0/176 executed / runtime certification 0**;
- RPR: **176/176 documented / 0/176 executed / runtime certification 0**;
- ATM: **176/176 documented / 0/176 executed / runtime certification 0**;
- MDP: **176/176 documented / 0/176 executed / runtime certification 0**;
- STM: **176/176 documented / 0/176 executed / runtime certification 0**.

WP114 total: **880/880 exact fixture definitions documented / 0 executed**.

These five namespaces move from `PLANNING GAP` to `NO GAP / READY AS PLAN` at the evidence-design layer. Operationally they remain `RUNTIME EVIDENCE PENDING` and, where external authorities apply, `PROVIDER CERTIFICATION PENDING`. No runtime certification is created by this ADR.

## Preserved truth boundaries

### Membership / MPR
- User ≠ Role/Capability ≠ Membership Plan ≠ Enrollment ≠ Entitlement ≠ Access Policy.
- Registration/account creation ≠ verified email/admin approval/active membership/paid entitlement.
- Navigation/UI hiding ≠ protected-resource authorization.
- Billing/provider facts remain external/provider-owned.
- Legacy role/label/import mapping cannot silently become canonical Membership truth.

### Role / RPR
- WordPress capability/meta-cap behavior plus WPE Policy remain canonical authorization authority.
- Role labels, menu visibility and editor visibility are not authorization.
- Generic user-management capability does not imply authority over every target role/user.
- Super Admin is not modeled as an ordinary role.
- Administrator Rescue is a separate high-risk recovery path with scoped, expiring, replay-safe, enumeration-safe artifacts.
- Permission simulation/explanation is not user impersonation.

### Admin Theme / ATM
- Branding/theme assignment ≠ authentication/authorization.
- Visual hiding cannot revoke/grant destination authority.
- Accessibility, contrast, focus, keyboard, login and recovery usability are correctness requirements.
- Environment color is not the sole safety signal.
- Core/native/token/fallback ownership is capability-detected and versioned; compatibility is not assumed.

### Media Performance / MDP
- LCP/priority/viewport inference ≠ measured Core Web Vitals improvement.
- Field sample ≠ permanent truth; missing/stale/contradictory evidence is represented explicitly.
- Private/protected media cannot leak through preload, srcset, placeholder, telemetry, cache or CDN optimization.
- WordPress Core/Performance Team capability ownership is detected/composed, not duplicated blindly.
- Original media source remains immutable in the standard profile.

### Safe Script / STM
- Browser-side/declarative output only; **no PHP, eval, arbitrary SQL, shell or server-code runtime**.
- Snippet editor/read access ≠ high-risk publish authority.
- Condition/visibility ≠ authorization.
- Consent category ≠ legal compliance and CSP compatibility ≠ permission to weaken CSP.
- Vault secrets are not frontend token sources.
- Imported code remains Draft/untrusted until normal validation/approval; PHP/server snippets are rejected from this runtime.

### Shared
- Site/network/tenant ownership is server-resolved.
- AI/MCP uses the same Capability/Policy/approval boundaries and gains no hidden privilege path.
- Unknown external/provider/cache/CDN outcome remains unknown until reconciled where applicable.
- Paper evidence never becomes runtime/provider certification.

## Planning-gap effect

Before WP114 / ADR-0209:
- **4,576 exact definitions across 26 namespaces** remained.

WP114 closes:
- **880 definitions across 5 namespaces**.

After ADR-0209, known exact-planning gap is:
- **3,696 exact definitions across 21 namespaces**.

Remaining sequence:
- **WP115 — CURRENT** — Second Competitive exact evidence: `ORD`, `SEC`, `FNT`, `UDS`, `STG`, `BKX`, `MRL`, `PBX`, `JEX`, `LHX`, `HFC` — **1,936 fixtures**;
- WP116 — RESERVED — Third Competitive exact evidence: `UAF`, `MIG`, `WLB`, `DUP`, `ALX`, `MBX`, `THM`, `RSX`, `RDX`, `CPTX` — **1,760 fixtures**.

After WP116, a new final closure/readiness audit must decide whether P0 may move to `AWAITING_DEVELOPMENT_APPROVAL`. That transition is not automatic and cannot itself grant implementation consent.

## Development / runtime truth

WP114 performed documentation/evidence design only. No WordPress/WooCommerce runtime, user/role/membership mutation, rescue email, admin-theme application, browser-script injection, field-metric collection, media regeneration/rewrite, provider/API/AI/MCP call, test, benchmark, package install, migration, build or deployment was executed.

Implementation authorization remains **NOT GRANTED / 0/56** under ADR-0014.

## Consequences

- WP114 is **DONE**.
- MPR/RPR/ATM/MDP/STM are exact planning-complete and unexecuted.
- Current safe planning work becomes **WP115 — Second Competitive exact executable-evidence specification**.
- Repository/PR/Linear current-state authorities must be synchronized to ADR-0209 / WP115.
