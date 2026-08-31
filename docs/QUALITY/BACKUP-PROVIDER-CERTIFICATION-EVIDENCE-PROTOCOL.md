# WPEssential — Backup Provider Certification Executable Evidence Protocol

Status: **Planning only / NOT AUTHORIZED FOR EXECUTION**  
Extends: P-013 in `CONSENT-GATED-TECHNICAL-SPIKE-PROTOCOLS.md`  
Related: ADR-0053, ADR-0056, ADR-0061, ADR-0064, ADR-0065, ADR-0084, ADR-0130, Backup Provider Certification Contract, Backup Provider Family & Capability Registry, Backup Remote Copy Lifecycle.

## 1. Purpose

Define the exact executable evidence required before a Backup destination/profile can move through C0/C1/C2/C3/C4.

This document preserves the existing C0–C4 certification ladder and V3 restore-tested semantics while replacing the earlier unnumbered evidence sketch with fixed executable fixture IDs **BPC-F001…BPC-F176**.

Current truth:
- provider targets: **34**;
- BPC-F documented: **176**;
- BPC-F executed: **0/176**;
- C-certified provider profiles: **0**;
- normal C3 Supported Backup Destination profiles: **0**;
- C4 Disaster Restore Certified profiles: **0**;
- V3 restore-tested provider profiles: **0**.

This file defines future tests only. It does not authorize credentials, provider accounts, API calls, SDK/client installation, uploads, downloads, deletes, restores, server changes, encryption/key operations, benchmarks or cost-incurring tests.

## 2. Hard truth boundaries

The following MUST remain separate:
- provider logo/catalog presence **≠** compatibility;
- static SE0–SE3 evidence **≠** C0 runtime certification;
- OAuth/credential connection success **≠** Backup support;
- one successful upload **≠** restorable Backup;
- C1 Upload Certified **≠** normal Supported Backup Destination;
- C2 integrity/resume proof **≠** restore proof;
- `remote_committed` **≠** `remote_verified`;
- provider object visibility **≠** WPE manifest/integrity verification;
- provider ETag/checksum **≠** WPE manifest digest unless exact profile semantics prove the relationship;
- V2 Remote Verified **≠** V3 Restore Tested;
- C3 Restore Certified **≠** C4 disaster recovery;
- provider delete request accepted **≠** bytes definitely gone;
- JobService at-least-once execution **≠** exactly-once provider mutation;
- Backup-provider certification **≠** Membership protected-file delivery certification.

Normal marketing/UI label **Supported Backup Destination** requires C3 for the exact provider/family/API/client/adapter/environment profile. C4 is required for `Disaster Restore Certified`.

## 3. Certification identity

Every future certification run records:
- WPE version/commit;
- adapter version;
- canonical `family_key`;
- provider key and provider profile version;
- provider API/schema/version;
- client/SDK/transport library and version;
- region/cloud/national-cloud variant;
- endpoint identity and custom-endpoint trust mode;
- auth method/scopes/tenant/account identity;
- WordPress/PHP/DB/runtime matrix identity;
- JobService/backend profile where asynchronous work is exercised;
- encryption profile and recovery-key-slot profile;
- object/part size classes;
- Multisite/site ownership profile;
- provider retention/versioning/object-lock/lifecycle configuration;
- test date and known provider limits;
- fixture generator version.

A certification cannot be silently reused for a materially different provider/API/client/adapter/server/storage profile.

Numeric planning-era `PF-xx` aliases are never certification identities. Canonical `bf.*` family keys are required.

## 4. C0–C4 certification ladder — preserved

### C0 — Detected / Connectable

Proves provider/profile detection, safe authentication/credential validation and destination namespace inspection only.

Marketing: Experimental/Connected only. **Not Backup Supported.**

### C1 — Upload Certified

Adds small WPE artifact upload/read-back/identity/error/delete evidence.

Still not enough for large-site or restore support.

### C2 — Resumable & Integrity Certified

Adds interrupted/crash-resumable transfer where the exact family supports it, finalization/reconciliation, WPE integrity/read-back and retry/idempotency evidence.

A non-resumable family may earn `C2 Integrity Certified / Non-resumable` only with explicit UI/documentation limitation.

### C3 — Restore Certified

