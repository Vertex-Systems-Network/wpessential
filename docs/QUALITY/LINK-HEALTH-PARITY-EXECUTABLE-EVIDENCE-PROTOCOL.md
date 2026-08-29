# WPEssential — Link Health Parity Executable Evidence Protocol

Status: **Planning-only exact executable-evidence specification / NOT EXECUTED**  
Namespace: `LHX-001…LHX-176`  
Documented: **176/176**  
Executed: **0/176**

## Truth boundaries

- Link check result is an observation with provenance; restricted/inconclusive response ≠ proven broken.
- Edit/Unlink/Ignore/Snooze/Recheck are typed Fix/Triage actions; detection itself grants no mutation authority.
- Remote/cloud scan requires explicit provider/privacy profile; provider success ≠ local business truth.
- Safe HTTP/SSRF protections apply to every server-side fetch/redirect hop.
- Redirect/SearchReplace/Media owners remain canonical for their mutations; LHX composes them rather than duplicating engines.
- Network summaries never expose raw cross-site protected URLs/content without Policy.

## Exact fixtures

### Group 1 — Local/Remote/Hybrid profile
- `LHX-001` Create engine profile with stable key and explicit Local, Remote Cloud or Hybrid mode plus scope/rate/privacy/version metadata.
- `LHX-002` Reject unknown engine/provider mode rather than silently falling back to remote/local.
- `LHX-003` Hybrid profile defines which checks are local vs provider-owned and conflict precedence.
- `LHX-004` Profile revision is pinned to scan job and stale profile change does not reinterpret old results.
- `LHX-005` Disable profile stops new scans but preserves issue history according retention.
- `LHX-006` Site/network scope is server-resolved; supplied site ID is not authority.
- `LHX-007` Local mode performs no cloud submission and reports provider fields unavailable.
- `LHX-008` Remote mode provider unavailability is degraded/unknown, not silent local substitution unless profile explicitly allows fallback.
- `LHX-009` Hybrid deduplicates same occurrence checked by multiple engines while preserving each evidence source.
- `LHX-010` AI/MCP may draft engine profile but cannot enable remote data transfer or scanning outside Policy.
- `LHX-011` Unknown profile schema/provider capability version blocks unsafe execution.

### Group 2 — cloud privacy/opt-in
- `LHX-012` Remote cloud scanning is off until explicit provider/privacy opt-in for the applicable site/network scope.
- `LHX-013` Opt-in UI states exactly what URL/content metadata is transferred and provider/region where known.
- `LHX-014` Protected/private URLs are excluded unless explicit policy authorizes transfer.
- `LHX-015` URL query strings classified as potentially sensitive are redacted/normalized according profile before submission.
- `LHX-016` Authentication cookies/headers/credentials are never sent to cloud provider unless an explicit certified connector requires scoped auth.
- `LHX-017` Cloud provider credential remains Vault-backed and absent from scan payload/log/export.
- `LHX-018` Provider retention/deletion capability is recorded as provider fact, not assumed legal compliance.
- `LHX-019` Data residency restriction blocks provider selection when incompatible.
- `LHX-020` Revoking opt-in stops future cloud submissions and follows retention/delete workflow for provider-held data where supported.
- `LHX-021` Network opt-in does not automatically authorize every site's protected-content transfer without declared network policy.
- `LHX-022` AI/MCP cannot toggle cloud opt-in or submit private URLs without same privacy/Policy gate.

### Group 3 — occurrence sources
- `LHX-023` Discover link occurrence in post/page content with source resource/field/context identity.
- `LHX-024` Discover link in block attributes through block-aware parser rather than regex-only mutation assumptions.
- `LHX-025` Discover link in custom field through registered field schema/provider and preserve field privacy classification.
- `LHX-026` Discover link in comments without exposing private/moderated comment content outside Policy.
- `LHX-027` Discover link in navigation/menu owner with exact menu/item identity.
- `LHX-028` Discover media attachment/file URL through Media owner and distinguish embedded media from ordinary anchor.
- `LHX-029` Discover redirect target through Redirect owner without treating redirect definition as page content.
- `LHX-030` Discover builder/serialized occurrence only through certified parser/adapter; unsupported formats remain unresolved.
- `LHX-031` Duplicate same URL in one resource retains occurrence-level offsets/IDs where safe Fix Plan requires it.
- `LHX-032` Generated/dynamic URL not durably stored is classified runtime/generated and not blindly editable.
- `LHX-033` Occurrence index/cache invalidates when source revision changes.

