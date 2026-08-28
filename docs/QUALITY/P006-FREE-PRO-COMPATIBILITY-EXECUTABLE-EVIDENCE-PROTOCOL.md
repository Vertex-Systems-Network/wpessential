# WPEssential — P-006 Free ↔ Pro Compatibility / Boot Executable Evidence Protocol

Status: **Phase 0 fixed executable-evidence contract / execution NOT AUTHORIZED**  
Work package: `P0-M00-WP11`  
Related: ADR-0001, ADR-0007, ADR-0010, ADR-0014, ADR-0017, ADR-0042, ADR-0044, ADR-0054, ADR-0060, ADR-0069, ADR-0070, ADR-0072, ADR-0076, ADR-0091, ADR-0101, ADR-0102, P-001/CF, P-005/VT, P-007/CI, P-008/BT.

## 1. Purpose

Prove that independently distributed WPEssential Free and Pro artifacts can boot, update, degrade, recover and coexist without fatal errors, data loss, unsafe migrations or licensing confusion.

This protocol fixes future evidence before implementation. It does **not** create Free/Pro packages, install plugins, run WordPress, mutate a database, call the licensing service, verify a real entitlement, execute an updater or run migrations.

## 2. Truth boundaries that MUST remain separate

1. **Binary/package compatibility** — whether installed Free and Pro code can safely coexist.
2. **Platform API compatibility** — whether Pro's declared Platform API range matches the Free kernel contract.
3. **Schema compatibility** — whether code and durable data schema are at mutually supported generations.
4. **Product entitlement** — signed commercial feature rights; never inferred from package compatibility.
5. **Remote account/allocation state** — commercial service resources; never a substitute for signed entitlement.
6. **Membership authorization** — end-user/site access policy; never derived from WPE product licensing.
7. **Update trust** — package authenticity/freshness under the updater/TUF contract; separate from runtime compatibility.

A green result in one truth domain must not upgrade another domain.

## 3. Preserved architecture

- Free owns the platform/kernel and WordPress.org-compatible free surface.
- Pro is a separately distributed add-on that registers premium modules only after compatibility is established.
- Platform API version is separate from marketing/plugin version.
- Incompatible Pro must fail closed before premium service/module registration and before Pro-owned migrations.
- Free remains functional where technically possible when Pro is absent, disabled, expired or incompatible.
- Pro expiry preserves local data/configuration and safe deployed output according to ADR-0007.
- Service outage is not expiry.
- License state is not schema compatibility.
- Signed entitlement is not ordinary API JSON and editable local options cannot manufacture entitlement.
- Breaking Platform API changes require an intentional compatibility/deprecation window rather than surprise same-release removal.

## 4. Execution prerequisites

Before any FP fixture can run:

- explicit owner authorization under ADR-0014 covering P-006 execution;
- accepted executable compatibility floor or explicitly scoped temporary P-001 matrix evidence;
- authorized build/package path sufficient to produce immutable Free and Pro candidate artifacts;
- isolated disposable WordPress fixtures;
- recoverable database snapshots for migration/update cases;
- exact artifact hashes and version metadata recorded;
- remote licensing fixtures use sandbox/test resources only and only when separately authorized;
- no production account/license/provider credential may be used.

If a required capability is unavailable, mark the relevant fixture **NOT EXECUTED** or **INCONCLUSIVE**. Do not simulate success.

## 5. Fixed evidence matrix — FP-01…FP-144

### A. Artifact identity, metadata and contract declaration — FP-01…FP-12

