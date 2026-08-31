# WPEssential — Import / Export Executable Evidence Protocol

Status: **Phase 0 consent-gated protocol / 0 fixtures executed**  
Date: 2026-08-28  
Work package refinement: `P0-M00-WP39`  
Related: ADR-0041, ADR-0095, ADR-0116, VER, MLC, DSR, FST, REL, QRY, CTB, CBP, CLG, DVR, CAC, PDL, ERR, KPA, JobService, Backup, Safe HTTP, Multisite, ADR-0014.

## 1. Purpose

Define executable evidence required before Import / Export can claim safe package parsing, planning, mapping, dependency closure, resumability, identity preservation, crash reconciliation, rollback truth, media handling, privacy, version migration, Multisite or scale support.

The original **IM-01…IM-56** semantics are preserved. This canonical refinement extends the fixed matrix to **IM-01…IM-176**.

Current execution truth: **0/176 executed**.

The execution invariant remains:

**reviewed Plan/Dry Run + source/package fingerprint + trusted target scope are pinned before execution; mutable Run/Checkpoint/Identity Map/Journal are separate runtime truth; Job delivery never proves a target mutation did or did not happen.**

Shared VER/DSR/FST/REL/CTB/CAC/KPA/PDL evidence never auto-certifies an Import/Export profile.

## 2. Runtime certification profile

Future certification records:
- WordPress/PHP/database versions;
- IR1/PT-D and/or IR2/PT-E runtime profile;
- source adapter/version and source format;
- package/manifest/schema versions;
- target Data Source/Definition/schema generations;
- Field/Relation/Query/Custom-Table/Blueprint adapter versions where present;
- JobService/backend profile;
- private temp/artifact storage profile;
- media/offload/Safe HTTP/Connection profile where applicable;
- Backup/restore prerequisite class;
- CAC cache profile/invalidation generation where applicable;
- privacy/retention/audit profile;
- single-site/Multisite topology;
- rollback coverage/retention profile.

Certification is scoped to the recorded profile. Unknown future package/source/adapter versions are never silently certified.

# 3. Original Plan/source/artifact fixtures — IM-01…IM-10
- **IM-01** — reviewed Dry Run/Plan pins immutable mapping/config and current source fingerprint before execution.
- **IM-02** — active Run continues against pinned Plan revision; editor changes do not mutate semantics silently.
- **IM-03** — material source fingerprint drift blocks or requires explicit re-plan/re-review.
- **IM-04** — unavailable source fingerprint is reported as weaker evidence, never “verified unchanged”.
- **IM-05** — uploaded/import source stages in private bounded storage, not executable/public plugin/theme path.
- **IM-06** — `../`, absolute paths and equivalent traversal cannot escape extraction root.
- **IM-07** — symlink/hardlink archive semantics cannot escape approved staging policy.
- **IM-08** — archive expanded bytes/file count/depth/compression ratio are bounded before extraction exhaustion.
- **IM-09** — unsupported/corrupt source fails safely with truthful Run/target state.
- **IM-10** — file extension alone does not establish parser trust; actual format/profile validation applies.

# 4. Original mapping/authorization/identity fixtures — IM-11…IM-22
- **IM-11** — source fields map only to registered target fields/relations/actions in reviewed Plan.
- **IM-12** — source/request data cannot choose arbitrary table/column/class/Ability/direct SQL primitive.
- **IM-13** — import cannot bypass owning Data Source/domain validation/Policy/integrity for speed.
- **IM-14** — source numeric/site IDs cannot change trusted target site/network scope.
- **IM-15** — stable source identity resolves intended target lineage rather than numeric coincidence.
- **IM-16** — matched preexisting target is distinct from import-created target and update policy is explicit.
- **IM-17** — concurrent same-source Runs cannot silently create duplicate owned targets.
- **IM-18** — forward/out-of-order references enter bounded unresolved state and reconcile deterministically.
- **IM-19** — unsupported reference becomes explicit conflict/skip/error, never guessed by label similarity.
- **IM-20** — local admin edit after prior import is preserved/conflicted when update-if-unchanged precondition fails.
- **IM-21** — source omission/deletion never deletes target without explicit high-risk sync-delete semantics.
- **IM-22** — password/token/Vault/provider credentials are excluded or delegated; never generic Journal/log/export plaintext.