### Group 4 — result provenance
- `LHX-034` Each check result records normalized URL, method/profile, timestamp, engine/provider, redirect chain and final classification.
- `LHX-035` HTTP 2xx is reachable-at-check-time, not proof target content/business correctness.
- `LHX-036` 404/410 can be broken candidates with provenance while custom app semantics remain distinguishable.
- `LHX-037` 401/403 is restricted/auth-required, not automatically broken.
- `LHX-038` 429 is rate-limited/retryable and not broken.
- `LHX-039` Timeout/DNS/TLS/network error is inconclusive/unreachable-at-time and does not become proven broken without policy.
- `LHX-040` HEAD not supported can fall back to bounded GET only under Safe HTTP profile and records method change.
- `LHX-041` Redirect loop/too-many-redirects has distinct classification and exact chain evidence.
- `LHX-042` Result freshness/age is visible; stale prior result never counts as current proof.
- `LHX-043` Conflicting Local/Remote results are preserved as conflicting evidence.
- `LHX-044` AI explanation cannot upgrade inconclusive/restricted result to confirmed broken without accepted rule/evidence.

### Group 5 — Edit/Unlink Fix Plan
- `LHX-045` Edit Target action compiles typed Fix Plan naming exact occurrence, old target and proposed new target.
- `LHX-046` Unlink action compiles owner-aware content mutation preserving surrounding content/markup safely.
- `LHX-047` Fix Plan dry run verifies source revision still matches indexed occurrence before mutation.
- `LHX-048` Protected source resource requires owning edit capability/Policy even if link issue is visible.
- `LHX-049` Serialized/builder/custom-field edits delegate to canonical Search/Replace/owner adapters rather than raw DB mutation.
- `LHX-050` Multiple occurrences can be selected explicitly; one issue action cannot silently modify unrelated occurrences.
- `LHX-051` Stale source content returns conflict/re-scan requirement rather than overwriting newer edit.
- `LHX-052` Invalid/unsafe new URL scheme/origin is rejected according URL policy.
- `LHX-053` Partial bulk fix reports per-occurrence success/failure and safe retry identity.
- `LHX-054` Fix audit records actor/source/old-new target/revision without logging protected full content unnecessarily.
- `LHX-055` AI/MCP may draft Fix Plan but cannot apply edit/unlink without owning resource Policy.

### Group 6 — Ignore/Snooze/Recheck
- `LHX-056` Ignore records issue/occurrence scope, actor, reason and optional expiry without deleting original evidence.
- `LHX-057` URL-wide ignore vs occurrence-only ignore are distinct and explicit.
- `LHX-058` Changed URL/source fingerprint invalidates overly narrow prior ignore where issue identity no longer matches.
- `LHX-059` Snooze records resume time and suppresses notifications/actions without changing check evidence.
- `LHX-060` Expired snooze returns issue to active triage if still applicable.
- `LHX-061` Recheck creates new observation and preserves prior result history.
- `LHX-062` Recheck uses current profile/provider/rate policy and does not reuse stale status as result.
- `LHX-063` Recheck while job already pending deduplicates/queues according operation identity.
- `LHX-064` Bulk Ignore/Snooze/Recheck is capability/Policy scoped and bounded.
- `LHX-065` Ignore cannot authorize access to protected target/source; it is triage state only.
- `LHX-066` AI/MCP cannot suppress issues indefinitely or hide critical results without same triage Policy.

### Group 7 — notifications
- `LHX-067` Immediate-critical notification triggers only for configured classifications/severity and current unsuppressed issue state.
- `LHX-068` Daily digest deduplicates occurrences/issues and records covered time window.
- `LHX-069` Weekly digest uses same authorized issue set as recipient and does not leak cross-site/private URLs.
- `LHX-070` Scan-complete notification distinguishes complete, partial, failed and degraded scan states.
- `LHX-071` Notification content redacts protected query strings/source fields according policy.
- `LHX-072` Recipient resolution uses canonical user/role/team Policy and does not trust arbitrary email in issue payload.
- `LHX-073` Provider/email transport accepted ≠ recipient delivered/read; notification state remains transport-accurate.
- `LHX-074` Repeated scan result does not spam duplicate immediate alert under dedupe window/profile.
- `LHX-075` Snoozed/ignored issue notification behavior follows triage policy explicitly.
- `LHX-076` Notification failure does not alter issue truth/classification.
- `LHX-077` AI/MCP cannot add external recipients or exfiltrate issue URLs through notification drafting outside Policy.

### Group 8 — saved views/bulk
- `LHX-078` Saved view definition stores typed filters/sorts/columns/site scope and stable revision.
- `LHX-079` Filter by status/provider/source type/age/snooze/owner returns only caller-authorized issues.
- `LHX-080` Saved view cannot embed arbitrary SQL/callback/executable filter.
- `LHX-081` Shared saved view shares definition, not hidden issue visibility rights.
- `LHX-082` Bulk select across pagination uses explicit query snapshot/IDs and does not silently include new hidden results.
- `LHX-083` Bulk Fix/Ignore/Snooze/Recheck previews count/scope and enforces per-item Policy.
- `LHX-084` Partial bulk operation reports exact item results; no false whole-batch success.
- `LHX-085` Stale issue/source revision causes per-item conflict and is not force-overwritten by default.
- `LHX-086` Export of saved view/issue list redacts unauthorized/private fields.
- `LHX-087` Multisite/network shared view resolves site scope server-side.
- `LHX-088` AI/MCP can draft view/filter but cannot use it to bypass underlying issue/resource Policy.

