# WPEssential — Documents, Records & Templates Executable Evidence Protocol

Status: **Accepted evidence design candidate / execution pending / no development authorization**  
Date: **2026-08-29**

Namespace: **DOC-001…DOC-176**  
Documented: **176/176**  
Executed: **0/176**  
Runtime certification: **0**

## 1. Purpose

This protocol fixes the executable evidence required before F09 — Documents, Records & Templates can be called runtime-ready.

F09 owns template definitions, bounded rendering, generated document artifacts, protected delivery and immutable-record lifecycle only for explicitly configured document/record profiles. A generated PDF/HTML/text/structured artifact is not automatically a legally binding record, qualified electronic signature, trusted timestamp, payment/order/ledger fact, identity proof or authorization merely because WPEssential rendered or stored it.

Canonical source data remains authoritative at its owning module/domain. F09 consumes only Policy-authorized, typed and redaction-safe data. Immutable record profiles are corrected through amendment/supersession rather than silent replacement. External storage/signing/timestamp providers remain external authorities and require typed reconciliation where outcomes are uncertain.

No fixture below has executed. No renderer, PDF generator, browser engine, file write, private-file delivery, signing/timestamp provider call, remote asset fetch, AI/MCP call, benchmark, test or runtime mutation is authorized by this protocol.

## 2. Non-negotiable truth boundaries

- `Generated document ≠ source business truth`.
- `Rendered invoice ≠ payment settlement / ledger posting / order truth`.
- `Certificate/report output ≠ identity/authorization proof unless its explicit profile and external authority establish that fact`.
- `Hash/checksum ≠ legal electronic signature`.
- `Application timestamp ≠ trusted timestamp authority evidence`.
- `Template approval ≠ authorization to read every bound data field`.
- Data is reauthorized and redacted at generation and delivery boundaries.
- `Immutable record ≠ unchangeable database row forever`; correction uses explicit amendment/supersession while preserving certified history.
- `Public-looking URL ≠ public authorization`.
- External fonts/images/SVG/HTML are untrusted inputs and do not gain execution or network authority through rendering.
- Unknown external signing/storage/provider outcome is not automatically failed; reconcile before replay where duplicate external effects are possible.
- Backup/restore cannot roll back external signing/storage/timestamp providers.
- Multisite/tenant/site ownership is server-resolved and durable; request-provided scope IDs do not grant access.
- AI/MCP may draft templates/content or explain records only through the same Policy/approval gates as human actions; no hidden privileged issuance path exists.

## 3. Certification classes

- `DOC-SCH` — document/template/version/schema lifecycle.
- `DOC-LAY` — renderer primitives, layout and pagination.
- `DOC-AST` — fonts/assets/images/SVG safety and provenance.
- `DOC-DAT` — dynamic values, Policy projection and redaction.
- `DOC-OUT` — HTML/PDF/text/structured output accuracy.
- `DOC-PRV` — protected storage and delivery.
- `DOC-REC` — immutable record, amend and supersede semantics.
- `DOC-JOB` — generation Job, idempotency and crash recovery.
- `DOC-PRVNC` — hash/checksum/signing/time provenance.
- `DOC-RET` — retention/export/erase/legal-hold metadata.
- `DOC-ACC` — download/share/audit/access expiry.
- `DOC-SEC` — malicious content, SSRF and resource limits.
- `DOC-MSI` — Multisite/tenant/site lifecycle isolation.
- `DOC-BCP` — backup/restore/migration portability.
- `DOC-PERF` — large/batch rendering performance.
- `DOC-DET` — end-to-end visual/data golden regression.

Passing one class never implies another. Runtime readiness requires every class applicable to the enabled document/record profile.

# Group 1 — Document/template/version/schema — DOC-001…DOC-011

