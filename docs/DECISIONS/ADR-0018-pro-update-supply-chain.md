# ADR-0018 — WPEssential Pro Update Supply-Chain Architecture

Status: **Accepted security architecture; concrete updater profile pending**  
Date: 2026-08-27

## Decision

WPEssential Pro update authenticity is governed by a dedicated signed software-update trust model. It is not inferred from HTTPS, account authentication or a valid product license.

Accepted invariants:

1. **WPEssential Free never becomes the external Pro package installer/updater.**
   - Free is WordPress.org distributed and follows its directory constraints.
   - initial Pro acquisition remains external/customer-account + normal WordPress/manual install flow unless a separately compliant management-service model is approved later.

2. **Pro software-update trust is separate from commercial entitlement.**
   - entitlement answers whether a site may download/use Pro management capabilities;
   - update trust answers whether an artifact is an authentic WPE release.

3. **Executable package acceptance requires signed metadata/trust, not only URL/version.**

4. **Client must defend against:**
   - arbitrary package substitution;
   - rollback to old vulnerable release;
   - stale/freeze metadata;
   - signing-key compromise;
   - wrong product/channel artifact;
   - incompatible Free/Pro update order;
   - corrupt/truncated/malformed archives.

5. **Trust roles/keys are separated.**
   - long-lived root trust is protected offline;
   - release/targets signing is separate from normal web/API infrastructure;
   - short-lived online freshness metadata may exist but cannot alone authorize arbitrary executable content.

6. **Package/release metadata is time-bounded and monotonic.**
   - version/release sequence;
   - signed artifact digest/size/identity;
   - compatibility range;
   - metadata expiry/freshness.

7. **Client verifies full downloaded artifact before installation.**
   - cryptographic digest from trusted signed metadata;
   - signature/trust profile;
   - expected product/package/channel;
   - compatibility;
   - archive structure safety according to implementation contract.

8. **Automatic downgrade/rollback is rejected.**
   Legitimate rollback is an explicit audited recovery operation using a trusted artifact + compatibility/restore safeguards.

9. **Signing-key rotation and compromise response exist before public Pro distribution.**
   New trusted keys require an existing trust chain/root authorization, not arbitrary API data.

10. **Executable signing keys are not reused for product entitlements, OAuth, support or webhooks.**

11. **CDN/download authorization is not authenticity.**
   A compromised download host/CDN must not be enough to execute an unsigned arbitrary package if signing trust remains intact.

12. **Free↔Pro Platform API compatibility is checked before offer/install/migration.**
   Mismatch degrades safely; it must not fatal.

## Concrete protocol intentionally not yet Accepted

Preferred research order:
1. maintained PHP implementation/profile of **The Update Framework (TUF)** if it fits accepted WordPress/PHP/dependency constraints;
2. otherwise a narrowly scoped TUF-inspired signed-manifest protocol only after explicit security review proving required rollback/freeze/key-rotation properties.

Do not implement a custom updater protocol merely for convenience.

## Why TUF principles

TUF explicitly addresses update-system threats often missed by basic signature checks, including arbitrary-package, rollback, freeze and signing-key-compromise scenarios. WPEssential should retain these properties even if its eventual implementation uses a reduced deployment profile.

## Consequences

Positive:
- compromised CDN/API cannot trivially become remote code execution;
- old vulnerable release rollback is detectable;
- signing key rotation/incident response is designed from the beginning;
- commercial licensing and software authenticity remain cleanly separated.

Costs:
- release signing/key custody becomes operational infrastructure;
- update metadata lifecycle/expiry must be maintained;
- client verification adds implementation/test complexity;
- recovery/rollback must account for DB migration compatibility, not only files.

## Follow-up blockers

Before implementation:
- choose/accept exact TUF or equivalent profile;
- dependency/license/maintenance review;
- root/targets/timestamp key custody and threshold policy;
- metadata schemas;
- artifact archive policy;
- emergency key-revocation runbook;
- executable tamper/rollback/freeze/rotation tests;
- Free↔Pro update-order test matrix.

All executable work remains owner-consent gated by ADR-0014.