# 5. Original checkpoint/crash/Job fixtures — IM-23…IM-34
- **IM-23** — checkpoint advances only after target changes and required Identity Map state are durably reconciled.
- **IM-24** — crash before target mutation retries without phantom success.
- **IM-25** — crash after target commit before Identity Map detects/adopts valid committed target instead of duplicate.
- **IM-26** — crash after Identity Map before Checkpoint does not repeat completed mutation.
- **IM-27** — crash after Checkpoint before continuation enqueue remains discoverable/reconcilable.
- **IM-28** — duplicate Job delivery cannot duplicate valid target mutation under certified identity/idempotency contract.
- **IM-29** — expired Job lease does not imply target mutation failed; worker ownership/reconciliation remains explicit.
- **IM-30** — pause stops new work at safe checkpoint and does not claim rollback.
- **IM-31** — resume revalidates Run/source/target assumptions and continues from last committed checkpoint.
- **IM-32** — cancel stops future work according to safe boundary and reports already committed effects.
- **IM-33** — continuation enqueue failure leaves Run/checkpoint discoverable/reconcilable.
- **IM-34** — site lifecycle drain prevents unsafe new chunks and reconciles active Run.

# 6. Original rollback/recovery fixtures — IM-35…IM-42
- **IM-35** — R0 UI/report never promises rollback for irreversible/unsupported effects.
- **IM-36** — R1 removes only safely import-owned created records; later edits/dependencies block unsafe delete.
- **IM-37** — R2 mapped-field/relation reversal uses expected post-import fingerprint and preserves newer unrelated edits.
- **IM-38** — R3 is claimed only for specifically certified bounded transactional reversal.
- **IM-39** — mixed rollback reports unresolved/conflicted/external effects, never false “fully rolled back”.
- **IM-40** — broad/high-risk import verifies independent Backup prerequisite; Journal is not disaster backup.
- **IM-41** — restored active/queued Run becomes revalidation/reconciliation-required; copied Jobs never auto-resume blindly.
- **IM-42** — restored Identity Map references validate against restored target identities/fingerprints before reuse.

# 7. Original media/export/privacy fixtures — IM-43…IM-50
- **IM-43** — remote media uses Safe HTTP/Connection policy for scheme/host/size/time/privacy; no SSRF/local-file access.
- **IM-44** — required media failure does not falsely mark parent item fully successful.
- **IM-45** — offloaded media is current only after certified upload/commit; credentials never reach client/log/export.
- **IM-46** — configuration export contains portable config/dependencies/versioning without runtime Run secrets/state by default.
- **IM-47** — data export reauthorizes rows/fields and cannot leak inaccessible records through counts/relations/media.
- **IM-48** — secrets/credentials/protected fields are excluded/redacted/placeholdered according to classification.
- **IM-49** — subsite export cannot include another site's runtime/map/data/network secrets.
- **IM-50** — missing/incompatible Definition/module/provider dependency is explicit conflict/degraded Plan.

# 8. Original scale/topology fixtures — IM-51…IM-56
- **IM-51** — controlled 100k/1M records measure throughput/memory/checkpoints/write amplification/map/index/temp storage without weakening correctness.
- **IM-52** — high fan-out references/media remain bounded/resumable with explicit failed/conflict counts.
- **IM-53** — IR1 noisy-neighbor Multisite cannot create wrong-site rows and uses Job fairness/backpressure.
- **IM-54** — IR2 per-site table profile proves provisioning/migration/version-skew/site-delete behavior.
- **IM-55** — 100/1k/10k-site fixture proves Run/Checkpoint/Map/Journal scope-safe identity/indexes.
- **IM-56** — temp artifacts/item details/Journal/Map have separate retention; cleanup preserves required lineage/recovery state.

# 9. Portable package manifest, trust and integrity — IM-57…IM-72
- **IM-57** — exported package has stable format/version/producer metadata and package UUID.
- **IM-58** — manifest enumerates all package files/objects with canonical identities and hashes.
- **IM-59** — manifest hash mismatch blocks import before target mutation.
- **IM-60** — missing manifest-declared file/object yields explicit invalid package state.
- **IM-61** — unlisted extra executable/suspicious file is ignored/rejected according to package policy.
- **IM-62** — package-level signature, when supported, verifies exact manifest bytes/key/profile and signer trust independently of checksum.
- **IM-63** — unsigned package is not represented as trusted-signed; policy can allow reviewed unsigned import explicitly.
- **IM-64** — unknown signer/expired/revoked key follows explicit trust policy; no automatic trust by file origin.
- **IM-65** — package timestamp does not establish freshness/trust by itself.
- **IM-66** — package filename/extension does not choose privileged parser or owner module.
- **IM-67** — duplicate package object identity inside manifest is rejected/conflicted deterministically.
- **IM-68** — object canonical checksum is stable across irrelevant serialization ordering where format promises canonicalization.
- **IM-69** — corruption of one object cannot be masked by valid outer archive checksum alone.
- **IM-70** — nested archive/package recursion is bounded and disabled unless explicitly supported.
- **IM-71** — package metadata cannot inject arbitrary PHP class/function/plugin activation instruction.
- **IM-72** — package verification result is preserved separately from import authorization and target compatibility.

