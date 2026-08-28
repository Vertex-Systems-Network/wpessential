# WPEssential — Pro Updater TUF Executable Evidence Protocol

Status: **Phase 0 consent-gated protocol / 0 fixtures executed**  
Date: 2026-08-28  
Related: ADR-0018, ADR-0044, ADR-0102, FP, VER, BT, CI, Vault, Product License, Module Lifecycle, Backup/Recovery, ADR-0014.

## 1. Purpose

Predefine evidence required before automated WPEssential Pro updates can be enabled.

The updater must prove authenticity, freshness, anti-rollback, freeze resistance, mix-and-match resistance, key rotation/revocation, target/package integrity, compatibility preflight, safe staging, replacement/recovery and release provenance. HTTPS, Account login or Product entitlement alone is never sufficient update authenticity.

As of 2026-08-28, upstream TUF stable releases remain in the 1.0 line; future certification MUST pin the exact TUF specification patch/version and verifier/library implementation tested rather than relying on a timeless `TUF 1.0` label.

No updater client, repository, signing key, target ZIP, package download, staging, plugin replacement or migration is authorized by this protocol.

## 2. Trust separation

These truths remain separate:

`Account identity ≠ Product entitlement ≠ download authorization ≠ TUF metadata authority ≠ target authenticity ≠ compatibility ≠ package staging safety ≠ successful activation/migration ≠ production health`

Free is not an external Pro updater authority. Trusted Root bootstrap belongs to the shipped updater/verifier artifact and cannot be replaced by an ordinary API/CDN response.

## 3. Candidate repository/key profile

Initial paper profile:
- top-level roles: Root, Targets, Snapshot, Timestamp;
- consistent snapshots enabled;
- Root candidate: 3 independent offline/hardware-backed keys, 2-of-3 threshold;
- Targets stable candidate: 3 controlled release keys, 2-of-3 threshold;
- Snapshot/Timestamp narrowly scoped online keys;
- exact thresholds/custody remain evidence/operations gated.

Metadata expiry classes remain bounded and role-specific. Exact production TTLs are not accepted by this protocol.

## 4. Canonical verification pipeline

`bundled trusted Root → sequential Root update → fresh Timestamp → verified Snapshot → verified Targets/delegations → exact product/channel target resolution → signed hash/length/custom compatibility facts → bounded download → target bytes hash/length verification → archive/plugin identity safety → staging → compatibility/recovery preflight → replacement → migration/activation → post-update health/reconciliation`

No later stage retroactively upgrades an earlier failed trust stage.

## 5. Fixed fixture matrix

### A. Original TU-01…TU-44 — preserved
- **TU-01** Valid stable update.
- **TU-02** One-byte target corruption rejected.
- **TU-03** Wrong target length rejected.
- **TU-04** Unsigned/malformed Targets rejected.
- **TU-05** Below-threshold Targets signatures rejected.
- **TU-06** Below-threshold Root signatures rejected.
- **TU-07** Wrong role key rejected.
- **TU-08** Metadata rollback rejected.
- **TU-09** Target/release rollback rejected in normal automated flow.
- **TU-10** Expired Timestamp rejected.
- **TU-11** Expired Snapshot rejected.
- **TU-12** Expired Targets rejected.
- **TU-13** Root expiry/renewal handled explicitly.
- **TU-14** Freeze attack cannot keep stale repository accepted indefinitely.
- **TU-15** Snapshot/Targets mix-and-match rejected.
- **TU-16** Consistent-snapshot stale/wrong target rejected.
- **TU-17** Attacker ZIP over authenticated CDN/API rejected absent trusted target hash.
- **TU-18** Unsigned API `latest_version` never overrides TUF.
- **TU-19** Authentic target + absent entitlement remains commercially blocked.
- **TU-20** Normal Root key rotation.
- **TU-21** Invalid skipped Root rotation rejected.
- **TU-22** One compromised Root key below threshold insufficient.
- **TU-23** One compromised Targets key below threshold insufficient.
- **TU-24** Timestamp-key compromise containment.
- **TU-25** Snapshot-key compromise containment.
- **TU-26** Targets key rotation/revocation.
- **TU-27** Truncated/corrupt metadata rejected without replacing trusted state.
- **TU-28** Metadata parser resource limits.
- **TU-29** Unknown future required spec/profile fails safely.
- **TU-30** Stable/beta channel confusion rejected.
- **TU-31** Product/package confusion rejected.
- **TU-32** Authentic but Platform API-incompatible target blocked.
- **TU-33** Authentic but WP/PHP-incompatible target blocked.
- **TU-34** ZIP traversal/symlink/bomb safety.
- **TU-35** Staging disk-full/write failure preserves current working Pro.
- **TU-36** Replacement failure recovery.
- **TU-37** Migration preflight failure blocks unsafe update.
- **TU-38** Post-update health failure enters truthful recovery decision.
- **TU-39** Emergency rollback is explicit/audited, not normal downgrade.
- **TU-40** Metadata cache does not cross environment/repository identity.
- **TU-41** Clock skew diagnosable without permanent ignore-expiry bypass.
- **TU-42** Service/CDN outage preserves installed code; no unverified fallback.
- **TU-43** First/manual package trust limitation documented.
- **TU-44** Key-compromise drill.