### Group 9 — network summaries
- `LHX-089` Network summary aggregates per-site issue counts/classifications without raw URLs by default.
- `LHX-090` Site admin sees only own-site summary; network aggregate requires network Policy.
- `LHX-091` Same URL on multiple sites remains separate occurrence/site identity.
- `LHX-092` Network trend counts preserve scan coverage/completeness so missing sites do not appear as zero issues.
- `LHX-093` Critical-count drilldown reauthorizes each site's issue details.
- `LHX-094` Network notification recipients receive only permitted aggregate/detail fields.
- `LHX-095` Site deletion/archive removes/retains summary contribution according lifecycle without mutating other sites.
- `LHX-096` Site clone creates new environment/site scan identity and does not copy issue state as same proof.
- `LHX-097` Shared cloud provider credential does not imply cross-site issue visibility.
- `LHX-098` Network cache keys include scope/profile/revision and cannot bleed site-specific details.
- `LHX-099` AI/MCP site-scoped principal cannot request raw network URLs/issues by passing network scope.

### Group 10 — provider/Safe HTTP/SSRF
- `LHX-100` Server-side check allows only configured HTTP/HTTPS schemes and rejects file/data/javascript/gopher/etc.
- `LHX-101` Host resolution blocks loopback/link-local/private/metadata destinations unless an explicit trusted-internal profile exists.
- `LHX-102` Every redirect hop is revalidated against scheme/host/IP policy.
- `LHX-103` DNS rebinding defense revalidates resolved destination at connection/redirect boundary according Safe HTTP contract.
- `LHX-104` Response body download is bounded and unnecessary bodies are not retained for simple health check.
- `LHX-105` TLS certificate error classification is distinct from HTTP status and does not disable verification silently.
- `LHX-106` Authentication headers are scoped to registered target/provider and never forwarded across untrusted redirect.
- `LHX-107` Provider endpoint is registered/bounded and cannot be replaced with arbitrary URL from issue payload.
- `LHX-108` Rate limits/concurrency are per host/site/provider and honor Retry-After.
- `LHX-109` Safe HTTP logs redact credentials/query-sensitive data and do not store full response content by default.
- `LHX-110` AI/MCP cannot use “check this URL” as SSRF/private-network probing bypass.

### Group 11 — jobs/cache
- `LHX-111` Scan job pins profile/site/source snapshot and operation identity.
- `LHX-112` Queue redelivery is idempotent and does not create duplicate issue observations for same check identity.
- `LHX-113` Crawl/checkpoint records progress and scan coverage without claiming unscanned URLs healthy.
- `LHX-114` Cancel requested is distinct from worker-stopped/partial-complete state.
- `LHX-115` Job crash preserves completed observations and unfinished scope for resume/retry.
- `LHX-116` URL result cache is keyed by normalized URL + profile/provider/security context + freshness window.
- `LHX-117` Private/authenticated result cache cannot be reused as public result across credentials/site contexts.
- `LHX-118` Stale cache triggers recheck according policy and never becomes permanent source truth.
- `LHX-119` Source revision change invalidates occurrence cache even if URL text is same where occurrence identity matters.
- `LHX-120` Host backpressure prevents one domain/site from starving all scan workers.
- `LHX-121` AI/MCP cannot start unbounded crawl/job or override queue/rate limits.

### Group 12 — Redirect/SearchReplace/Media composition
- `LHX-122` Broken internal target candidate can propose Redirect owner rule; LHX does not publish redirect directly.
- `LHX-123` URL replacement across many occurrences delegates to SRT dry-run/mutation/recovery semantics.
- `LHX-124` Media missing/broken reference delegates to Media/MRL owner for replacement/reference lifecycle.
- `LHX-125` Existing redirect chain is read through Redirect owner and remains distinct from source-content link issue.
- `LHX-126` Fix Plan cannot create redirect loop; Redirect owner validation remains authoritative.
- `LHX-127` Search/Replace proposal uses typed exact URL/reference mapping, not blind substring replacement.
- `LHX-128` Protected media URL issue cannot expose signed/private locator in public diagnostics.
- `LHX-129` Mutation in one owner emits recheck/invalidation event rather than LHX reverse-writing owner state.
- `LHX-130` Partial external owner mutation leaves issue in pending/recheck state with operation provenance.
- `LHX-131` Rollback/recovery remains owning module responsibility and LHX links evidence only.
- `LHX-132` AI/MCP cannot bypass Redirect/SRT/Media approvals by invoking them indirectly through LHX.

