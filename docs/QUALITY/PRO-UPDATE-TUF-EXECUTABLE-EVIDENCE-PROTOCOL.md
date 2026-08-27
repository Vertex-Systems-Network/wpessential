# WPEssential — Pro Updater TUF Executable Evidence Protocol

Status: **Phase 0 consent-gated protocol / 0 fixtures executed**  
Date: 2026-08-28  
Related: ADR-0014, ADR-0018, ADR-0044, Pro Update TUF Profile Candidate, TUF 1.0.

## 1. Purpose

Predefine the evidence required before automated WPEssential Pro updates can be enabled.

The updater must prove authenticity, freshness, anti-rollback, mix-and-match resistance, key rotation, package integrity, compatibility preflight and safe staging. HTTPS/account authentication alone is never sufficient.

No updater/client/repository/signing key implementation is authorized by this protocol.

## 2. TUF repository profile under first evaluation

Top-level roles:
- Root;
- Targets;
- Snapshot;
- Timestamp.

Consistent snapshots: **enabled in first profile**.

### TK1 key-custody paper profile

Root:
- 3 independent offline/hardware-backed candidate keys;
- 2-of-3 threshold;
- unavailable to normal web/CDN/CI runtime.

Targets:
- 3 controlled release-signing candidate keys;
- 2-of-3 threshold for stable production channel.

Snapshot:
- narrowly scoped online/release-service key candidate.

Timestamp:
- narrowly scoped online freshness key candidate.

TK1 is an evidence baseline, not production key ceremony approval. Future operational/security review can supersede exact counts/custody through ADR if evidence requires.

## 3. Metadata expiry paper profile

First test policy classes:
- Timestamp: ~24-hour class;
- Snapshot: ~24-hour class;
- Targets: longer bounded release-policy class;
- Root: longest bounded class with proactive renewal/rotation.

Exact production TTLs are not accepted here. Tests must prove expired metadata fails closed and outage handling does not disable verification.

## 4. Trusted-root bootstrap

Client starts from one bundled trusted Root metadata version.

Proof requirements:
- Root metadata version/hash is part of installed trusted updater code/artifact;
- ordinary API/account/CDN response cannot replace root trust;
- Root updates are accepted sequentially under TUF root-rotation rules;
- skipped/unverifiable root chain fails;
- old root cannot roll back newer trusted root state.

## 5. Client trusted-state persistence

Persist minimum trusted update state outside ordinary mutable remote API cache:
- trusted Root version/metadata;
- highest accepted Timestamp/Snapshot/Targets versions as required by verifier;
- metadata expiry/fetch state;
- selected channel/product;
- last verified target version/release sequence/hash;
- safe correlation/error state.

Local state cannot by itself authorize an untrusted target; it only supports verification/rollback detection.

## 6. Update resolution pipeline

`trusted Root → verify/update Root → fresh Timestamp → verify Snapshot → verify Targets/delegations → resolve exact target → enforce hash/length/custom compatibility → download bounded bytes → hash/length verify → archive safety/preflight → staged replacement → post-update compatibility/migration verification`

Entitlement/download authorization is separate from authenticity.

## 7. Target metadata minimum

Each Pro target under first profile must include signed metadata for:
- target path/name;
- exact byte length;
- SHA-256 digest;
- WPE Pro semver;
- release/build identity;
- product/channel;
- Platform API compatibility range;
- supported WordPress/PHP ranges where policy requires;
- schema/migration generation;
- release sequence/epoch if used;
- emergency/recovery classification where applicable.

Unsigned API fields cannot override these target facts.

## 8. TUF fixture classes

### TU-01 — Valid stable update
Fresh trusted metadata + correct target → update preflight can proceed.

### TU-02 — Target byte corruption
One byte changed; hash mismatch rejects before staging execution.

### TU-03 — Wrong target length
Reject.

### TU-04 — Unsigned/malformed Targets metadata
Reject.

### TU-05 — Below-threshold Targets signatures
Reject.

### TU-06 — Below-threshold Root signatures
Reject.

### TU-07 — Wrong key for role
Timestamp key signs Targets or vice versa; reject according to role trust.

### TU-08 — Metadata rollback
Serve older trusted version after newer accepted; reject.

### TU-09 — Target rollback
Serve older Pro release/release sequence contrary to policy; reject normal automated downgrade.

### TU-10 — Timestamp expired
Reject stale update metadata; show actionable service/freshness state.

### TU-11 — Snapshot expired
Reject.

### TU-12 — Targets expired
Reject according to trusted metadata policy.

### TU-13 — Root expired/near-expiry
Exercise renewal/rotation procedure; client must not silently ignore expiry.

### TU-14 — Freeze attack
Repeated old but correctly signed expired Timestamp must not keep client indefinitely accepting old repository state.

### TU-15 — Mix-and-match Snapshot/Targets
Serve metadata versions not belonging to same trusted Snapshot graph; reject.

### TU-16 — Consistent-snapshot stale target path
Serve wrong hash/version-addressed object; reject.

### TU-17 — CDN returns attacker ZIP with valid account session
Reject because no matching TUF target hash/length.