Minimum normal **Supported Backup Destination** gate. Requires an actual complete remote Backup Set restore where local source artifacts are unavailable/ignored and the restore path consumes the remote copy.

### C4 — Disaster Restore Certified

Requires a V3 fresh-environment disaster-recovery scenario with original application runtime state unavailable except the documented independent recovery material.

## 5. Size classes

Every applicable profile covers:
- empty/minimal artifact;
- small single-request artifact;
- just below large-transfer/multipart threshold;
- just above threshold;
- medium multipart/chunked artifact;
- realistic large Backup artifact;
- boundary part/chunk sizes;
- provider maximum-limit behavior when safe/practical.

Exact bytes are execution-profile data, not universal planning constants.

# Fixed executable evidence protocol — BPC-F001…BPC-F176

The following 16 groups contain 11 ordered fixtures each, totaling **176**. Within each group the listed scenarios map sequentially to the group's ID range.

No fixture is passed by static documentation, code presence, provider marketing, connection success, upload success or a local-only restore. Runtime execution requires explicit scoped owner consent under ADR-0014.

## Group 1 — Certification identity / family / version binding — BPC-F001…BPC-F011

1. exact canonical `family_key + provider_key + provider_profile_version + adapter_version` recorded;
2. ambiguous legacy PF identifier rejected rather than guessed;
3. provider/API/client-library version captured and supported range evaluated;
4. region/national-cloud/tenant variant captured;
5. endpoint identity/custom-endpoint trust policy captured;
6. auth method/scopes/account identity captured without secret exposure;
7. capability descriptor matches exact provider profile rather than family optimism;
8. newer unverified provider/API/client version becomes unverified, not silently Supported;
9. known incompatible/security-blocked version cannot retain certification;
10. test/live/staging environment identity cannot cross-certify another environment;
11. static SE0–SE3 maturity remains paper evidence and cannot create C0.

## Group 2 — C0 connect/auth/Vault/namespace safety — BPC-F012…BPC-F022

1. valid least-privilege credential validates non-destructively where provider allows;
2. wrong credential normalized safely;
3. revoked/expired credential normalized safely;
4. insufficient write/read/delete/list scope detected distinctly;
5. Vault reference is used instead of plaintext persisted credential;
6. secrets/preauthenticated session URLs absent from logs/UI/error payloads;
7. TLS/certificate validation cannot be bypassed to obtain a pass;
8. SFTP/SSH host-key mismatch fails closed where applicable;
9. custom endpoint cannot reach private/link-local/cloud-metadata destinations under trust policy;
10. intended bucket/container/folder/drive/path resolves to exact account/tenant namespace;
11. cross-site or lower-capability admin cannot inspect another destination's credentials/namespace.

## Group 3 — C1 small upload / read-back / delete / names — BPC-F023…BPC-F033

1. small WPE artifact uploads to intended staging/final namespace according to profile;
2. exact remote object/file identity captured;
3. remote byte size matches expected stored bytes;
4. list/head/metadata discovers intended object without false duplicate identity;
5. full read-back verifies WPE stored-byte hash;
6. Unicode filename/object-key behavior is deterministic;
7. long/path-boundary name behavior handled safely;
8. overwrite/conflict behavior is deterministic and explicit;
9. quota/storage-full error normalized without corrupt local state;
10. cleanup/delete result is represented according to actual provider semantics;
11. C1 result remains Upload Certified only and cannot display Supported Backup Destination.

## Group 4 — Large transfer / multipart / resumable session — BPC-F034…BPC-F044

1. threshold selection uses exact provider/profile capability and limits;
2. multipart/resumable session identity persists durably enough for crash recovery;
3. interruption after multiple non-final parts resumes in a new process/request;
4. provider-reported offset/part state is reconciled before resume;
5. corrupted local offset/state cannot cause silent duplicate/corrupt remote bytes;
6. one part/chunk retry preserves exact final bytes;
7. session/upload-ID expiry transitions to explicit recovery/restart state;
8. orphan multipart/session abort/cleanup semantics verified;
9. parallel/out-of-order parts used only where exact profile certifies them;
10. non-resumable family safely restarts and advertises `Non-resumable` limitation;
11. family/provider limit override is honored rather than inheriting another provider's part-count/size rules.

## Group 5 — JobService at-least-once / retry / duplicate mutation — BPC-F045…BPC-F055