- **DOC-001** Valid Document Template publishes with stable key, immutable revision identity, explicit document profile and supported output targets.
- **DOC-002** Template schema declares typed input bindings, layout/body structure, locale/timezone defaults, assets, metadata and lifecycle state.
- **DOC-003** Unknown or unsupported template schema version is rejected as incompatible rather than silently coerced.
- **DOC-004** Required template fields and options are type/range/enum validated before publish; malformed definition cannot become active.
- **DOC-005** Draft, published, paused/disabled and archived revisions preserve stable template identity without rewriting an earlier published revision.
- **DOC-006** A generated artifact records the exact template revision/profile used so later template edits cannot rewrite historical provenance.
- **DOC-007** Duplicate/fork operation creates a new stable template identity while retaining declared provenance to the source revision.
- **DOC-008** Export/import preserves stable schema semantics, references and non-secret metadata without embedding Vault secrets or protected source records.
- **DOC-009** Deleting/archiving a template performs dependency/Used-by review and never silently deletes immutable records generated from prior revisions.
- **DOC-010** Unsupported template/output combination fails with typed diagnostics before generation instead of producing an apparently valid but incomplete artifact.
- **DOC-011** Template publication and high-risk record profile activation use separately governable capabilities/approval where configured.

# Group 2 — Renderer primitives/layout/pagination — DOC-012…DOC-022

- **DOC-012** Registered layout primitives render deterministically for the same normalized template revision, data snapshot and renderer profile.
- **DOC-013** Page size, orientation, margins, bleed/safe-area settings and unit conversion follow explicit validated semantics.
- **DOC-014** Text wrapping, line breaking and overflow behavior are deterministic and never silently drops content outside declared overflow policy.
- **DOC-015** Multi-page tables repeat configured headers/footers correctly and preserve row order without duplication or omission across page breaks.
- **DOC-016** Keep-together/page-break rules either satisfy their contract or emit a bounded degradation diagnostic when content cannot fit.
- **DOC-017** Header/footer/page-number variables use the final pagination context and do not produce off-by-one or stale total-page values.
- **DOC-018** Explicit RTL/LTR, locale and writing-direction rules preserve logical content order and do not corrupt mixed-direction text.
- **DOC-019** Long unbroken strings/URLs/content are bounded by wrap/truncate/overflow policy and cannot expand layout or memory without limit.
- **DOC-020** Nested sections/tables/components enforce configured depth/complexity limits and fail safely rather than recursive renderer exhaustion.
- **DOC-021** Renderer fallback for unsupported primitive/property is explicit and observable; it cannot silently claim pixel-equivalent output.
- **DOC-022** Preview/simulation and final generation use the same semantic renderer contract, with any preview-only approximation visibly identified.

# Group 3 — Fonts/assets/images/SVG sanitization/licensing references — DOC-023…DOC-033

- **DOC-023** Font reference resolves only through an approved Asset/Font profile with explicit family, weight/style and fallback behavior.
- **DOC-024** Missing font asset triggers declared fallback/failure behavior and cannot silently substitute a materially different font while claiming exact output.
- **DOC-025** Font embedding/subsetting preserves required glyphs for the input corpus and records asset/version provenance where output profile requires it.
- **DOC-026** Font license/source metadata is retained as provenance only; local hosting or embedding is not presented as automatic legal compliance.
- **DOC-027** Raster images validate type, dimensions, decoded size and resource budgets before renderer use; decompression-bomb inputs fail safely.
- **DOC-028** Image orientation, crop, fit and resolution rules are deterministic and preserve declared aspect behavior across outputs.
- **DOC-029** SVG input is sanitized against scripts, event handlers, external references and unsupported active content before rendering.
- **DOC-030** SVG/XML entity expansion and dangerous parser features are disabled/bounded so crafted assets cannot trigger XXE/entity-exhaustion behavior.
- **DOC-031** Remote asset references are prohibited by default or pass an explicit SSRF-safe fetch policy; arbitrary template URLs cannot access internal/private networks.
- **DOC-032** Asset replacement/version change invalidates only affected derived document caches/previews and never rewrites immutable historical artifacts silently.
- **DOC-033** Missing/corrupt asset produces explicit generation failure or configured placeholder with provenance; it never disappears without diagnostic evidence.

# Group 4 — Dynamic values/Policy/redaction — DOC-034…DOC-044

