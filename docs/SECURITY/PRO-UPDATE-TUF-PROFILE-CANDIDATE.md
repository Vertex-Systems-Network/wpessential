# WPEssential — Pro Update TUF Profile Candidate

Status: **Phase 0 static supply-chain design / no updater implementation authorized**  
Related: ADR-0018, ADR-0010, ADR-0014.

## Goal

Narrow WPEssential Pro automated-update trust to an actual TUF-compatible repository/profile instead of a vague “signed manifest” while avoiding premature selection of an unsafe/pre-release PHP client.

## Research conclusion

The Update Framework defines four required top-level roles:
- Root;
- Targets;
- Snapshot;
- Timestamp.

It explicitly defends against arbitrary package substitution, rollback, freeze, mix-and-match and key-compromise scenarios when correctly implemented.

The current PHP-TUF project is useful research/reference material but explicitly states it is **pre-release and not considered a complete secure production implementation**. Therefore WPEssential must not select it for production merely because it exists.

## Accepted-for-evaluation protocol shape

WPEssential Pro update metadata will target **TUF 1.0-compatible semantics**.

### Root role

Purpose:
- bootstraps top-level trusted keys and signature thresholds;
- authorizes rotation of Root/Targets/Snapshot/Timestamp trust.

Candidate custody:
- 3 independent offline/hardware-backed root keys;
- threshold **2-of-3**;
- geographically/administratively separated where operationally practical;
- never available to ordinary web/API/CDN/CI runtime.

Root private keys are separate from WPE entitlement signing keys.

Client ships an initial trusted root metadata version with Pro updater code. Root rotation follows sequential TUF root-update verification; a normal API response cannot replace root trust.

### Targets role

Purpose:
- authorizes executable Pro release artifacts and signed custom compatibility metadata.

Candidate custody:
- 3 release-signing keys;
- threshold **2-of-3** for stable production targets;
- release signing occurs in controlled release operation, not normal website request path.

Potential future delegated targets roles:
- stable;
- beta/preview;
- emergency/recovery.

Delegation is only added when it reduces operational/key scope; v1 does not need delegation merely for architecture fashion.

### Snapshot role

Purpose:
- signs the exact versions/hashes of Targets/delegated metadata visible in one repository state;
- prevents mix-and-match metadata views.

Candidate:
- one isolated online/release-service key;
- short metadata lifetime;
- cannot authorize arbitrary target file without valid Targets metadata.

### Timestamp role

Purpose:
- provides freshness for Snapshot metadata and detects indefinite freeze.

Candidate:
- one narrowly scoped online key;
- shortest metadata lifetime;
- rotated more frequently than Root/Targets;
- compromise cannot alone authorize arbitrary package content.

## Metadata expiry policy direction

TUF guidance uses shorter expiry for Timestamp/Snapshot and longer expiry for Root/Targets.

WPE v1 paper policy:
- Timestamp: short-lived, operational target around 24 hours;
- Snapshot: short-lived, operational target around 24 hours unless release infrastructure evidence requires a slightly longer bounded window;
- Targets: materially longer but intentionally bounded;
- Root: longest-lived but rotated/renewed before expiry.

Exact production TTLs and outage operational procedure remain evidence/runbook decisions. Client does not ignore expired metadata “because server is down.”

## Consistent snapshots

**Required direction: enable TUF consistent snapshots.**

Versioned/hash-addressed metadata/target naming reduces cache/CDN mix-and-match and stale-file ambiguity.

Exact repository path layout remains implementation detail.

## Pro target metadata

Each Pro ZIP target must have trusted TUF target metadata including at minimum:
- target path/name;
- exact byte length;
- SHA-256 digest (and any additional accepted TUF hash if chosen);
- release/build identity;
- WPE Pro semantic version;
- Platform API compatibility range;
- minimum/maximum supported WordPress/PHP range where required;
- channel;
- schema/migration generation metadata needed for preflight;
- release sequence/epoch if separate from semver.