1. duplicate upload Job does not create ambiguous duplicate final Backup Set;
2. worker death after remote part write but before local persistence reconciles provider state;
3. worker death before remote write retries safely;
4. response lost after request may have succeeded becomes unknown/reconcile, not blind repeat;
5. retry policy distinguishes safe read/list from potentially duplicating create/finalize/delete;
6. provider idempotency token is used only where exact API certifies semantics;
7. retry budget/backoff handles provider 429 correctly;
8. retry budget handles 5xx/timeout without infinite queue churn;
9. cancellation while upload active reaches truthful remote/local state;
10. resource/concurrency key prevents unsafe concurrent mutation of same Remote Copy;
11. at-least-once Job execution is never represented as exactly-once provider behavior.

## Group 6 — Finalize / commit ambiguity / reconciliation — BPC-F056…BPC-F066

1. commit/finalize definitely not sent remains uncommitted;
2. explicit successful commit captures canonical final identity;
3. explicit terminal commit failure remains non-committed;
4. response loss around commit enters `commit_unknown`;
5. duplicate finalize request reconciles rather than inventing second successful copy;
6. final remote resource exists but local state was not updated and is recovered by reconciliation;
7. local state claims committed but remote object absent becomes degraded/missing;
8. staging parts/object cannot be mistaken for complete Backup Set;
9. provider-specific commit point is explicit for family/profile;
10. final manifest/completion marker publishes last and is read back;
11. `remote_committed` cannot advance to `remote_verified` without integrity/inventory verification.

## Group 7 — Integrity / manifest / encryption / corruption — BPC-F067…BPC-F077

1. WPE stored-part digest verifies exact remote bytes;
2. provider checksum is interpreted by exact documented algorithm/profile;
3. multipart/object ETag is not assumed to be whole-object MD5 without proof;
4. remote size mismatch fails verification;
5. one-byte/bit corruption is detected;
6. missing required part is detected;
7. duplicate/wrong part identity is detected;
8. stale manifest or wrong object version is rejected;
9. encrypted stream truncation/reordering fails authentication/integrity;
10. wrong recovery key/key slot fails safely without destructive fallback;
11. manifest relationship verifies exact required part IDs/sizes/hashes/profile before `remote_verified`.

## Group 8 — Remote read / range / retrieval / provider state — BPC-F078…BPC-F088

1. full remote read retrieves exact committed object;
2. range read works only where advertised and bounds are correct;
3. interrupted download resumes/retries according to exact provider capability;
4. credential refresh during long read succeeds where supported;
5. provider archive/cold-storage state is detected rather than treated as missing;
6. rehydration-required state produces truthful workflow;
7. object version/delete-marker semantics select exact intended recovery object;
8. eventual/strong consistency assumptions match exact provider profile;
9. list omission cannot alone prove object deletion when stronger identity lookup exists;
10. wrong object returned under same display filename is rejected by identity/hash;
11. remote retrieval never falls back silently to local source artifact while claiming remote evidence.

## Group 9 — C3 Restore Certified / normal Supported gate — BPC-F089…BPC-F099

1. complete Backup Set is produced and copied to candidate provider;
2. local source artifacts are removed/ignored so restore must use remote copy;
3. final manifest is discovered by stored remote identity and validated;
4. every required remote part is preflighted before destructive restore stage;
5. encrypted profile decrypts/authenticates through certified recovery path;
6. interrupted remote download/restore resumes or recovers truthfully;
7. selected/full restore scope completes from remote copy;
8. post-restore health checks pass against expected fixture truth;
9. missing/corrupt remote part fails before falsely declaring restored;
10. cleanup/retention after restore does not destroy required verified recovery copy;
11. only after all applicable C0–C3 evidence passes may exact profile receive `Supported Backup Destination`.

## Group 10 — C4 / V3 disaster restore / independent recovery — BPC-F100…BPC-F110

1. fresh supported WordPress/PHP/DB environment starts without original runtime DB/state;
2. provider connection is reconstructed from documented independent recovery procedure;
3. encrypted Backup recovery material is available independently of lost runtime state;
4. remote Backup Set can be discovered/selected using documented recovery catalog or provider identity;
5. no hidden tester-only local path/object reference is required;
6. complete remote restore executes in fresh environment;
7. domain/path/environment remap works where in scope;
8. Free/Pro/schema/version compatibility safety is evaluated;
9. post-restore application and representative module health is verified;
10. fresh operator/automation can follow documented runbook without hidden knowledge;
11. only successful repeatable V3 scenario permits C4 `Disaster Restore Certified` claim.