- **DOC-034** Dynamic binding resolves through registered typed Data Source/Query/field contracts rather than arbitrary PHP/SQL/eval expressions.
- **DOC-035** Every protected source value is authorized server-side for the generation principal, site/tenant, template purpose and resource scope.
- **DOC-036** A template author able to reference a field does not automatically gain permission to render that field for every subject/record.
- **DOC-037** Redaction policy can mask/remove protected fields while preserving layout and an auditable reason without leaking original values in metadata.
- **DOC-038** Null, missing, denied and redacted values remain distinguishable internally and follow explicit display/fallback semantics.
- **DOC-039** Derived values from F04 or other owners retain source revision/provenance and do not become F09 authorization/business truth.
- **DOC-040** Collection/query bindings enforce row limits, ordering, pagination/aggregation semantics and Policy projection before template expansion.
- **DOC-041** Nested object traversal is bounded to registered schema paths and cannot read arbitrary object properties/files/environment data.
- **DOC-042** Secret/Vault values are non-renderable by default; any exceptional secret-derived display uses a dedicated masked/tokenized contract rather than raw secret access.
- **DOC-043** Delivery-time authorization is re-evaluated for protected artifacts where access can change after generation; possession of artifact ID alone grants nothing.
- **DOC-044** AI/MCP-proposed binding/redaction changes remain Draft until the same publish/Policy review required for human-authored changes is satisfied.

# Group 5 — HTML/PDF/text/structured output profile accuracy — DOC-045…DOC-055

- **DOC-045** HTML output escapes/sanitizes untrusted dynamic content according to declared rich-text/plain-text context and prevents script/event-handler injection.
- **DOC-046** PDF output preserves normalized textual values, page count, ordering and mandatory metadata required by the selected certified profile.
- **DOC-047** Plain-text output has deterministic line/section ordering and cannot accidentally expose fields omitted/redacted in the canonical generation plan.
- **DOC-048** Structured output (for example JSON/XML when enabled) validates against its published schema and uses typed canonical values rather than screen-formatted strings unless declared.
- **DOC-049** The same generation snapshot produces semantically equivalent required data fields across HTML/PDF/text/structured targets, with target-specific differences explicitly declared.
- **DOC-050** Locale-specific number/date/currency formatting changes presentation only and does not mutate canonical source values or invent exchange rates.
- **DOC-051** Output character encoding handles Unicode, combining characters and unsupported glyphs deterministically without silent data loss.
- **DOC-052** Hyperlink/URI output validates allowed schemes and escaping; `javascript:` and equivalent active schemes cannot be emitted through untrusted values.
- **DOC-053** Accessibility/tagging claims are made only for output profiles with actually evidenced semantics; ordinary PDF generation is not automatically labelled accessible/PDF-UA compliant.
- **DOC-054** Archival format claims such as PDF/A are surfaced only if the selected renderer/profile passes dedicated conformance evidence; file extension alone is insufficient.
- **DOC-055** Output comparison records deterministic semantic checksums/golden fields separately from visual raster comparisons so visual similarity cannot hide data mismatch.

# Group 6 — Private/protected file storage/delivery — DOC-056…DOC-066

- **DOC-056** Protected artifact storage uses a non-public or access-mediated profile; direct predictable web paths cannot bypass Policy.
- **DOC-057** Artifact identity, owner/site/tenant, classification, MIME/type, size, checksum and storage locator are durable metadata separated from public display name.
- **DOC-058** Download endpoint authorizes actor/resource/action at request time and does not trust client-supplied owner/site/tenant fields.
- **DOC-059** Range/streaming downloads preserve authorization and content-length/range correctness without loading an unbounded protected artifact wholly into memory.
- **DOC-060** Response headers prevent dangerous content sniffing/inline execution according to artifact type and declared disposition policy.
- **DOC-061** Private artifact cache/CDN integration uses signed/authorized delivery semantics whose expiry and scope cannot be extended by client parameter tampering.
- **DOC-062** Storage provider timeout/unknown write outcome is reconciled before duplicate upload/generation if replay could create multiple authoritative copies.
- **DOC-063** Deleting a storage object only after canonical retention/deletion authorization cannot leave metadata falsely claiming the protected bytes still exist.
- **DOC-064** Missing external/provider object is represented as degraded/missing and does not return an unrelated object through recycled key/path.
- **DOC-065** Artifact classification changes invalidate delivery caches/tokens so a formerly public artifact cannot remain anonymously reachable after protection.
- **DOC-066** Public artifact mode is explicit, separately approved and still applies safe content headers; making an artifact public never grants access to its source entities.