- **FP-01** Free artifact plugin version, Platform API version and minimum environment metadata are machine-readable and internally consistent.
- **FP-02** Pro artifact plugin version, supported Free/Platform API range and Pro schema generation are machine-readable and internally consistent.
- **FP-03** marketing/plugin version changes without Platform API changes do not falsely imply an incompatible platform contract.
- **FP-04** Platform API change without matching declared compatibility metadata is rejected by release validation.
- **FP-05** Free artifact contains no Pro implementation/source/assets prohibited by the distribution contract.
- **FP-06** Pro artifact identifies its Free/platform dependency without treating WordPress dependency metadata as the sole runtime guard.
- **FP-07** package manifests/headers/Composer metadata agree on minimum PHP/WordPress requirements.
- **FP-08** artifact hashes uniquely identify the exact Free and Pro binaries under test.
- **FP-09** duplicate/copied plugin directory naming does not bypass identity/compatibility checks.
- **FP-10** malformed or missing compatibility metadata fails closed with an actionable diagnostic.
- **FP-11** unknown future metadata fields are handled according to versioning rules rather than causing unsafe permissive boot.
- **FP-12** artifact/package state can be reported without exposing license tokens, secrets or account-private data.

### B. Baseline boot combinations — FP-13…FP-28

- **FP-13** Free-only clean activation boots successfully on the accepted minimum environment.
- **FP-14** Free-only current/reference environment boots successfully.
- **FP-15** compatible Free + Pro activation in Free-first order boots premium modules only after compatibility passes.
- **FP-16** compatible Free + Pro activation in Pro-first order does not fatal; Pro remains inert/degraded until Free is available.
- **FP-17** Pro installed but inactive has no premium runtime side effects.
- **FP-18** Pro active while Free is missing does not fatal frontend/admin/REST/cron/CLI bootstrap.
- **FP-19** Pro active with unsupported-too-old Free fails closed before premium module registration.
- **FP-20** Pro active with unsupported-too-new Free fails closed before premium module registration.
- **FP-21** Free active with older Pro inside the supported overlap window boots the documented compatibility path.
- **FP-22** older Free with newer Pro inside a declared overlap window boots only supported functionality.
- **FP-23** unrelated Free CPT/Taxonomy functionality remains available during a Pro mismatch.
- **FP-24** a single incompatible premium adapter does not disable unrelated compatible premium modules when the contract permits per-module degradation.
- **FP-25** normal wp-admin request in mismatch state is non-fatal and presents exact remediation.
- **FP-26** public frontend request in mismatch state is non-fatal and does not leak internals.
- **FP-27** REST/Ability request in mismatch state does not load incompatible premium handlers.
- **FP-28** background/cron/CLI bootstrap in mismatch state does not load incompatible premium handlers.

### C. Preflight/load-order/autoload safety — FP-29…FP-44

- **FP-29** compatibility preflight executes before Pro code references unavailable Free classes/interfaces/functions.
- **FP-30** Free bootstrap does not eagerly reference Pro-only symbols.
- **FP-31** Pro bootstrap's minimal compatibility layer has no dependency on premium service container initialization.
- **FP-32** PHP opcode/autoload cache refresh after package replacement does not produce mixed-version fatal behavior in the supported deployment model.
- **FP-33** duplicate activation hooks do not run destructive work before compatibility is known.
- **FP-34** plugin load-order variation produces the same effective compatibility decision.
- **FP-35** network-activated Free + site-activated Pro is handled according to declared Multisite support rather than implicit current-blog assumptions.
- **FP-36** site-activated Free with network-activated Pro fails/degrades explicitly if unsupported.
- **FP-37** MU-plugin/host bootstrap ordering cannot trick Pro into treating an incomplete Free bootstrap as compatible.
- **FP-38** recovery-mode request can identify a Free/Pro mismatch without requiring incompatible premium code to boot.
- **FP-39** fatal-error protection/recovery UI does not erase or mutate Pro data merely because a mismatch is detected.
- **FP-40** cached compatibility decision is invalidated when either artifact version changes.
- **FP-41** stale object-cache/transient compatibility state cannot keep incompatible Pro modules loaded.
- **FP-42** local clock changes do not affect binary compatibility decisions.
- **FP-43** request concurrency while plugin files switch versions cannot authorize migrations from an unverified mixed state.
- **FP-44** compatibility preflight adds a bounded, measured boot cost and avoids remote network calls.

### D. Independent update order and interrupted deployment — FP-45…FP-60