# 10. Package schema/version/dependency closure — IM-73…IM-88
- **IM-73** — supported current package schema parses into typed intermediate representation before planning.
- **IM-74** — known older package schema uses deterministic VER migrator chain before target planning.
- **IM-75** — unknown future package schema fails/read-only/inspection-safe; no permissive field dropping.
- **IM-76** — package Product Version and Definition schema versions are separate compatibility dimensions.
- **IM-77** — module/adapter minimum/maximum Platform API ranges are validated before execution.
- **IM-78** — dependency graph includes required Definitions/modules/adapters/providers/assets explicitly.
- **IM-79** — dependency closure order is deterministic independent of archive file order.
- **IM-80** — dependency cycle is detected with useful path before mutation.
- **IM-81** — optional dependency absence yields declared degraded mapping, not silent semantic substitution.
- **IM-82** — hard dependency absence blocks affected object or Plan according to declared atomicity group.
- **IM-83** — deprecated definition/package contract follows VER compatibility stage and warns/blocks according to policy.
- **IM-84** — removed/incompatible contract cannot be silently accepted because field names still look similar.
- **IM-85** — source module newer than target module has explicit incompatible/degraded state.
- **IM-86** — target module newer than source package applies only registered forward migration path.
- **IM-87** — dependency capability change (read/write/delete/query) re-plans rather than assuming previous behavior.
- **IM-88** — package compatibility report distinguishes parseable, migratable, executable and fully supported states.

# 11. Definition identity, UUID remap and dependency references — IM-89…IM-104
- **IM-89** — immutable exported Definition UUID is preserved when safe and non-conflicting according to package policy.
- **IM-90** — UUID collision with semantically different local Definition creates explicit conflict, never overwrite-by-ID.
- **IM-91** — selected “create as copy” assigns new UUID and rewrites all internal package references consistently.
- **IM-92** — human slug/name collision does not override UUID identity semantics.
- **IM-93** — numeric database IDs are never portable cross-environment identity authority.
- **IM-94** — CPT/taxonomy published runtime key conflicts surface migration-class impact before import.
- **IM-95** — Field Group/Field stable keys preserve/remap relation/query/form references deterministically.
- **IM-96** — Relation Definition remap updates both endpoints/pivot references without dangling old UUIDs.
- **IM-97** — Query Definition remap updates source/field/relation placeholders and dependencies.
- **IM-98** — Blueprint/listing/dashboard/email/form/workflow dependencies remap by declared typed references only.
- **IM-99** — unknown opaque string matching a UUID is not rewritten unless schema declares it a reference.
- **IM-100** — external URL/provider ID is not remapped as local Definition UUID accidentally.
- **IM-101** — mixed preserve/copy/replace conflict decisions produce deterministic final identity map.
- **IM-102** — conflict resolution change after Dry Run changes Plan fingerprint and requires re-review.
- **IM-103** — import replay with same package/Plan reuses lineage and does not fork identities unexpectedly.
- **IM-104** — identity map corruption/mismatch blocks unsafe continuation and enters recovery state.

# 12. DSR, fields, relations, custom tables and domain constraints — IM-105…IM-120
- **IM-105** — target write capability comes from registered DSR adapter, never inferred from readable source.
- **IM-106** — target schema is pinned/revalidated at Run start; changed schema invalidates stale mapping.
- **IM-107** — field type conversion uses registered compatibility rules and records lossy/rejected conversions.
- **IM-108** — missing/null/empty/default semantics are preserved according to FST schema, not collapsed by CSV/JSON parser.
- **IM-109** — required/enum/range/format validation executes through owning field/domain rules.
- **IM-110** — unique-field collision under concurrency resolves through target constraint/precondition, not pre-check race only.
- **IM-111** — relation cardinality tightening/conflict is surfaced before or during bounded mutation with no silent edge loss.
- **IM-112** — relation delete/cascade semantics are never inferred from source omission.
- **IM-113** — pivot/relation metadata migration follows relation schema/version rules.
- **IM-114** — Custom Table desired schema Definition never triggers unreviewed DDL merely because package contains it.
- **IM-115** — CT schema migration Plan remains separate reviewed high-risk operation using CTB/CM semantics.
- **IM-116** — target transaction capability is used only where DSR/CTB certifies it; no fake atomicity.
- **IM-117** — mixed transactional/non-transactional targets produce truthful rollback class.
- **IM-118** — protected Role/User/Vault/system fields require dedicated certified domain action; generic mapping cannot write them.
- **IM-119** — WordPress core post/term/user/media invariants/hooks are preserved under certified adapter profile.
- **IM-120** — DSR/FST/REL/CTB certifications remain independent; passing them does not certify import mapping.