# Group 7 — Immutable record/version/amend/supersede semantics — DOC-067…DOC-077

- **DOC-067** Immutable-record issuance freezes record identity, source snapshot/provenance, template revision and artifact checksum according to the selected profile.
- **DOC-068** Once issued/finalized, protected immutable fields/artifact bytes cannot be silently edited in place through normal template/document APIs.
- **DOC-069** Correction creates an explicit amendment/corrected record or superseding version linked to the original instead of overwriting history.
- **DOC-070** Superseded record remains queryable according to Policy/retention with clear status and forward/backward linkage to replacement records.
- **DOC-071** Void/cancel/revoke status does not delete prior record bytes/history unless a separate retention/privacy rule authorizes deletion and preserves required evidence.
- **DOC-072** Reissuing the same business event under the same configured idempotency/source identity returns the existing record or explicit revision path rather than duplicate issuance.
- **DOC-073** Record number/sequence, when configured, is generated by a dedicated atomic namespace/profile and does not rely on client guesses or `max()+1` races.
- **DOC-074** Sequence gaps caused by rollback/failed issuance follow declared policy and are not hidden by dangerous renumbering of already-issued records.
- **DOC-075** Record linkage to order/payment/ledger/certificate subject remains a typed reference; F09 does not claim ownership of those source-domain facts.
- **DOC-076** Template retirement does not invalidate or mutate records already issued from that template revision.
- **DOC-077** Import of legacy immutable records preserves explicit original provenance and does not falsely claim they were generated/signed by WPEssential.

# Group 8 — Generation Job/idempotency/crash/partial output — DOC-078…DOC-088

- **DOC-078** Generation request records stable idempotency identity, normalized input/source snapshot fingerprint, template revision, output profile and requester scope.
- **DOC-079** Repeating the same idempotency key with identical normalized generation plan returns the original terminal/in-progress result rather than generating duplicate records.
- **DOC-080** Reusing an idempotency key with materially different generation plan is rejected as conflict with safe diagnostics.
- **DOC-081** Crash before artifact commit leaves no record labelled successfully generated and retry reuses the same idempotency/source identity.
- **DOC-082** Crash after durable artifact/record commit but before response is resolved through idempotency lookup without duplicate issuance.
- **DOC-083** Partial multi-format generation reports each target state explicitly and does not label the bundle complete while required outputs are missing/unknown.
- **DOC-084** Temporary/intermediate files are uniquely scoped, permission-safe and cleaned after success/failure without deleting another concurrent job’s artifacts.
- **DOC-085** Queue redelivery preserves generation identity and cannot create duplicate immutable records or sequence numbers.
- **DOC-086** Dead-lettered generation retains bounded diagnostic/provenance data and controlled replay semantics without exposing protected source content/secrets.
- **DOC-087** Timeout/cancellation distinguishes unknown renderer/provider outcome from confirmed failure when external side effects may already exist.
- **DOC-088** Generation status API never reports success until every required durable artifact/record transition for the selected profile has completed.

# Group 9 — Signing/hash/checksum/time provenance without false legal signature claims — DOC-089…DOC-099

- **DOC-089** Content checksum uses an explicit algorithm/version and canonical byte target so repeat verification is deterministic.
- **DOC-090** Hash verification detects artifact-byte modification but is never presented as proof of signer identity or legal intent by itself.
- **DOC-091** Internal application signature/attestation, if supported, records key/profile/provenance semantics and is labelled according to what it actually proves.
- **DOC-092** External electronic-signature provider identity, envelope/request ID, signer state and provider evidence remain typed external facts distinct from local artifact metadata.
- **DOC-093** Provider signing timeout after request submission is classified as unknown until reconciliation; blind resubmission cannot create duplicate signing workflows.
- **DOC-094** Provider webhook/callback processing is authenticated, idempotent and reconciles against expected record/envelope identity before updating local status.
- **DOC-095** Application-generated timestamp records system time/source only and is not labelled a trusted timestamp authority token.
- **DOC-096** Trusted timestamp profile, if ever enabled, records external authority/token verification separately and cannot be simulated by local clock metadata.
- **DOC-097** Key rotation preserves verification provenance for historical artifacts without rewriting their prior signature/hash metadata.
- **DOC-098** Revoked/expired signing credential status is represented accurately; historical verification result is not silently changed into a claim of current validity.
- **DOC-099** UI/API/report wording prevents “signed”, “certified”, “trusted timestamp”, “legally binding” or equivalent claims unless the exact configured profile has matching evidence.