### B. Specification/verifier profile and parser behavior
- **TU-45** Certification records exact TUF spec version/patch.
- **TU-46** Verifier/library exact version/commit/build provenance recorded.
- **TU-47** Unsupported major spec version rejected.
- **TU-48** Supported newer 1.0 patch parsed only after compatibility/conformance evidence.
- **TU-49** `spec_version` malformed/non-semver rejected.
- **TU-50** Metadata `_type` mismatch rejected.
- **TU-51** Duplicate JSON keys handled according to audited parser/profile; ambiguous signed content rejected.
- **TU-52** Canonical signature input semantics match verifier implementation.
- **TU-53** Unknown non-critical custom fields do not override trusted standard fields.
- **TU-54** Integer/version overflow/negative/zero edge cases rejected per profile.
- **TU-55** Excessive signature/key entries bounded.
- **TU-56** Metadata byte/depth/object-count limits bounded before resource exhaustion.
- **TU-57** Unicode/path normalization does not create alternate target identity.
- **TU-58** Target path traversal/absolute/backslash ambiguity rejected.
- **TU-59** Mirror/base URL joining cannot escape configured repository origin unintentionally.
- **TU-60** Metadata content-type/encoding mismatch fails safely.

### C. Root bootstrap, rotation and key custody
- **TU-61** Bundled Root bytes/version/hash tied to shipped updater artifact.
- **TU-62** Ordinary Product API cannot inject new root trust.
- **TU-63** Sequential Root update verifies old-root and new-root threshold rules as required by selected verifier/profile.
- **TU-64** Same-version Root replacement rejected/handled per verifier without trust reset.
- **TU-65** Root rollback after newer trusted Root rejected.
- **TU-66** Root chain gap produces recovery-required state, not trust bypass.
- **TU-67** Root metadata key-ID/key mismatch rejected.
- **TU-68** Revoked Root key no longer satisfies future threshold.
- **TU-69** Threshold changed by unauthorized/insufficiently signed Root rejected.
- **TU-70** Role key reassignment cannot make Timestamp key a Targets authority outside trusted Root.
- **TU-71** Offline Root private material absent from web/CDN/normal CI workers.
- **TU-72** Targets release keys separated from ordinary request path.
- **TU-73** Snapshot/Timestamp online key permissions scoped to intended roles.
- **TU-74** Signing ceremony records public metadata/audit, never private key material.
- **TU-75** Lost Root-key runbook preserves threshold/recovery assumptions.
- **TU-76** Compromise response rotates/revokes without silently weakening threshold.
- **TU-77** Root expiry ownership/on-call alerting evidence exists before production.
- **TU-78** Offline backup/recovery of root trust has documented custody controls.

### D. Timestamp/Snapshot/Targets graph, freshness and rollback
- **TU-79** Timestamp version monotonic trusted-state persistence.
- **TU-80** Snapshot version/length/hash linkage verified where metadata supplies it.
- **TU-81** Targets version/length/hash linkage verified where metadata supplies it.
- **TU-82** Metadata downloaded from mirror/CDN cannot bypass parent metadata linkage.
- **TU-83** Timestamp rollback after local state loss scenario requires trusted recovery, not blind reset.
- **TU-84** Snapshot rollback after local state loss treated explicitly.
- **TU-85** Targets rollback after local state loss treated explicitly.
- **TU-86** Expiry checks use UTC/time semantics consistently.
- **TU-87** Far-future local clock fails diagnosably; no blanket expiry disable.
- **TU-88** Far-past clock cannot accept otherwise expired metadata indefinitely.
- **TU-89** Fresh Timestamp pointing to unavailable Snapshot yields unavailable state, not older silent graph.
- **TU-90** Fresh Snapshot pointing to unavailable Targets yields unavailable state.
- **TU-91** Repository partial publish ordering is tested for safe client behavior.
- **TU-92** CDN propagation skew cannot produce accepted mix-and-match graph.
- **TU-93** Consistent-snapshot filename/hash path resolution exactly tested.
- **TU-94** Old unversioned target path cannot satisfy consistent-snapshot target resolution where disabled by profile.
- **TU-95** Cached verified metadata remains separate from failed/unverified fetch bytes.
- **TU-96** Corrupt cache triggers safe refetch/recovery without trust reset.