## Group 11 — Retention / prune / delete / versioning / object lock — BPC-F111…BPC-F121

1. hard-delete provider semantics verified where supported;
2. trash/recycle result is labeled as trash, not confirmed deletion;
3. versioning/delete-marker behavior is represented truthfully;
4. object-lock/retention refusal becomes `retention_locked`/equivalent;
5. async delete acceptance remains pending until verified;
6. lost delete response becomes `delete_unverified` and reconciles;
7. provider lifecycle external deletion is detected as remote degradation;
8. prune never removes only known-good required recovery copy;
9. newer but unverified Backup cannot justify deleting last verified recovery point;
10. copy used by active restore/reset cannot be pruned unsafely;
11. pinned/legal/retention-protected recovery point survives automated prune.

## Group 12 — Auth lifecycle / endpoint security / secret handling — BPC-F122…BPC-F132

1. access-token refresh/rotation during active operation follows exact provider semantics;
2. refresh-token expiry/revoke becomes credentials-required without secret leakage;
3. credential rotation during multipart/resumable upload reconciles safely;
4. least-privilege scopes are sufficient for advertised capability and no broader claims are made;
5. redirect to untrusted host cannot receive credential;
6. signed/preauthenticated upload/session URL is treated as secret and redacted;
7. provider error body cannot inject executable wp-admin HTML/JS;
8. path/object-key manipulation cannot escape destination namespace;
9. SSH host-key change requires explicit trust handling and cannot auto-accept silently;
10. invalid TLS certificate fails closed;
11. destination credentials/operations remain site/network capability and ownership scoped.

## Group 13 — Provider limits / quota / rate / backpressure / resource cost — BPC-F133…BPC-F143

1. exact min/max part/chunk limits enforced;
2. exact max part-count/object-size limit enforced;
3. provider-specific deviations override generic family defaults;
4. quota exhaustion produces bounded failure/recovery state;
5. provider rate-limit headers/codes drive bounded backoff;
6. prolonged outage does not cause unbounded Job/temporary-file growth;
7. local temporary disk-full fails safely and cleans/reconciles;
8. peak PHP memory remains within declared safety ceiling;
9. client-side encryption buffer/CPU/resource cost recorded;
10. API request/cost-call count is measured where operationally meaningful;
11. throughput is informational and never substitutes for restore/integrity certification.

## Group 14 — Multisite / Site Lifecycle / clone / environment ownership — BPC-F144…BPC-F154

1. site-owned destination cannot read/write another site's prefix/object identity;
2. network-shared destination requires explicit network ownership and target policy;
3. current-blog context cannot substitute for durable ownership field;
4. same provider object key/filename collision across sites cannot cross-bind Backup Set;
5. site clone/staging cannot continue production upload/delete schedule blindly;
6. clone environment cannot reuse production credentials/namespace without explicit policy;
7. site transfer/domain change preserves Backup ownership identity independently of hostname;
8. site deletion/uninitialize handles remote copies according to retained policy without deleting another site's data;
9. archived/suspended site backup/restore behavior follows explicit lifecycle policy;
10. network Backup/Restore includes only intended site/network scopes;
11. restored cloned site revalidates provider/version/credentials before mutating remote copies.

## Group 15 — Privacy / Audit / Error / observability / version drift — BPC-F155…BPC-F165

1. backup/provider logs contain safe correlation and state but no secret/session URL;
2. provider account/object metadata retention follows privacy classification;
3. exported diagnostics redact credentials/private identifiers as required;
4. local erase, remote delete and provider retention are reported as distinct truths;
5. unknown provider outcome is labeled unknown rather than success/failure guess;
6. Error taxonomy distinguishes auth/quota/rate/integrity/missing/retention/provider/network/recovery classes;
7. Audit records destructive restore/delete/prune actor/action without becoming Backup state authority;
8. health checks expose stale certification/provider-version drift;
9. serious provider security/regression event can move profile to blocked/unverified state;
10. recertification trigger records exact affected capability/profile rather than brand-wide permanent badge;
11. certification report records Pass/Fail/Not Run, limitations and evidence without secrets.