# Group 10 — Retention/export/erase/legal-hold metadata — DOC-100…DOC-110

- **DOC-100** Document/record profile declares retention class, retention start event, minimum/maximum policy and source of policy authority.
- **DOC-101** Retention expiry does not automatically delete an artifact under active legal-hold/preservation metadata.
- **DOC-102** Legal-hold metadata identifies scope, authority/reason, start/end state and access Policy without pretending WPEssential itself creates legal privilege.
- **DOC-103** Erasure request distinguishes erasable personal data, retained legal/business record data and redaction/pseudonymization alternatives according to policy.
- **DOC-104** Redaction/erasure of mutable metadata does not silently mutate immutable artifact bytes when profile requires preserving original record; any derived redacted copy is separately identified.
- **DOC-105** Retention deletion is idempotent and reconciles canonical metadata with local/external storage state before marking bytes destroyed.
- **DOC-106** Export package includes only Policy-authorized records/metadata and preserves stable provenance/checksums without leaking unrelated tenants/sites.
- **DOC-107** Export of protected artifacts uses a controlled delivery job and cannot place private documents in a public predictable directory.
- **DOC-108** Import restores retention/legal-hold metadata only through compatible schema/policy mapping and never silently shortens required preservation.
- **DOC-109** Archived template/record state remains distinct from retention deletion; archive is not proof that underlying bytes were destroyed.
- **DOC-110** Retention/reporting UI distinguishes “scheduled for deletion”, “deletion attempted”, “provider outcome unknown” and “verified removed” states.

# Group 11 — Download/audit/share/access expiry — DOC-111…DOC-121

- **DOC-111** Successful protected download records bounded Audit evidence including actor/service principal, artifact identity, action, time and outcome without duplicating full protected content.
- **DOC-112** Denied download/share attempt is auditable without leaking protected filename, subject or source fields beyond authorization policy.
- **DOC-113** Share grant has stable identity, grantee/audience scope, permitted action, expiry and revocation state; possession of share ID alone grants nothing.
- **DOC-114** Signed/share token is cryptographically bound to artifact, allowed action, audience/scope and expiry; changing token parameters invalidates it.
- **DOC-115** Expired share/access token fails closed even if downstream cache still contains artifact bytes.
- **DOC-116** Revocation invalidates new access and follows explicit cache/CDN purge or bounded token-expiry guarantee; UI cannot claim instant revocation without evidence.
- **DOC-117** One-time download link, when enabled, consumes atomically and cannot be redeemed twice under concurrent requests.
- **DOC-118** Download count/limit enforcement is atomic under concurrency and separate from business authorization.
- **DOC-119** Share recipient view never exposes source entity navigation, unrelated metadata or internal storage locator unless separately authorized.
- **DOC-120** Audit Log is operational access evidence, not the immutable business record itself and not a substitute for F09 record history.
- **DOC-121** AI/MCP retrieval/download/share actions are attributed to a real governed principal/session and cannot use a hidden privileged document-access channel.

# Group 12 — Malicious template/content/SSRF/resource limits — DOC-122…DOC-132