- **FP-45** Free-first compatible update succeeds with old supported Pro during overlap.
- **FP-46** Pro-first compatible update succeeds with old supported Free during overlap.
- **FP-47** Free-first breaking update with incompatible old Pro safely degrades rather than fatals.
- **FP-48** Pro-first breaking update with incompatible old Free safely degrades rather than fatals.
- **FP-49** interrupted Free package replacement leaves a detectable non-runnable/incomplete state without starting Pro migration.
- **FP-50** interrupted Pro package replacement leaves Free usable and premium runtime disabled.
- **FP-51** partial filesystem replacement/missing Pro file fails safely and preserves data.
- **FP-52** partial filesystem replacement/missing Free platform file fails safely without premium mutation.
- **FP-53** update retry from the same artifact is idempotent at the compatibility layer.
- **FP-54** rollback Free to previous supported package restores the expected compatibility state without automatic destructive downgrade migration.
- **FP-55** rollback Pro to previous supported package restores the expected compatibility state where schema remains supported.
- **FP-56** rollback to code older than durable schema blocks unsafe runtime and reports recovery requirements.
- **FP-57** automatic updater order cannot assume Free and Pro update in one transaction.
- **FP-58** manual upload/replacement order gets the same compatibility guarantees as automated update.
- **FP-59** stale browser/admin request during update cannot trigger a migration using the prior compatibility decision.
- **FP-60** post-update health verification records exact Free/Pro artifact identities and effective state.

### E. Platform API ranges and deprecation windows — FP-61…FP-76

- **FP-61** exact-match Platform API range is accepted.
- **FP-62** inclusive minimum boundary is accepted.
- **FP-63** inclusive maximum boundary is accepted where range semantics allow it.
- **FP-64** below-minimum Platform API is rejected.
- **FP-65** above-maximum Platform API is rejected.
- **FP-66** malformed range syntax is rejected rather than treated as unrestricted.
- **FP-67** unknown Platform API major version fails closed.
- **FP-68** backward-compatible minor capability discovery does not require hard-coded marketing versions.
- **FP-69** optional platform capability missing causes scoped module degradation instead of global fatal when declared optional.
- **FP-70** required platform capability missing prevents only the dependent premium bootstrap path before mutation.
- **FP-71** deprecated API remains available for the declared overlap window.
- **FP-72** deprecation use is observable without exposing sensitive state.
- **FP-73** removal of deprecated API before its promised window fails release evidence.
- **FP-74** a Pro release that adopts the replacement API works before old API removal.
- **FP-75** Platform API capability negotiation is deterministic across admin/frontend/REST/CLI contexts.
- **FP-76** compatibility range semantics are documented and testable independently from the remote license service.

### F. Schema and migration compatibility — FP-77…FP-94

- **FP-77** compatible code + matching schema boots without migration.
- **FP-78** Free schema behind code executes only an authorized compatible Free migration path.
- **FP-79** Pro schema behind code executes only after Free/Pro binary compatibility passes.
- **FP-80** Pro schema migration does not use entitlement state as a substitute for binary/schema compatibility.
- **FP-81** incompatible Free/Pro pair starts no Pro destructive migration.
- **FP-82** schema ahead of current Pro code yields read-only/degraded/recovery state rather than unsafe downgrade writes.
- **FP-83** schema ahead of current Free code yields safe recovery behavior.
- **FP-84** interrupted migration resumes/reconciles according to its migration contract and does not rerun blindly.
- **FP-85** migration metadata written but data step failed is detected.
- **FP-86** data step committed but migration marker write failed is reconciled.
- **FP-87** concurrent requests do not execute the same migration unsafely.
- **FP-88** Free and Pro migration ordering is explicit when both need changes.
- **FP-89** a Pro migration cannot require a newer Platform API than the installed Free contract allows without preflight rejection.
- **FP-90** restore of older DB under newer code follows supported upgrade path only after package compatibility passes.
- **FP-91** restore of newer DB under older code blocks unsafe mutation.
- **FP-92** failed/rolled-back package update does not automatically roll back database state without a proven recovery plan.
- **FP-93** irreversible migration requires its declared Backup/restore boundary before release certification.
- **FP-94** migration logs/status contain no license tokens, Vault plaintext or unrelated user data.

