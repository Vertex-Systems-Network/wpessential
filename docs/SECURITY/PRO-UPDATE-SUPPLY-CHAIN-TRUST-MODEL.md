# WPEssential Pro — Update Supply-Chain Trust Model

Status: **Phase 0 planning / no updater implementation authorized**  
Date: 2026-08-27

## 1. Scope

This document applies only to the externally distributed **WPEssential Pro** package/update channel.

WPEssential Free on WordPress.org must not become an external premium package installer/updater. Initial Pro acquisition remains outside the directory plugin and uses the normal WordPress/manual upload flow described by the Remote Service contract.

Once Pro is installed, any future external update integration requires an independently secure design.

## 2. Threats

A secure updater must consider more than HTTPS:
- arbitrary malicious package substitution;
- compromised update API/CDN;
- rollback to older vulnerable package;
- freeze/stale metadata attack;
- fast-forward/version poisoning;
- signing-key compromise;
- package corruption/truncation;
- wrong-channel or wrong-product artifact;
- incompatible Free/Pro update order;
- replay of expired metadata;
- redirect to untrusted origin;
- malicious archive structure/path traversal/symlink tricks;
- emergency withdrawal/minimum-safe-version response.

The Update Framework (TUF) explicitly models arbitrary-package, rollback, freeze and key-compromise attacks; WPEssential should adopt these security principles even if full TUF is not initially integrated.

## 3. Trust separation

Do not reuse product-entitlement keys for executable software updates.

Candidate roles:

### Root trust
- long-lived public root metadata/keys pinned in a trusted Pro artifact;
- corresponding root private keys kept offline;
- preferably threshold/multi-party protected before scale warrants production release;
- authorizes update/release signing keys and key rotation.

### Release/targets signing
- signs release target metadata and/or detached package signatures;
- private key stored separately from normal web application servers;
- compromise response can revoke/replace through root trust.

### Online freshness/timestamp metadata
- short-lived online metadata may indicate latest snapshot/release availability;
- compromise must not be sufficient to sign arbitrary package content;
- metadata expires to bound freeze attacks.

Exact adoption may be a simplified TUF-inspired profile or a maintained PHP TUF client if dependency/security review justifies it. Do not implement a home-grown protocol merely because it is shorter.

## 4. Update metadata candidate

Signed release metadata should include:
- metadata schema version;
- product/package ID;
- release channel (`stable`, future `beta`, etc.);
- release sequence/version;
- semantic product version;
- minimum/maximum Free Platform API compatibility;
- minimum WordPress/PHP where package changes requirements;
- artifact filename/identifier;
- exact byte size;
- cryptographic digest (SHA-256 minimum candidate);
- package detached signature or signed target digest;
- release notes/known issue identifiers as data;
- issued/expires timestamps;
- signing key ID;
- minimum-safe-version/revocation metadata where emergency policy requires.

Metadata never contains executable PHP/JS snippets.

## 5. Client verification sequence

Before WordPress is allowed to install an update:
1. fetch metadata from trusted HTTPS origin under strict timeout/redirect/size limits;
2. verify metadata trust/signature/expiry using already-trusted root/key material;
3. enforce product/channel identity;
4. enforce monotonic release sequence and anti-rollback policy;
5. verify Free/Pro Platform API compatibility before download/install;
6. download from approved trusted artifact origin or signed target URL policy;
7. enforce expected maximum/declared size;
8. hash entire downloaded archive and compare signed digest;
9. verify package/target signature where profile uses one;
10. inspect expected archive/package structure before installation where practical;
11. establish rollback/recovery point according to update policy;
12. hand verified package to standard WordPress upgrade/filesystem mechanisms;
13. post-install verify plugin version/bootstrap/health;
14. record audit/update result.

No package installation occurs solely because an API returned a URL and version number.

## 6. Rollback protection

Automatic updater rejects a release sequence lower than the highest trusted installed/observed sequence.

Legitimate rollback is an exceptional recovery operation and should require:
- explicit administrator action;
- compatible signed rollback target/recovery artifact;
- impact warning;
- backup/restore point;
- audit record.

Security emergency rollback policy must be designed separately from normal automatic updates.

## 7. Freeze protection

Update metadata has bounded expiry/freshness. Client stores last trusted metadata/release sequence and can surface:
- update metadata stale;
- service unreachable;
- metadata expired;
- possible rollback/freeze anomaly.

Failure to fetch fresh update information does not break the installed plugin; it means update status is unknown/stale.

## 8. Key rotation

Normal rotation:
1. distribute/authorize new release verification key through signed root metadata or trusted package update;
2. overlap trust window;
3. begin signing new releases with new key;
4. retire old signing key after safe propagation window.