- **DOC-122** Template engine supports only registered bounded primitives/functions and does not evaluate arbitrary PHP/JavaScript/shell/SQL expressions.
- **DOC-123** Dynamic rich-text/HTML sanitization prevents script, event-handler, unsafe URL and active embedded-content execution in generated/preview surfaces.
- **DOC-124** Template include/partial references resolve only within registered template/asset namespaces and cannot perform filesystem path traversal.
- **DOC-125** Remote URL fetching is disabled by default; enabled profiles enforce allowlist/protocol/DNS/IP/rebind protections against SSRF to loopback, link-local, private and metadata networks.
- **DOC-126** Redirect chains for approved remote assets are bounded and each hop is revalidated against SSRF policy.
- **DOC-127** Maximum template size, source data size, collection rows, asset count, page count, nesting depth and render time are enforced before/until termination.
- **DOC-128** Malicious image/font/SVG/archive parser failure is contained and cannot write outside temporary/artifact namespaces or execute embedded payloads.
- **DOC-129** Zip/archive import guards against path traversal, symlinks and decompression bombs before extracting template packages.
- **DOC-130** Error messages/logs redact protected source data, secrets, filesystem paths and provider credentials while retaining actionable diagnostics.
- **DOC-131** Concurrent malicious/expensive render requests obey per-principal/site/tenant/global Job/Rate-Limit budgets and cannot starve unrelated tenants indefinitely.
- **DOC-132** AI-generated template/content passes the same sanitizer, complexity, Policy and approval checks as human-created content with no trusted-AI bypass.

# Group 13 — Multisite/tenant/site lifecycle — DOC-133…DOC-143

- **DOC-133** Template, record and artifact ownership stores durable site/tenant/network scope resolved server-side.
- **DOC-134** Same template key or record external reference on two isolated sites/tenants cannot collide in identity, cache, sequence or storage namespace.
- **DOC-135** Network template inheritance/override follows an explicit model and preserves stable provenance to network and site-specific revisions.
- **DOC-136** Network administrator visibility does not automatically imply protected record-content access where tenant/data Policy restricts it.
- **DOC-137** Cross-site document generation is denied by default unless an explicit network aggregate profile authorizes every source and output scope.
- **DOC-138** Cross-site generated bundle cannot leak rows/assets/metadata from unauthorized sites through template loops, caches or shared storage prefixes.
- **DOC-139** Site deletion/deactivation follows explicit template/record/artifact retention/export policy and never silently orphan/reassign records to another site.
- **DOC-140** Site creation from a template may copy definitions but must create new site-scoped identities where runtime record/artifact identity requires isolation.
- **DOC-141** Shared network assets/templates do not imply shared record sequence, protected storage or source-data authority unless explicitly configured.
- **DOC-142** Site/tenant switch during generation cannot change scope mid-job; normalized generation plan pins ownership throughout execution.
- **DOC-143** Multisite export/import preserves ownership mapping and rejects ambiguous/unmapped protected source references rather than guessing destination scope.

# Group 14 — Backup/restore/migration portability — DOC-144…DOC-154

- **DOC-144** Backup inventory distinguishes template definitions, mutable document metadata, immutable records, artifact bytes, storage-provider references and external signature/timestamp facts.
- **DOC-145** Restore reproduces locally backed template/record metadata and verifies artifact checksums for bytes included in the recovery artifact.
- **DOC-146** Restore does not claim to roll back external signing/storage/timestamp providers; post-recovery reconciliation identifies external facts after the recovery point.
- **DOC-147** Point-in-time restore cannot resurrect an expired/revoked external share/signing state as current authority without revalidation.
- **DOC-148** Restored idempotency/source identities prevent duplicate immutable-record issuance when upstream events are replayed after recovery.
- **DOC-149** Clone/staging operation assigns a distinct non-production environment identity and cannot emit production document/signing/storage side effects by default.
- **DOC-150** Cloned external provider credentials/connections are disabled/quarantined unless separately remapped and approved for the target environment.
- **DOC-151** Migration preserves stable template/record IDs, revision chains, checksums and storage mappings or records explicit remapping provenance.
- **DOC-152** Missing artifact bytes during migration are surfaced as missing/degraded and never replaced by unrelated files with matching names.
- **DOC-153** Cross-environment URL/storage-prefix rewriting occurs through typed migration mappings rather than blind text replacement inside immutable artifacts.
- **DOC-154** Backup/restore/migration verification produces counts/checksums/unresolved-provider mappings without exposing protected document contents to unauthorized operators.