### Group 13 — privacy
- `LHX-133` URL/query/source metadata fields receive privacy classification before storage/export/provider transfer.
- `LHX-134` Tokens/session IDs/email/PII in query strings are redacted/hashed/omitted according profile.
- `LHX-135` Private source-content snippets are not stored when occurrence locator is sufficient.
- `LHX-136` User privacy export includes only subject-related LHX data where applicable and authorized.
- `LHX-137` Erasure/retention removes erasable scan/notification metadata without corrupting shared security/operational evidence subject to policy.
- `LHX-138` Legal hold/retention can block destructive cleanup for declared scope.
- `LHX-139` Cloud provider deletion is not reported complete until provider confirms or capability is unavailable/unknown.
- `LHX-140` Logs/metrics avoid full protected URLs and response bodies by default.
- `LHX-141` Public/shared issue report strips private path/query/source identifiers.
- `LHX-142` Data residency policy applies to cloud scan/notification/provider storage.
- `LHX-143` AI/MCP receives redacted/authorized issue context only and cannot infer raw hidden query tokens.

### Group 14 — Multisite
- `LHX-144` Occurrence identity includes site/source so same post/URL IDs on two sites cannot collide.
- `LHX-145` Site-scoped scan cannot crawl another site's content merely by URL/ID injection.
- `LHX-146` Network template can define scan profile but site opt-in/privacy/provider settings follow explicit inheritance/enforcement policy.
- `LHX-147` Network scan scheduler budgets sites fairly and isolates rate/cache state.
- `LHX-148` Network aggregate has per-site completion/degradation indicators.
- `LHX-149` Cross-site shared navigation/reference is checked in each owning context and does not create cross-site edit authority.
- `LHX-150` Site clone resets scan/issue freshness and environment identity; old results remain source-environment evidence only.
- `LHX-151` Site deletion retires site occurrence/issues according retention without deleting shared provider/global metadata incorrectly.
- `LHX-152` Shared user account does not grant network issue/fix permission.
- `LHX-153` Provider credential sharing is explicit network policy and secret remains hidden from sites.
- `LHX-154` AI/MCP site principal cannot bulk fix/scan network-wide through site ID manipulation.

### Group 15 — provider failure/scale
- `LHX-155` Provider outage reports affected checks unknown/degraded and preserves last-known evidence age.
- `LHX-156` Provider schema/version change quarantines incompatible result parsing rather than misclassifying links.
- `LHX-157` Provider auth failure is connection/config error, not link issue.
- `LHX-158` Million-URL fixture later measures crawl queue/checkpoint/storage throughput with declared environment.
- `LHX-159` High-host-cardinality fixture later validates per-host concurrency/backpressure and DNS cache safety.
- `LHX-160` Large occurrence graph later validates dedupe/index/query without full content copies.
- `LHX-161` Bulk triage/fix later validates chunking/idempotency and per-item Policy.
- `LHX-162` Network-scale fixture later validates many sites without cache/privacy leakage.
- `LHX-163` Notification digest at scale remains bounded and deduplicated.
- `LHX-164` Metrics/logs remain bounded/redacted under large failure storms.
- `LHX-165` Performance/provider claims remain NOT EXECUTED until reproducible evidence is recorded.

### Group 16 — golden parity
- `LHX-166` Golden 404 content-link scenario creates broken candidate with exact occurrence and typed Edit/Unlink Fix Plan.
- `LHX-167` Golden 403 scenario remains restricted/inconclusive rather than falsely broken.
- `LHX-168` Golden timeout/provider disagreement scenario preserves conflicting/unknown evidence and supports recheck.
- `LHX-169` Golden serialized builder occurrence delegates safe mutation to certified owner adapter.
- `LHX-170` Golden remote-cloud privacy scenario blocks unapproved private URL/query transfer.
- `LHX-171` Golden SSRF scenario blocks loopback/private/metadata target and revalidates redirects.
- `LHX-172` Golden Ignore/Snooze/Recheck scenario preserves evidence/history and deterministic triage state.
- `LHX-173` Golden network summary scenario exposes counts but no unauthorized raw cross-site URLs.
- `LHX-174` Golden owner-composition scenario routes redirect/SRT/media fixes through canonical owners with their approvals.
- `LHX-175` Golden crash/scale scenario reports incomplete coverage and resumes idempotently without false healthy state.
- `LHX-176` Golden adversarial AI/MCP scenario cannot suppress critical issue, probe private network, exfiltrate URLs or apply Fix Plan outside Policy.

## Execution gate

This document specifies evidence only. **LHX executed remains 0/176.** No crawl/HTTP/provider call, content mutation, notification send, test, benchmark or AI/MCP execution is authorized by this protocol.