Custom metadata is signed as part of Targets metadata and never trusted from an unsigned download API response.

## Client acceptance sequence

Conceptually:
1. load persisted trusted Root;
2. update Root sequentially according to TUF rules;
3. fetch/verify fresh Timestamp;
4. fetch/verify Snapshot against Timestamp;
5. fetch/verify Targets/delegated metadata against Snapshot/root trust;
6. resolve requested Pro target;
7. enforce target hash/length;
8. enforce WPE channel/product/compatibility metadata;
9. download target with strict byte/time/resource bounds;
10. verify complete target bytes before installation staging;
11. separately run archive-structure and Free↔Pro compatibility preflight;
12. only then permit update transaction.

No metadata verification step is skipped because HTTPS or account authentication succeeded.

## Anti-rollback / freeze

Persist trusted TUF metadata versions.

Reject:
- older trusted metadata versions;
- expired Timestamp/Snapshot/Targets according to TUF update workflow;
- target release sequence below locally accepted security/update policy unless explicit trusted recovery flow is invoked;
- target whose hash/length differs;
- inconsistent channel/product identity.

An emergency rollback is not an automatic updater downgrade. It is a separate audited recovery operation using a still-authentic trusted target plus DB/schema compatibility/restore safeguards.

## CDN/API separation

Commercial entitlement may authorize a download URL/token, but:
- authorization is not authenticity;
- CDN compromise is assumed possible;
- updater trusts TUF metadata, not CDN response headers;
- metadata and targets may live behind different services;
- no download host can mint trusted target metadata without signing authority.

## Initial Pro install trust limitation

TUF secures the **installed updater's subsequent automated update path**. It cannot retroactively make an arbitrary manually uploaded first Pro ZIP safe before that package executes.

Initial acquisition therefore has a separate trust problem:
- customer obtains Pro from authenticated WPE distribution;
- WPE publishes target digest/signature metadata for independent verification;
- future optional tooling may verify a local ZIP before WordPress installation, but WordPress.org Free must not become an external Pro auto-installer;
- never claim TUF protects a malicious first package that an administrator manually installs without prior verification.

## Package staging boundary

Even an authentic ZIP can be malformed.

After cryptographic target verification and before replacement:
- path traversal/symlink/archive-bomb protections;
- expected plugin root/header/layout;
- declared compatibility;
- staged filesystem write;
- rollback point;
- migration preflight;
- no execution from unverified staging directory.

These are update-engine concerns beyond TUF authenticity.

## Key compromise runbook requirements

Before production updater:
- Root key loss/compromise procedure;
- Targets key compromise/revocation;
- Timestamp/Snapshot online key rotation;
- metadata expiry renewal process;
- emergency release authorization;
- compromised CDN/API scenario;
- lost threshold signer scenario;
- audit trail of signing ceremony/release metadata;
- customer communication and minimum-safe-version policy.

## Library decision

No production PHP TUF library is selected yet.

Current PHP-TUF may be evaluated in an owner-authorized spike, but its own current warning prohibits treating it as production-secure today.

Acceptable outcomes of future evidence:
1. a maintained production-ready TUF PHP client becomes available and passes review;
2. a narrowly scoped audited verifier implements the accepted TUF profile with explicit conformance/security evidence;
3. development remains blocked if neither route can meet the security bar.

Do **not** silently downgrade to a home-grown one-signature JSON updater.

## Future executable evidence — NOT AUTHORIZED

- official TUF conformance vectors;
- root threshold/rotation;
- targets threshold signing;
- timestamp/snapshot expiration/freeze;
- metadata rollback/fast-forward/mix-and-match;
- corrupted/wrong-length target;
- CDN stale-cache simulation;
- key-compromise/revocation drills;
- channel/delegation behavior if used;
- Free↔Pro update order and rollback;
- archive/staging failure recovery;
- library dependency/security review.

No updater client, signing keys, repository metadata or package-download implementation has been created.