# Group 15 — Large/batch rendering memory/time benchmarks — DOC-155…DOC-165

- **DOC-155** 10K-document batch profile records actual throughput, success/failure counts, queue latency, renderer time and resource usage before any performance claim.
- **DOC-156** 100K-document batch profile measures sustained throughput/backpressure and proves no unbounded queue/memory growth under declared infrastructure.
- **DOC-157** 1M-document archive/index metadata query profile measures pagination/filter latency and memory without requiring all artifacts in memory.
- **DOC-158** Large single document profile measures page-count, table-row, image and text limits with explicit maximum supported envelope.
- **DOC-159** Large-image/font/SVG corpus measures decoded memory and renderer behavior so asset bombs cannot hide behind average-case benchmarks.
- **DOC-160** Concurrent protected downloads measure authorization/storage streaming overhead without disabling Policy or security headers for benchmark convenience.
- **DOC-161** Batch failure/retry benchmark verifies idempotency prevents duplicate record issuance/sequence allocation under queue redelivery.
- **DOC-162** Hot-template contention benchmark proves unrelated tenants/templates remain within declared fairness/isolation budgets.
- **DOC-163** Cache effectiveness benchmark separates safe reusable template/assets from subject-specific/protected outputs and checks no personalized data leakage.
- **DOC-164** Performance evidence records exact dataset, renderer/version, WordPress/PHP/DB/storage profile and hardware so results are reproducible and not universalized falsely.
- **DOC-165** Documentation-only estimates or synthetic calculations cannot mark DOC-PERF passed; actual executed measurements are required later.

# Group 16 — Invoice/certificate/contract/report golden visual/data regression — DOC-166…DOC-176

- **DOC-166** Golden invoice fixture preserves exact source order/line items/taxes/totals supplied by canonical commerce/decision sources and never invents payment-settlement truth.
- **DOC-167** Golden invoice amendment/reissue fixture preserves original artifact/record chain while showing explicit corrected/superseding status.
- **DOC-168** Golden certificate fixture verifies subject/issuer/template/date identifiers and checksum provenance without claiming identity/legal authority beyond the configured certificate profile.
- **DOC-169** Golden contract-style document fixture verifies multi-page clauses, signatures/placeholders, headers/footers and amendments while separating rendered signature fields from external legal-signature evidence.
- **DOC-170** Golden analytics/report fixture verifies tables/charts/text values are bound to a pinned source snapshot/revision and late data does not silently rewrite an immutable issued report.
- **DOC-171** Golden multilingual/RTL fixture validates Unicode, fonts, directionality, pagination and locale formatting across required output targets.
- **DOC-172** Golden privacy fixture proves the same template under two principals/tenants produces Policy-correct redaction and no cache/artifact cross-leakage.
- **DOC-173** Golden crash/retry/provider-unknown fixture proves one durable record identity, no duplicate external signing flow and reconciled terminal state.
- **DOC-174** Golden restore/clone fixture proves immutable checksums, idempotency and provider quarantine survive recovery/environment transition without production side effects.
- **DOC-175** Cross-runtime/renderer-version regression corpus distinguishes approved rendering drift from semantic data/checksum failures and blocks silent incompatible upgrades.
- **DOC-176** AI/MCP adversarial golden suite attempts protected-field exfiltration, malicious HTML/SVG/URL insertion, fake “signed/legal” wording, unauthorized issuance/share and duplicate generation; every attempt remains bounded by Policy, sanitizer, provenance and idempotency rules.

## 4. Completion and certification rule

F09 may be called **documented** when all `DOC-001…DOC-176` fixture definitions are present and reviewed. That state is achieved by this protocol.

F09 may be called **runtime-certified** only after the applicable fixtures have actually executed against the claimed implementation, renderer/output/storage/provider profiles and supported WordPress/Multisite environments, with retained machine/verifiable evidence.

Current truth remains:

- documented: **176/176**;
- executed: **0/176**;
- runtime certification: **0**;
- implementation authorization: **NOT GRANTED**.

This document does not authorize implementation, runtime mutation, external provider calls, tests or benchmarks.