### G. Entitlement, expiry and service-outage separation — FP-95…FP-112

- **FP-95** compatible Free+Pro with valid signed entitlement enables entitled premium management paths.
- **FP-96** compatible binaries with no entitlement do not manufacture premium edit rights.
- **FP-97** incompatible binaries with a valid entitlement remain binary-incompatible; entitlement cannot force boot.
- **FP-98** editable local option/cache manipulation cannot create a valid signed entitlement.
- **FP-99** ordinary authenticated licensing API JSON cannot substitute for signed entitlement verification.
- **FP-100** confirmed expiry preserves local data/configuration.
- **FP-101** confirmed expiry preserves safe deployed public output where ADR-0007 permits.
- **FP-102** confirmed expiry pauses/blocks premium mutating operations that require entitlement without deleting state.
- **FP-103** licensing service outage enters offline/service-unavailable semantics rather than immediate expiry.
- **FP-104** valid signed offline cache continues only within its accepted freshness/validity rules.
- **FP-105** stale/rollback entitlement is rejected under anti-rollback rules.
- **FP-106** revoked entitlement cannot be treated as a binary mismatch and does not trigger package/schema mutation.
- **FP-107** license state change mid-request is handled at defined authorization/mutation boundaries and does not create half-authorized writes.
- **FP-108** license renewal does not auto-run pending destructive migration until normal compatibility/migration gates pass.
- **FP-109** disconnecting account/OAuth does not delete local definitions or alter binary compatibility.
- **FP-110** Pro deactivation/reactivation preserves data and reevaluates compatibility/entitlement from authoritative sources.
- **FP-111** product entitlement never grants or revokes end-user Membership authorization by itself.
- **FP-112** licensing diagnostics distinguish Binary Compatibility, Platform API, Schema, Entitlement and Service state separately.

### H. Multisite, allocation, clone, migration and restore — FP-113…FP-128

- **FP-113** network Free/Pro compatibility decision is explicit and does not assume one blog's state represents the network.
- **FP-114** site-scoped commercial allocation remains separate from package compatibility.
- **FP-115** linking a network/account does not silently allocate every child site.
- **FP-116** valid network package compatibility cannot grant unallocated child-site product entitlement.
- **FP-117** child-site allocation cannot make an incompatible network Pro binary compatible.
- **FP-118** cloned production DB with copied compatible binaries enters clone/revalidation semantics rather than creating a second paid production activation silently.
- **FP-119** approved staging clone preserves safe deployed runtime while outbound/premium mutations follow environment/entitlement policy.
- **FP-120** domain change alone does not create a package compatibility change.
- **FP-121** site/network transfer does not change Platform API compatibility semantics.
- **FP-122** restored signed entitlement bound to another installation/site is rejected/revalidated without damaging local data.
- **FP-123** restored DB with mismatched Free/Pro packages follows binary/schema recovery before remote allocation mutation.
- **FP-124** numeric blog ID reuse cannot inherit a prior site's commercial allocation solely by ID.
- **FP-125** site deletion/recreation does not automatically erase retained commercial/audit history or create entitlement.
- **FP-126** remote allocation timeout/unknown outcome does not result in repeated seat consumption with fresh mutation identity.
- **FP-127** local persistence failure after successful remote allocation is reconcilable without duplicate allocation.
- **FP-128** Multisite mismatch/recovery notices reveal no other site's/account's private commercial data.

### I. Security, UX, observability and adversarial recovery — FP-129…FP-144