## Group 16 — Family/provider deviations / 34-target registry / scale — BPC-F166…BPC-F176

1. `bf.browser-export` is never counted as durable remote C3 destination merely because download succeeded;
2. generic WebDAV does not claim crash-resume without provider extension certification;
3. SFTP requires exact client/server offset/temp→rename/host-key evidence;
4. generic `bf.s3` provider inherits no AWS-only checksum/versioning/part-limit capability without profile proof;
5. GCS/Drive/Graph resumable or preauthenticated session URI secrecy/lifecycle is proven;
6. provider-native `bf.native` inherits no family capability automatically;
7. all 34 catalog targets remain Planned/Not Certified until their exact profile evidence executes;
8. 100 concurrent transfer Jobs preserve destination/resource/idempotency boundaries;
9. 1,000 queued/provider-throttled operations demonstrate bounded backpressure/fairness without corrupting Remote Copy truth;
10. large Backup restore from candidate provider remains integrity-first under latency/retries;
11. final C-level is awarded only to the exact family/provider/API/client/adapter/environment profile whose required fixtures passed.

## 6. Certification mapping rules

- **C0** requires applicable identity + connect/auth/Vault/namespace evidence. It is not a support claim.
- **C1** additionally requires small upload/read-back/identity/error/delete evidence.
- **C2** additionally requires applicable large-transfer/retry/finalization/integrity evidence; non-resumable profiles must be labeled explicitly.
- **C3** additionally requires a complete remote restore that does not use local source artifacts and passes post-restore health. C3 is the minimum normal Supported Backup Destination gate.
- **C4** additionally requires a repeatable V3 fresh-environment disaster restore with independently recoverable provider/encryption material and documented procedure.
- A profile cannot skip lower certification levels through one high-level happy path.
- `remote_verified`/V2 is required evidence for the selected remote copy before C3 restore certification but is not itself C3.
- Provider profile/version/environment is part of certification identity.

## 7. Stop-the-line conditions for future execution

Immediately stop and mark the affected certification failed/unverified if any evidence shows:
- secret/session/preauthenticated URL leakage;
- TLS/host-key verification bypass required;
- custom endpoint SSRF/private/link-local/cloud-metadata access;
- cross-site/network destination access leakage;
- remote bytes differ from WPE manifest expectation without detection;
- incomplete/staging copy accepted as complete Backup;
- `commit_unknown` guessed as success without reconciliation;
- restore test silently uses local source artifacts while claiming remote restore;
- deletion/prune removes the only required verified recovery point;
- fresh-env disaster restore requires undocumented hidden local state;
- provider/API/client version outside certification scope is marketed as Supported automatically.

## 8. Certification artifact

A completed future certification emits a durable report containing:
- work/fixture IDs and date;
- provider/family/profile/API/client/adapter identity;
- endpoint/region/environment scope;
- C-level achieved and V-level evidence reached;
- capabilities certified/unsupported/non-resumable;
- test matrix Pass/Fail/Not Run;
- WordPress/PHP/DB/JobService/environment identity;
- encryption/recovery profile;
- provider limit/resource/performance observations;
- evidence references/log/artifact IDs;
- security/privacy findings;
- known limitations;
- certification expiry/review triggers;
- reviewer class;
- exact UI/marketing label permitted.

No credential, secret, private key, refresh token, session URL or preauthenticated URL may appear in the report.

## 9. Re-certification triggers

Re-run affected evidence after:
- provider API/schema/region behavior change;
- authentication mechanism/scopes change;
- SDK/client/transport major upgrade;
- WPE provider adapter change;
- WPE manifest/container/crypto change;
- JobService retry/claim semantics change affecting transfer;
- provider multipart/session/checksum/finalization change;
- provider retention/versioning/delete/object-lock change;
- serious provider regression/security incident;
- minimum WordPress/PHP/platform change affecting adapter;
- long certification age according maintenance policy.

## 10. Runtime state / development gate

**NOT EXECUTED.** BPC-F executed **0/176**. C-certified profiles **0**. C3 Supported profiles **0**. V3 profiles **0**.

No provider credential validation, connection, SDK install, network request, upload, multipart session, download, delete, encryption/recovery operation, Backup/Restore, server mutation or benchmark is authorized until explicit scoped owner development consent is recorded under ADR-0014.