### TU-18 — API says `latest_version` newer than TUF
Ignore as authenticity authority; updater only trusts verified TUF target.

### TU-19 — TUF target exists but Product entitlement absent
Authenticity passes but commercial authorization blocks download/install according to entitlement policy. Do not conflate failure reason.

### TU-20 — Root key rotation normal
Old threshold signs next Root with new key set; client advances sequentially.

### TU-21 — Root rotation skipped version
Attempt jump that violates accepted client root-update rules; reject/recover according to TUF verifier behavior.

### TU-22 — One Root key compromised below threshold
Attacker cannot mint trusted Root alone.

### TU-23 — Targets key compromised below threshold
Attacker cannot authorize production target alone.

### TU-24 — Timestamp online key compromised
Attacker can affect freshness metadata within role power but cannot alone authorize arbitrary target content; verify containment.

### TU-25 — Snapshot key compromised
Verify inability to authorize target absent trusted Targets metadata.

### TU-26 — Targets key rotation/revocation
New trusted Root/Targets role transition invalidates compromised/old key according to procedure.

### TU-27 — Metadata download truncated/corrupt
Reject and retain prior trusted state safely.

### TU-28 — Metadata JSON/resource exhaustion corpus
Parser size/depth/count limits prevent memory/resource abuse.

### TU-29 — Unknown future required spec/profile
Fail safe with upgrade instruction; do not interpret loosely.

### TU-30 — Channel confusion
Beta target cannot satisfy stable update request unless explicit channel switch policy authorizes it.

### TU-31 — Product/package confusion
Different WPE product ZIP with valid signature cannot replace Pro target.

### TU-32 — Platform API incompatibility
Authentic target blocked by Free↔Pro compatibility preflight.

### TU-33 — WordPress/PHP incompatibility
Authentic target blocked before replacement.

### TU-34 — Malformed ZIP path traversal/symlink/bomb
Authenticity alone is insufficient; archive safety rejects before execution.

### TU-35 — Staging disk-full/write failure
Existing working Pro remains recoverable; target never executes from partial staging.

### TU-36 — Replacement failure after old plugin moved/backup point created
Recovery restores previous compatible package/state according to updater transaction policy.

### TU-37 — Migration preflight fails
Do not execute schema migration/update blindly; preserve prior running version.

### TU-38 — Update completes but health check fails
Recovery/rollback decision follows accepted package/schema policy; report truthfully.

### TU-39 — Emergency trusted rollback
Only explicit audited recovery flow using authentic still-trusted target + compatibility/DB restore safeguards; normal updater does not auto-downgrade.

### TU-40 — Metadata cache cross-environment
Staging repository metadata cannot satisfy production trust/cache key.

### TU-41 — Clock skew
Reasonable local clock errors produce diagnosable expiry behavior; no permanent bypass/ignore-expiry option as normal recovery.

### TU-42 — Service/CDN outage with current Pro installed
Installed code continues; updater reports unavailable/stale state without installing unverified fallback.

### TU-43 — Initial manual Pro package
Document limitation: installed updater cannot retroactively secure an already malicious first package. Acquisition/pre-install verification is a separate trust surface.

### TU-44 — Key-compromise drill
Runbook exercises Root/Targets/Timestamp/Snapshot compromise response, revocation, new metadata publication and client recovery.

## 9. Metadata/cache pass gates

Fail if client:
- trusts expired metadata;
- accepts lower trusted versions;
- accepts target not linked through current verified metadata graph;
- uses account API/CDN response to add root trust;
- caches metadata across production/staging repository identities;
- overwrites last known trusted metadata state with failed/unverified download.

## 10. Key-custody pass gates

Before production:
- Root private material demonstrably offline from ordinary service runtime;
- Targets release keys separated from ordinary web request path;
- Snapshot/Timestamp online role permissions isolated;
- threshold signing procedure documented/audited;
- lost-key procedure;
- compromised-key revocation procedure;
- metadata expiry renewal ownership/on-call procedure;
- signing ceremony/release audit trail.

The evidence report must not include private key bytes.

## 11. Package staging pass gates

- target hash/length verified before installation staging;
- archive paths constrained to expected plugin root;
- no symlink/path traversal escape;
- decompression/file-count/size bounds;
- plugin header/root identity verified;
- compatibility preflight before replacement;
- rollback/recovery point before risky replacement/migration;
- unverified staging code never executed.

## 12. Required evidence report

Include:
- verifier/library/version/commit or audited in-house verifier identity;
- TUF spec/profile version;
- repository layout/profile;
- trusted Root bootstrap version;
- role thresholds/key custody architecture (no private material);
- metadata expiry values used;
- consistent-snapshot behavior;
- TU-01…TU-44 pass/fail;
- official TUF conformance results where applicable;
- archive/staging recovery results;
- Free↔Pro compatibility matrix;
- unresolved security findings;
- independent review status.

## 13. Current state

**TU fixtures executed: 0/44.**

No TUF metadata, repository, signing key, target ZIP, updater client, package download, staging or replacement has been created/executed.

## 14. Development gate

Execution requires explicit owner consent under ADR-0014. If no production-grade verifier can meet this protocol, automated Pro updates remain blocked rather than downgraded to weak signed JSON.