- **FP-129** compatibility diagnostics require appropriate admin/network authority and expose only safe metadata.
- **FP-130** unauthenticated/public users cannot infer private license/account/allocation details from mismatch responses.
- **FP-131** compatibility/admin notice content is escaped and resists stored/reflected XSS through version/error metadata.
- **FP-132** crafted plugin/header/version strings cannot bypass range parsing or inject UI/log content.
- **FP-133** mismatch state performs no remote call merely to keep frontend rendering alive.
- **FP-134** compatibility state contains no Vault plaintext, OAuth tokens, signed private artifacts or provider secrets.
- **FP-135** audit records distinguish package mismatch, entitlement expiry, service outage and migration block.
- **FP-136** mismatch notice gives exact safe recovery action without global wp-admin hijacking.
- **FP-137** no unsupported pair is silently labeled supported because both plugin marketing versions are equal.
- **FP-138** no supported pair is rejected solely because plugin marketing versions differ when Platform API/schema ranges are compatible.
- **FP-139** uninstall/deactivation behavior does not delete shared/user data unless an explicit destructive uninstall policy is separately authorized.
- **FP-140** crash/recovery after compatibility state persistence but before module registration re-evaluates truth safely on next request.
- **FP-141** fuzzed compatibility/range/schema metadata cannot produce permissive boot on parse failure.
- **FP-142** representative high-concurrency boot does not create a thundering-herd remote license check or migration race.
- **FP-143** final package-pair certification records exact artifact hashes, environment, Platform API range, schema generations and entitlement fixture class.
- **FP-144** independent final adversarial review confirms no known mismatch/expiry/outage/restore path can cause fatal boot, silent entitlement escalation, destructive unverified migration or user-data deletion.

## 6. Required result dimensions

Each executed fixture records:

- fixture ID;
- exact Free artifact hash/version/Platform API;
- exact Pro artifact hash/version/declared range/schema generation;
- WordPress/PHP/database/site-mode environment;
- activation/update order;
- schema state before/after;
- entitlement fixture class when applicable, never secret/token values;
- expected vs actual boot/module/migration behavior;
- HTTP/admin/CLI/cron context where applicable;
- logs/audit result with redaction check;
- data-integrity result;
- recovery result;
- PASS / FAIL / INCONCLUSIVE / NOT EXECUTED;
- linked defect/evidence artifact.

## 7. Stop-the-line failures

Stop P-006 certification immediately on any confirmed case of:

- fatal public/admin/REST/cron/CLI boot caused by a known mismatch state that should degrade safely;
- incompatible Pro module/service registration before compatibility verification;
- Pro migration starts while binary/platform compatibility is unverified or failed;
- local option/API JSON forges signed product entitlement;
- service outage treated as confirmed expiry contrary to accepted freshness rules;
- confirmed expiry deletes local user/site data or destroys safe deployed output contrary to ADR-0007;
- product licensing bypasses Membership authorization;
- restore/clone silently creates an unauthorized second production allocation;
- rollback/downgrade writes against unsupported newer schema;
- secrets/tokens/private signed artifacts appear in UI/log/support evidence;
- an unknown parse/error state defaults to permissive premium boot.

## 8. Certification rule

P-006 can become runtime-certified only when all mandatory FP fixtures applicable to the selected distribution/runtime profile pass across the accepted compatibility matrix and all required cross-gates also pass.

A passing P-006 does **not** certify:
- P-001 compatibility floor;
- P-005 Vault;
- P-007 CI;
- P-008 build;
- OAuth Account Link;
- Product License remote service implementation;
- TUF/Pro updater;
- Membership;
- any provider adapter.

Those keep their own evidence/certification states.

## 9. Current evidence state

- FP fixtures documented: **144**
- FP fixtures executed: **0/144**
- P-006 runtime certifications: **0**
- Free/Pro certified artifact pairs: **0**
- remote Product License service executions under this protocol: **0**
- migrations executed under this protocol: **0**

## 10. Development gate

This protocol authorizes **no** Free/Pro source code, bootstrap, plugin header/package, Composer/npm dependency, build, activation/deactivation, migration, database mutation, licensing API call, entitlement verification/signing, update, rollback, restore, CI workflow or runtime test.

ADR-0014 explicit scoped owner consent remains mandatory before any executable P-006 work.