### E. Delegations, channel/product resolution and target custom metadata
- **TU-97** Delegated role path scope enforced if delegations are used.
- **TU-98** Delegation terminating/non-terminating behavior tested if used.
- **TU-99** Delegation cycle/depth/role-count bounded.
- **TU-100** Delegated key threshold enforced.
- **TU-101** Stable/beta/dev channels map to explicit trusted target namespaces/policies.
- **TU-102** Product slug/edition mismatch cannot substitute another signed target.
- **TU-103** Architecture/package variant mismatch rejected.
- **TU-104** Target custom semver is informational unless explicitly signed policy uses it; target path/hash remains authoritative artifact identity.
- **TU-105** Release sequence/epoch rollback rule is signed and monotonic where used.
- **TU-106** Platform API min/max range signed and parsed strictly.
- **TU-107** WordPress/PHP compatibility range signed and validated against trusted local environment facts.
- **TU-108** Schema/migration generation signed and compared with local state.
- **TU-109** Emergency/recovery target classification signed and cannot be supplied only by API.
- **TU-110** Target custom metadata cannot choose arbitrary install path/class/callback.
- **TU-111** Target hash algorithm set contains required strong algorithm; unsupported-only hash set rejected.
- **TU-112** Multiple hashes all handled according to verifier/profile without accepting a mismatching required hash.

### F. Product entitlement, Account and environment separation
- **TU-113** Account authenticated but entitlement expired: authenticity check remains independent; install policy blocks appropriately.
- **TU-114** Entitlement valid but TUF invalid: update rejected.
- **TU-115** Entitlement service outage does not weaken TUF verification.
- **TU-116** TUF service outage does not convert signed entitlement into update authenticity.
- **TU-117** Site allocation change cannot rewrite trusted target hash/version.
- **TU-118** Production vs staging environment classification partitions entitlement and repository/cache state as designed.
- **TU-119** Clone restored to another environment revalidates installation/allocation and updater trusted-state ownership.
- **TU-120** Multisite network/site allocation does not let subsite choose update trust root.
- **TU-121** Site admin cannot install/network-update Pro absent native network/update authority.
- **TU-122** Account unlink leaves installed code safe but does not create unauthenticated update path.

### G. Target download and archive/plugin identity safety
- **TU-123** Download byte ceiling enforced before disk/resource exhaustion.
- **TU-124** Redirect chain/scheme/host policy bounded.
- **TU-125** Partial HTTP/resume behavior cannot splice bytes into trusted target without final full hash/length verification.
- **TU-126** Range/proxy/CDN corruption caught by final target verification.
- **TU-127** Temp filename/path cannot escape staging root.
- **TU-128** ZIP central/local header path inconsistencies handled safely.
- **TU-129** Duplicate archive paths rejected/handled deterministically.
- **TU-130** Case-folding/path collision on target filesystem prevented.
- **TU-131** Symlink/hardlink/device/special-file entries rejected unless explicitly safe profile.
- **TU-132** Decompression byte/file/depth limits bounded.
- **TU-133** Expected plugin root directory and main plugin identity verified.
- **TU-134** Plugin header product/version identity agrees with trusted target policy.
- **TU-135** Unexpected executables/files outside package manifest policy rejected/diagnosed.
- **TU-136** Staged code never loaded/executed before trust/archive/compatibility gates complete.

### H. Compatibility, replacement, migration and recovery
- **TU-137** Free↔Pro exact pair compatibility checked against FP contract.
- **TU-138** Active module/schema compatibility preflight blocks unsupported target.
- **TU-139** Database migration Plan/recovery class determined before destructive schema step.
- **TU-140** Verified recovery point required for update class that can make irreversible schema changes.
- **TU-141** Concurrent update attempts serialized/fenced.
- **TU-142** Update vs Backup/Restore/Reset/destructive migration conflicts are blocked/coordinated.
- **TU-143** Crash before replacement leaves current package intact.
- **TU-144** Crash after old package move but before new commit recovers deterministically.
- **TU-145** Crash after new files commit but before activation state records reconciles observed filesystem/plugin state.
- **TU-146** PHP fatal on new package triggers safe recovery path without trusting arbitrary rollback target.
- **TU-147** Migration starts then fails: recovery truth distinguishes package rollback from schema/data rollback.
- **TU-148** New package works but schema migration pending is not reported fully updated.
- **TU-149** Post-update health covers bootstrap/admin/core safe path appropriate to profile.
- **TU-150** Successful activation invalidates relevant package/build/cache generations.
- **TU-151** Old package recovery target must itself remain authentic/compatible for recovery context.
- **TU-152** Emergency downgrade cannot overwrite newer irreversible schema without verified compatible restore strategy.