Emergency compromise:
- root trust revokes compromised target/release key;
- publish short-lived fresh metadata;
- identify affected release ranges;
- publish minimum-safe-version guidance where applicable;
- rotate credentials/keys;
- document incident/recovery without exposing unnecessary exploit details before remediation.

A compromised online API/database must not automatically provide the private root/release signing keys.

## 9. Root trust compromise

Root compromise is the highest-severity updater event.

Plan must include:
- multiple offline root keys / threshold signing candidate;
- documented offline backup;
- independent custody;
- out-of-band emergency trust reset procedure if threshold root trust is lost;
- customer/security communication plan.

Do not defer root-key lifecycle planning until after paid packages are distributed.

## 10. Artifact structure rules

Candidate release package checks before installation:
- one expected plugin root directory;
- expected main plugin bootstrap path/name;
- no unexpected parent-directory traversal entries;
- no absolute paths;
- no symlink/special-file surprises unless explicitly supported/reviewed;
- package manifest/version matches signed metadata;
- reasonable file-count/expanded-size limits against archive bombs;
- development-only files/secrets/private signing material absent.

Exact archive parser/WordPress integration remains implementation work.

## 11. Free ↔ Pro update order

Updater consults Platform API compatibility before offering/applying Pro update.

States:
- Pro update compatible with installed Free → normal offer;
- requires newer Free from WordPress.org → show requirement/update Free first;
- installed Free newer than Pro supports → Pro remains degraded/read-only until compatible update;
- migration cannot run while counterpart compatibility contract is unmet.

Do not fatal because WordPress updates Free and Pro in an unexpected order.

## 12. Update channels

Initial public release should use **stable** only unless beta channel has a real operational need.

If channels are added:
- explicit opt-in for beta/RC;
- separate metadata/target trust boundaries where practical;
- never silently downgrade stable user into beta;
- return-to-stable behavior defined without breaking DB schema compatibility.

## 13. Auto-update behavior

Future Pro integration may participate in WordPress update UX/auto-update mechanisms only after verified metadata/package trust is in place.

Security-sensitive update policy should not silently force updates without a documented product decision, but critical vulnerability notices can recommend/encourage expedited update.

## 14. Entitlement vs authenticity

Two independent questions:
1. **Is this update package authentic and safe to trust as a WPE release?** — signing/update trust model.
2. **Is this customer/site entitled to download/update Pro?** — commercial service authorization.

A valid license token cannot substitute for package signature verification. A valid package signature does not itself prove the requesting account is entitled to download it.

## 15. CDN and download authorization

Private download authorization may use short-lived signed URLs/cookies/service sessions, but final package authenticity is still verified locally from signed metadata/digest.

CDN compromise should not permit silent arbitrary package execution if signing keys remain safe.

## 16. Recovery / failed update

Before update where practical:
- verify recent backup/restore point;
- capture installed Free/Pro versions and schema versions;
- detect filesystem write viability.

After failure:
- WordPress recovery mode/filesystem error surfaced safely;
- previous plugin files/package rollback strategy documented;
- DB migration rollback/recovery follows migration policy, not blind file downgrade;
- diagnostics include trusted release/update IDs, never private keys/tokens.

## 17. Security logging

Audit safe metadata:
- requested/installed version;
- release sequence;
- signing key ID;
- metadata/hash/signature verification outcome;
- compatibility decision;
- source host classification;
- update result;
- rollback/recovery result.

Never log entitlement tokens, private download credentials or signing secrets.

## 18. Candidate implementation choices

### Option A — maintained TUF client/profile
Pros:
- mature threat model and key-rotation/rollback/freeze design.
Cons:
- PHP ecosystem/dependency/operational complexity must be validated.

### Option B — narrowly scoped TUF-inspired signed manifest
Pros:
- smaller client surface.
Cons:
- substantial protocol/security responsibility; easy to omit attack classes.

### Recommendation
Research maintained PHP TUF implementations first. If they meet WordPress/PHP/dependency constraints, prefer standards-based behavior. A simplified profile is acceptable only after explicit security review demonstrating equivalent required properties for WPE's threat model.

## 19. Evidence gate

Before updater implementation/marketing:
- accepted trust/profile ADR;
- key-generation/custody/rotation runbook;
- signed fixture vectors;
- tampered package rejection;
- wrong key rejection;
- expired metadata/freeze case;
- rollback case;
- compromised/revoked key fixture;
- mismatched Free/Pro matrix;
- large/corrupt/archive attack fixtures;
- interrupted update/recovery drill;
- installable artifact verification.

All executable proof remains blocked until explicit owner development consent under ADR-0014.

## Sources
- The Update Framework security/overview/specification — supply-chain attack model and role separation.