# 13. Query, conditions, dynamic values and component dependencies — IM-121…IM-132
- **IM-121** — Query Definition import preserves typed AST and cannot smuggle raw SQL/eval through opaque config.
- **IM-122** — unknown Query provider/operator becomes explicit incompatibility, not guessed equivalent.
- **IM-123** — CLG condition definitions retain typed operators/value-source references and safe unknown semantics.
- **IM-124** — DVR token/value source references preserve/remap only registered typed sources.
- **IM-125** — generic import never materializes resolved dynamic runtime value as canonical Definition unless schema says so.
- **IM-126** — Component Blueprint import preserves safe control/binding/slot/style/asset schema, not builder-private executable code as core authority.
- **IM-127** — builder adapter payload is accepted only through certified adapter contract and cannot become generic PHP/JS execution.
- **IM-128** — imported Definition dependency cache/index is rebuilt/reconciled from canonical imported graph.
- **IM-129** — imported disabled/draft Definition does not become active merely because a consumer references it.
- **IM-130** — imported published consumer with unresolved hard dependency cannot execute stale local dependency accidentally.
- **IM-131** — dependency visibility/authorization for reviewer does not leak secret/private values in Dry Run.
- **IM-132** — shared CLG/DVR/CBP/QRY evidence never auto-certifies package import/export semantics.

# 14. Parser and file-format security — IM-133…IM-144
- **IM-133** — JSON parser enforces nesting/string/array/object/total-byte limits.
- **IM-134** — duplicate JSON keys follow deterministic reject/normalization policy; no shadow-key ambiguity.
- **IM-135** — XML import, if supported, disables external entity/network expansion and bounds entity/depth/size behavior.
- **IM-136** — YAML or other object-deserializing formats are unsupported unless separately safe-profile certified; no arbitrary object construction.
- **IM-137** — CSV parser handles quoting/newlines/encoding deterministically and bounds field/row lengths.
- **IM-138** — CSV exported cells beginning with spreadsheet formula control prefixes are escaped/neutralized according to export profile.
- **IM-139** — spreadsheet format, if supported, never executes macros/formulas/embedded scripts during import.
- **IM-140** — Unicode normalization/invalid byte sequences cannot create duplicate/confusable identity bypass silently.
- **IM-141** — filenames are normalized/sanitized and cannot overwrite staging control files.
- **IM-142** — MIME/polyglot content cannot be routed to executable/public storage through generic importer.
- **IM-143** — archive extraction creates files with safe permissions and no executable-bit trust from source archive where host semantics matter.
- **IM-144** — parser error messages redact local filesystem paths/secrets/raw sensitive payload.

# 15. Remote sources, media and provider unknown outcomes — IM-145…IM-156
- **IM-145** — remote source fetch uses Safe HTTP allowlist, DNS/IP rebinding protections and redirect policy.
- **IM-146** — remote source cannot access loopback/link-local/private metadata/file schemes unless explicit certified profile authorizes it.
- **IM-147** — remote download byte/time/redirect limits prevent resource exhaustion.
- **IM-148** — authenticated remote source credentials are Vault-backed and excluded from logs/package exports.
- **IM-149** — remote source changes between Dry Run and Run are detected by ETag/hash/version where available or weaker evidence is explicit.
- **IM-150** — provider 429/quota response follows adapter scheduling/backoff truth and is not confused with shared RLT abuse bucket.
- **IM-151** — provider timeout after possible upload/delete/mutation enters unknown-outcome reconciliation state.
- **IM-152** — provider idempotency key, when available, is stable per logical operation and does not replace local Identity Map.
- **IM-153** — redirect to disallowed host is rejected before credentials/Authorization forwarding.
- **IM-154** — media dedupe uses content/reference policy and cannot merge unrelated private assets by filename alone.
- **IM-155** — private/protected media stays private after import; possession of imported URL/reference never grants access.
- **IM-156** — provider/Safe HTTP certification remains separate from IM certification.