### I. Release provenance, CI/build and repository publication
- **TU-153** Exact target ZIP hash matches release artifact produced/recorded by BT/CI provenance.
- **TU-154** Repacked/re-zipped bytes with same source/version but different hash require separately signed target.
- **TU-155** Release metadata cannot point at unreviewed CI artifact by mutable job name alone.
- **TU-156** Stable target publication occurs only after required release gates/review class.
- **TU-157** Untrusted PR job cannot access production signing keys.
- **TU-158** Release service cannot access offline Root private keys.
- **TU-159** Signing/publishing retry is idempotent and does not create ambiguous same-version metadata.
- **TU-160** Failed repository publication does not announce partial release as available.
- **TU-161** TUF repository backup/restore preserves metadata versions/key trust without rollback.
- **TU-162** Repository compromise drill distinguishes online-role compromise from root/release-key compromise.
- **TU-163** Audit records target/repository/version/hash/key IDs safely without private material.
- **TU-164** Release revocation/withdrawal semantics do not rewrite historical trusted metadata deceptively.

### J. Multisite, privacy, observability and regression
- **TU-165** Network update state shared/owned at correct installation/network scope; subsite IDs cannot fork trusted Root state.
- **TU-166** Multisite site clone/delete does not duplicate/reset updater trust incorrectly.
- **TU-167** Support bundle excludes Account tokens, entitlement bearer material and private signing keys.
- **TU-168** Logs never contain private keys/package auth secrets; public key IDs/hashes allowed per policy.
- **TU-169** Error taxonomy distinguishes metadata-expired/signature/hash/compatibility/entitlement/network/staging/migration/health failures.
- **TU-170** Rate-limit/service outage errors cannot be misreported as signature/authenticity failure.
- **TU-171** 1k/10k target metadata repository resolution remains bounded.
- **TU-172** Large Root/key history rotation remains bounded and sequentially correct.
- **TU-173** Metadata/cache cold-start/offline/reconnect regression preserves trusted-state monotonicity.
- **TU-174** Adversarial corpus covers rollback/freeze/mix-match/key-role/path/archive/parser/cache/environment attacks with zero untrusted install.
- **TU-175** Upgrade across supported verifier/TUF spec patch versions preserves trusted state or enters explicit migration/recovery.
- **TU-176** Final certification pins exact TUF spec/verifier/repository/key-custody/expiry/product/channel/platform/build/CI/runtime profile; no generic “TUF secure” claim beyond executed evidence.

## 6. Independent certification classes

Future reports certify separately:
- `TU-V` verifier/spec/parser;
- `TU-K` key custody/rotation;
- `TU-M` metadata graph/freshness/rollback;
- `TU-T` target/channel/delegation resolution;
- `TU-P` package download/archive/staging;
- `TU-U` replacement/migration/recovery;
- `TU-R` release provenance/repository operations;
- `TU-S` Multisite/security/privacy/regression.

Passing one class does not promote another.

## 7. Stop-the-line gates

Automated Pro updates remain blocked if any tested profile:
- trusts expired/rolled-back/mix-and-match metadata;
- accepts target not linked through current trusted metadata graph;
- allows API/Account/CDN response to add Root trust;
- accepts target bytes without final trusted hash/length verification;
- executes staged code before trust/archive/compatibility gates;
- exposes production signing/root keys to untrusted CI/web request path;
- cannot recover safely from replacement/migration failure;
- permits subsite/lower-privilege actor to bypass native update authority;
- relies on weak signed JSON instead of a production-grade verifier meeting this contract.

## 8. Required future evidence report

Include exact TUF spec/verifier/library version, trusted Root bootstrap, role thresholds/public key IDs/custody architecture (never private material), expiry values, consistent snapshot/delegation profile, TU-01…TU-176 pass/fail/NA, conformance results where applicable, build/CI artifact provenance, archive/staging failure injection, compatibility/migration/recovery, Multisite/security findings and independent review class.

## 9. Current state

**TU fixtures documented: 176.**  
**TU fixtures executed: 0/176.**  
TUF verifier/repository/package/runtime certifications: **0**.

No TUF metadata/repository/signing key/target ZIP/updater client/package download/staging/replacement/migration/health test has been created or executed.

## 10. Development gate

Execution requires explicit owner consent under ADR-0014 and the Approval Ledger. If no production-grade verifier meets this protocol, automated Pro updates remain blocked rather than downgraded to weaker authenticity semantics.