# 16. Cache, audit, privacy and post-import reconciliation — IM-157…IM-168
- **IM-157** — successful canonical mutation invalidates/versions affected CAC caches after commit.
- **IM-158** — cache invalidation failure is visible/reconcilable and cannot leave security-sensitive stale privileged access silently.
- **IM-159** — imported Definition/Relation/Field/Query generation changes invalidate compiled/result/render caches as declared.
- **IM-160** — Audit records actor, Plan, package/source fingerprint, target scope, item/result class and correlation with sensitive redaction.
- **IM-161** — Journal is operational lineage, not a substitute for append-only Audit or Backup.
- **IM-162** — privacy classifications/retention travel only when schema/package policy explicitly supports them and target owner validates them.
- **IM-163** — export applies row/field/resource Policy at execution time, not only when export Definition was created.
- **IM-164** — privacy erase after import cleans derived imported data according to owner-specific PDL, not generic blind deletion.
- **IM-165** — export excludes cache/job/idempotency/rate-limit operational state by default unless explicitly defined and safe.
- **IM-166** — support/diagnostic export cannot silently include package source or private imported payload.
- **IM-167** — Pro expiry/module disable after import preserves canonical data/safe output according to MLC and does not orphan ownership invisibly.
- **IM-168** — post-restore reconciliation treats restored cache/jobs/provider sessions/commercial/access authority as requiring current validation.

# 17. Multisite, network packages, performance and final truth — IM-169…IM-176
- **IM-169** — site-owned package imports only into explicitly authorized target site; current blog is not durable scope authority.
- **IM-170** — network-owned package requires Network authority and clearly identifies network-scoped Definitions/resources.
- **IM-171** — multi-site package maps each source tenant to explicit target tenant; no numeric blog-ID coincidence.
- **IM-172** — network fan-out import is bounded/paged/Job-driven and never one unbounded synchronous all-site mutation.
- **IM-173** — clone/transfer/migration package does not copy stale OAuth/Site Allocation/provider credential/access-session authority as valid target truth.
- **IM-174** — controlled 1M-record + large-relations/media workload reports throughput, errors, checkpoints, storage and rollback/reconciliation without unsupported scale claim.
- **IM-175** — 10k-site planning/metadata workload remains bounded and demonstrates no wrong-site identity leakage/starvation beyond declared profile.
- **IM-176** — final evidence report scopes certification to exact package/source/target/runtime/topology profile and refuses generic “Import/Export certified” overclaim.

## 18. MUST NOT / stop-the-line gates

Certification fails for affected profile if:
- archive traversal/symlink/bomb/polyglot escapes bounded private staging;
- stale changed source executes as reviewed unchanged;
- package integrity/signature status is falsely reported;
- unknown future schema executes permissively;
- source data selects arbitrary SQL/PHP/class/function/filesystem/Vault secret primitive;
- wrong-site resource is read/mutated/exported;
- crash/retry/concurrent same-source Run creates duplicate logical target unexpectedly;
- UUID/numeric-ID remap corrupts dependency graph or overwrites unrelated local Definition;
- rollback overwrites newer unrelated edits or falsely claims full success;
- restored active Run auto-resumes without revalidation;
- export leaks unauthorized rows/secrets/another site's data or spreadsheet formula payload unsafely;
- remote source/media enables SSRF/credential forwarding leakage;
- CT schema/DDL is applied without its separate reviewed Migration Plan;
- cache remains privileged/stale after security-relevant import change beyond certified correctness profile;
- passing VER/DSR/FST/REL/CTB/CAC/KPA/shared evidence is used to claim IM certification.

## 19. Required future evidence report

Include exact runtime/source/package/target/topology profile; IM-01…IM-176 pass/fail/N/A; manifest/hash/signature/parser evidence; Dry Run/source fingerprint/Plan diff evidence; dependency closure and UUID/remap results; DSR/FST/REL/CTB mapping/constraint results; crash-window/duplicate Job/Identity Map results; rollback R0–R3 truth report; Safe HTTP/media/provider reconciliation; cache invalidation; privacy/audit/export tests; Multisite isolation; IR1/IR2 scale/index/storage/cleanup measurements; unsupported/degraded profiles and separate dependency protocol certifications.

## 20. Current state

- IM fixtures documented: **176**.
- IM fixtures executed: **0/176**.
- Import/Export runtime certifications: **0**.
- IR1/IR2 physical profiles remain evidence-gated.
- Shared/provider certifications remain separate.

No import/export parse, archive extraction, signature verification runtime, target mutation, DDL, DB runtime row, Job, provider/media fetch/upload, cache operation, rollback, Restore, cleanup or benchmark has been executed.

## 21. Development gate

Execution requires explicit owner consent under ADR-0014 and the Approval Ledger.