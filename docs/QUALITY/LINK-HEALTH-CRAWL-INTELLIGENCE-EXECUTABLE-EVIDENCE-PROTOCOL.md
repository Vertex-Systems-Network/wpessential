# WPEssential — Link Health / Crawl Intelligence Executable Evidence Protocol

Status: **Exact planning evidence / NOT EXECUTED / no development authorization**  
Date: 2026-08-29  
Work package: **WP113**  
Namespace: **LNK-001…LNK-176**  
Documented: **176/176**  
Executed: **0/176**  
Runtime certification: **0**

## Purpose

Freeze exact future evidence for Surface 47 Link Health, Broken Link & Crawl Intelligence, preserving the 16 canonical groups from the market-expansion master plan and `MODULES/LINK-HEALTH-BROKEN-LINK-CRAWLER-EXHAUSTIVE-SPEC.md`.

## Truth boundaries

- Inconclusive/blocked/rate-limited/timeout response ≠ proven broken link.
- Internal route/entity knowledge ≠ authorization to reveal protected/private resource existence.
- Crawl/fetch result ≠ business correctness of target.
- Orphan classification ≠ automatic SEO defect.
- Scan result ≠ permission to mutate source content/redirects/media.
- Safe HTTP/SSRF/DNS/TLS/redirect policy applies at every external fetch hop.
- No credentials/cookies/Authorization headers may leak to arbitrary external origins.
- AI suggestions are advisory; intended replacement target requires evidence/approval.

---

## Group 1 — source discovery, content, field and block adapters — LNK-001…011

- **LNK-001** — Discover anchors from stored post/page/CPT content and record source entity, field/path, site and source revision/fingerprint.
- **LNK-002** — Discover URLs from excerpt only when source profile enables it; excerpt-disabled profile must not scan hidden field accidentally.
- **LNK-003** — Custom field discovery uses Field Storage descriptors/Policy and cannot enumerate protected fields by raw meta-table scan.
- **LNK-004** — Comment URL discovery is opt-in and applies comment/resource Policy before exposing source occurrence.
- **LNK-005** — Term description links use taxonomy/term identity and do not collide across taxonomies/sites with same slug/name.
- **LNK-006** — Navigation/menu link discovery uses registered menu adapter and records menu item identity separately from destination URL.
- **LNK-007** — Gutenberg block attributes are parsed from certified block schema and unknown block payload is not blindly executed/rendered.
- **LNK-008** — Shortcode source adapter parses certified stored attributes/output without executing arbitrary shortcode PHP during static scan.
- **LNK-009** — Rendered frontend crawl is a distinct profile and cannot be silently substituted for stored-source scan when dynamic/private output differs.
- **LNK-010** — XML sitemap/manual URL-list sources are marked external/source-list inputs and do not imply corresponding WordPress entity ownership.
- **LNK-011** — Source changed after discovery is detected through revision/fingerprint before a Fix Plan can rely on stale occurrence coordinates.

## Group 2 — link extraction and normalization — LNK-012…022

- **LNK-012** — Extract `href` URL and preserve original occurrence plus normalized comparison identity without altering source content.
- **LNK-013** — Extract image/source `src` and every `srcset` candidate with descriptor association; one broken candidate does not rewrite the entire attribute automatically.
- **LNK-014** — Extract video/audio/embed URLs only from certified attributes and classify provider/embed URLs separately.
- **LNK-015** — Canonical/hreflang/asset/form-action references are extracted only under enabled developer/SEO profiles and are not treated as ordinary content links by default.
- **LNK-016** — Relative URL resolves against the owning source’s canonical base/site context, not an attacker-controlled Host header.
- **LNK-017** — Protocol-relative URL is normalized/classified using current canonical scheme policy while retaining original form for diagnostics.
- **LNK-018** — `mailto:`, `tel:`, `sms:` and unsupported schemes are classified without network fetch; unsupported does not automatically mean broken.
- **LNK-019** — Fragment-only URL is linked to source document identity and routed to anchor checking rather than external HTTP.
- **LNK-020** — Percent encoding/Unicode/IDN normalization avoids double decoding and produces deterministic URL identity for dedupe.
- **LNK-021** — URLs containing sensitive query keys are normalized with redacted display/log identity while preserving enough hash/typed data for dedupe where allowed.
- **LNK-022** — Generated/signed/expiring URL profile can mark URL `do_not_crawl`; scanner must not invalidate ephemeral credentials by probing blindly.

## Group 3 — internal WordPress route/entity resolution — LNK-023…033

- **LNK-023** — Internal permalink maps to existing published post/entity and is classified healthy without requiring external network fetch when direct resolver is authoritative.
- **LNK-024** — Internal URL resolving to trashed/deleted content is classified according to route/redirect state and not simply by stale entity ID.
- **LNK-025** — Private/protected post may be resolvable internally but result shown to unauthorized actor must not reveal title/existence beyond Policy.
- **LNK-026** — Term/archive/internal route existence uses registered WordPress route/entity resolver and supports expected non-entity routes.
- **LNK-027** — Permalink changed but Redirect Manager has valid active rule; issue classification distinguishes source stale link from destination reachability.
- **LNK-028** — Expected intentional 404/410 route is distinguishable from accidental missing content when owner/router supplies explicit status semantics.
- **LNK-029** — Internal route collision/ambiguous rewrite rule produces `ambiguous/inconclusive` diagnostic instead of guessed entity resolution.
- **LNK-030** — Destination requiring login/capability is classified restricted/protected, not broken, and cannot leak resource details to unauthorized reports.
- **LNK-031** — Same path on site A/site B resolves under server-trusted site/domain mapping and cannot use request-supplied site ID to cross tenant.
- **LNK-032** — Query/listing/generated route uses owning Query/Listing adapter and is not assumed valid merely from a matching URL pattern.
- **LNK-033** — Internal resolver unsupported/disabled falls back only to an allowed Safe HTTP profile; missing adapter is reported rather than silently treating URL healthy.

## Group 4 — Safe HTTP, SSRF, DNS, TLS and redirect policy — LNK-034…044

- **LNK-034** — External public HTTPS URL check validates DNS/IP and TLS before request under Safe HTTP profile.
- **LNK-035** — Loopback target (`127.0.0.1`, `::1`, localhost aliases) is blocked before connection.
- **LNK-036** — RFC1918/private/link-local/cloud-metadata target is blocked even when supplied through DNS hostname.
- **LNK-037** — DNS rebinding/TOCTOU protection revalidates resolved destination at connection/hop according to certified Safe HTTP contract.
- **LNK-038** — Public URL redirecting to private/link-local target is blocked on redirected hop; initial public resolution does not whitelist later hops.
- **LNK-039** — Redirect chain does not forward Authorization/cookies/secret headers to a different origin.
- **LNK-040** — TLS certificate/hostname validation failure is classified TLS failure and cannot be bypassed by “ignore SSL” default.
- **LNK-041** — HEAD first/GET fallback policy uses bounded body and only falls back for accepted status/method behavior; fallback cannot download unbounded response.
- **LNK-042** — Configured timeout, max redirects and response-size budget terminate request with typed inconclusive/failure category, not “404 broken.”
- **LNK-043** — Per-host concurrency/backoff and Retry-After are honored so scanner cannot overload a remote host intentionally or accidentally.
- **LNK-044** — Robots/polite-crawl rules for rendered crawler are distinct from stored-link checker semantics and any policy-blocked target is reported as not checked/inconclusive.

## Group 5 — status classification and inconclusive handling — LNK-045…055

- **LNK-045** — 2xx response is `reachable/healthy` for transport but does not prove page business correctness/content quality.
- **LNK-046** — 301/302/307/308 is classified redirect with Location validation and recorded hop, not automatically healthy final state.
- **LNK-047** — 404/410 is classified broken/gone under accepted profile with final URL and response evidence.
- **LNK-048** — 401/403 is `restricted` by default and cannot be labeled broken solely from denied anonymous access.
- **LNK-049** — 429 is `rate_limited/inconclusive` and schedules backoff rather than permanent broken issue.
- **LNK-050** — 5xx is server error with retry/history semantics; one transient 500 does not necessarily become confirmed broken until profile threshold.
- **LNK-051** — Timeout/connection reset is `inconclusive/network failure`, not verified broken.
- **LNK-052** — DNS NXDOMAIN and persistent name-resolution failure are distinguished from transient resolver failure.
- **LNK-053** — Bot/WAF challenge/CAPTCHA response is classified inconclusive/restricted and does not trigger auto-fix.
- **LNK-054** — Unsupported scheme/not-checked/policy-blocked states remain first-class and excluded from “confirmed broken” metrics unless explicitly selected.
- **LNK-055** — Confirmation policy can require repeated/fresh checks before state `confirmed`; history records contradictory recoveries without erasing prior evidence.

## Group 6 — fragments and anchors — LNK-056…066

- **LNK-056** — Fragment on stored internal HTML is checked against exact element ID/name under configured case/encoding rules.
- **LNK-057** — Missing fragment on otherwise healthy URL produces `missing_anchor`, not generic broken URL.
- **LNK-058** — Percent-encoded/Unicode fragment normalization is deterministic and does not double decode.
- **LNK-059** — Duplicate IDs on target page produce ambiguous HTML diagnostic rather than pretending unique anchor resolution.
- **LNK-060** — Anchor check of protected/private page applies target Policy and cannot expose its DOM/IDs to unauthorized scanner viewer.
- **LNK-061** — External anchor checking requires allowed bounded page fetch; if body not fetched due policy/size, anchor status remains not checked/inconclusive.
- **LNK-062** — JavaScript-generated anchor is `unsupported_dynamic` unless certified browser adapter is used; static miss is not asserted as definite broken in that profile.
- **LNK-063** — Anchor cache key includes target content revision/hash/freshness so stale DOM cannot be reused indefinitely after content change.
- **LNK-064** — Same base URL with multiple fragments can share one safe fetch while preserving separate fragment results.
- **LNK-065** — Malicious HTML with huge ID count/nested markup respects parser/resource limits and cannot exhaust scan worker.
- **LNK-066** — Fix suggestion for missing fragment remains advisory; scanner cannot invent target anchor or rewrite source automatically.

## Group 7 — redirect chain and loop analysis — LNK-067…077

- **LNK-067** — One-hop redirect records source, response code, destination and final reachability distinctly.
- **LNK-068** — Three-plus-hop chain records each hop/origin/status and flags excessive chain threshold without auto-rewriting source.
- **LNK-069** — Redirect cycle is detected within max-hop graph before request loop can consume unbounded network budget.
- **LNK-070** — HTTP↔HTTPS or www/non-www flip-flop is reported as canonicalization loop with exact hops.
- **LNK-071** — Cross-domain redirect chain preserves origin changes and applies credential/SSRF validation each hop.
- **LNK-072** — Redirect to final 404/410 reports both chain and broken final target; intermediate 3xx is not treated as success.
- **LNK-073** — Mixed query preservation/discard across chain is recorded and can flag semantic-risk warning without asserting business intent.
- **LNK-074** — Redirect chain involving WPE Redirect Manager local rule references exact rule/revision but external/core redirects remain separate observed facts.
- **LNK-075** — Cached redirect result expires/revalidates according to freshness profile; remote chain change cannot remain permanent truth.
- **LNK-076** — Fix Plan may propose replacing source URL with final destination or collapsing local redirect rule, but these are separate owner-governed mutations.
- **LNK-077** — External redirect graph unavailable/timeout mid-chain yields partial/inconclusive chain rather than fabricated final target.

## Group 8 — broken media, srcset and embeds — LNK-078…088

- **LNK-078** — Attachment DB record whose source file is missing is classified media-source-missing using Media owner evidence.
- **LNK-079** — File exists but expected derivative missing is derivative advisory/failure, not evidence source attachment itself is missing.
- **LNK-080** — Orphan physical file without attachment record is not discovered/deleted by Link Health unless Media owner exposes that inventory; scan is read-only.
- **LNK-081** — External image 404 is classified broken media after safe status confirmation.
- **LNK-082** — External image timeout/WAF/403 remains inconclusive/restricted rather than proven broken.
- **LNK-083** — `srcset` candidate results are tracked per candidate descriptor; one broken width candidate can create a partial media issue.
- **LNK-084** — Mixed-content HTTP media on HTTPS source is security/quality issue distinct from reachability.
- **LNK-085** — Redirected media records final target/content-type where allowed and does not blindly rewrite attachment URLs.
- **LNK-086** — Private media delivery URL is checked through certified protected-delivery profile or labeled not checked; public anonymous 403 is not broken.
- **LNK-087** — Embed/video provider URL check follows provider/polite-crawl profile and avoids downloading media body beyond bounded metadata request.
- **LNK-088** — Media fix handoff to regeneration/replacement/SearchReplace/Redirect creates reviewed Plan; scanner never deletes/replaces files directly.

## Group 9 — internal graph, orphan content and crawl depth — LNK-089…099

- **LNK-089** — Build directed internal link graph from authorized source/destination identities and preserve site/type metadata.
- **LNK-090** — Inbound/outbound occurrence counts distinguish multiple links from unique source entities.
- **LNK-091** — Configured roots/navigation/sitemap membership define crawl-depth calculation explicitly; depth is not universal SEO truth.
- **LNK-092** — Zero inbound links classifies candidate orphan under selected definition but does not label SEO defect automatically.
- **LNK-093** — Private/draft/non-indexable content follows configured graph inclusion policy and cannot leak existence to unauthorized reports.
- **LNK-094** — Navigation membership counts as inbound/root only when profile says so; widget/footer/global template links remain separately attributable.
- **LNK-095** — Redirected internal link can be represented as edge to source URL plus observed/known redirect rather than rewriting graph silently to final target.
- **LNK-096** — Duplicate destination patterns/isolated clusters are derived analytics and do not become source content ownership.
- **LNK-097** — Graph update after changed content removes stale edge only after new source revision is processed successfully.
- **LNK-098** — Large cyclic graph traversal uses visited/depth budgets and cannot recurse indefinitely.
- **LNK-099** — Cross-site network edge is classified separately and does not imply destination site Policy or shared ownership.

## Group 10 — scan schedule, JobService and host backpressure — LNK-100…110

- **LNK-100** — Manual scan creates durable Scan Run/Job identity tied to Scan Definition revision and target scope.
- **LNK-101** — Daily/weekly/monthly/custom schedule composes JobService and queued schedule occurrence is not reported as completed scan.
- **LNK-102** — Incremental changed-content scan uses source revision/checkpoint and does not rescan unchanged sources unnecessarily under certified profile.
- **LNK-103** — Post-import/migration/SearchReplace trigger deduplicates same change event and cannot enqueue unbounded duplicate scans.
- **LNK-104** — Per-host resource key caps concurrent network checks across workers/scans as configured.
- **LNK-105** — Host 429/Retry-After/backoff pauses that host without stalling unrelated hosts/site scans unnecessarily.
- **LNK-106** — Queue backpressure caps pending URLs/jobs/memory when extraction outpaces HTTP workers.
- **LNK-107** — Crash after a URL check but before checkpoint reconciles stored check identity before replay to avoid duplicate issue noise.
- **LNK-108** — Pause/resume preserves source/URL cursor and revalidates definition/site lifecycle before continuing.
- **LNK-109** — Cancel prevents future checks at safe boundary and does not mark unchecked URLs healthy.
- **LNK-110** — Scheduler/runtime delay is reported as actual start/freshness; “daily” does not mean exact clock execution guarantee.

## Group 11 — issue lifecycle, dedupe and history — LNK-111…121

- **LNK-111** — New confirmed occurrence creates stable issue key scoped by normalized URL + source identity + issue type under accepted dedupe contract.
- **LNK-112** — Repeated identical issue updates occurrence count/last-seen/history instead of creating duplicate issue rows indefinitely.
- **LNK-113** — Same broken URL in multiple source fields/entities yields distinct occurrences while supporting grouped issue view.
- **LNK-114** — Issue state transitions `new→confirmed→triaged→fix planned→fixed pending verification→verified` only through valid actions and evidence.
- **LNK-115** — Ignore/snooze records actor/reason/expiry and does not delete historical evidence.
- **LNK-116** — Previously healthy URL becoming broken opens/reopens issue according to history policy without erasing earlier healthy checks.
- **LNK-117** — Broken URL becoming healthy moves to verification/recovered state only after configured recheck evidence.
- **LNK-118** — Inconclusive after prior broken does not automatically close issue; status/history retain uncertainty.
- **LNK-119** — Severity/priority is derived/configurable and cannot be presented as proven business/SEO impact without separate evidence.
- **LNK-120** — Issue export/report applies source/URL Policy/redaction and cannot expose protected entity/query data.
- **LNK-121** — Retention/archive removes eligible old check history while preserving required current issue/audit facts and reports failed cleanup truthfully.

## Group 12 — Fix Plan integration with Redirect, SearchReplace and Media — LNK-122…132

- **LNK-122** — Replace-target proposal resolves exact source entity/field/occurrence and creates owner-aware Search/Transform Draft rather than editing content directly.
- **LNK-123** — Unlink proposal preserves surrounding content structure and requires owning content transformer evidence before mutation.
- **LNK-124** — Direct-final-target proposal from redirect chain is advisory until source/business intent is reviewed.
- **LNK-125** — Create-redirect proposal creates Redirect Manager Draft with source/destination evidence and does not publish automatically.
- **LNK-126** — Media regeneration proposal routes to Media owner and cannot recreate/delete file directly from Link Health.
- **LNK-127** — Restore/replace media proposal distinguishes local file identity from remote URL and requires appropriate owner/provider capability.
- **LNK-128** — Navigation-item fix uses navigation owner/Ability rather than raw postmeta/menu table mutation.
- **LNK-129** — Bulk URL map groups compatible occurrences and produces Dry Run/diff/backup class before any SearchReplace execution.
- **LNK-130** — Source revision changed after Fix Plan causes stale/conflict review rather than applying change to shifted occurrence offset blindly.
- **LNK-131** — Fix execution partial failure keeps per-owner operation state and issue remains pending until re-scan verifies final source/target.
- **LNK-132** — Verification re-scan is distinct from mutation success; “fix applied” cannot become “verified fixed” without check evidence.

## Group 13 — privacy, query redaction and protected sources — LNK-133…143

- **LNK-133** — Sensitive query keys are redacted in UI/log/export while normalized/hashing identity remains bounded to allowed purpose.
- **LNK-134** — Authorization, Cookie, session, reset, signed-token and similar secret-bearing headers/values are never stored in generic scan history.
- **LNK-135** — Scanner does not forward source-user credentials to arbitrary external target; authenticated profiles are explicit origin-bound adapters only.
- **LNK-136** — Protected/private source occurrence is visible only to authorized actor; aggregate counts cannot leak title/path/value beyond policy.
- **LNK-137** — External request log stores minimized host/status/timing/error metadata and avoids body/raw headers by default.
- **LNK-138** — URL containing personal identifier in path/query follows privacy classification/retention and redacted reporting.
- **LNK-139** — Link source content denied by Policy is not scanned through a lower-privilege REST/MCP request merely because URL extraction service can technically read DB.
- **LNK-140** — Public report/dashboard cannot expose private link inventory or infer existence from stable issue IDs/count differences outside allowed aggregation.
- **LNK-141** — Privacy export/erase identifies eligible scan metadata/history without deleting source business content owned elsewhere.
- **LNK-142** — Legal/retention hold can block deletion of authorized scan evidence scope; cleanup reports retained status instead of claiming erased.
- **LNK-143** — AI context includes only redacted/authorized link evidence and never receives credentials/secrets/protected source bodies by default.

## Group 14 — REST, Abilities, MCP and AI — LNK-144…154

- **LNK-144** — REST create Scan Draft validates source/site/network scope and returns no network requests until explicit Run.
- **LNK-145** — REST start/stop scan requires actor capability/Policy and exact Scan Definition revision.
- **LNK-146** — REST issue list applies source/issue Policy and query redaction per record.
- **LNK-147** — Ability `recheck` remains bounded to approved URL/issue and Safe HTTP; caller cannot supply arbitrary private-network target outside policy.
- **LNK-148** — Ability `create Fix Plan` is draft-only and cannot mutate content/redirect/media directly.
- **LNK-149** — MCP discovery is read-oriented by default and hides mutation/scan capabilities not authorized to current principal.
- **LNK-150** — MCP tool argument trying `http://169.254.169.254/` or private target is denied by Safe HTTP regardless of model/user prose.
- **LNK-151** — AI Prompt classifies/prioritizes issues from authorized evidence only and labels inferred destination/business impact as suggestion.
- **LNK-152** — Prompt injection in fetched page/title/changelog/source content remains untrusted and cannot alter tool permissions/approval.
- **LNK-153** — AI cannot auto-apply replacement target/redirect because it “looks obvious”; reviewed owner-aware Plan remains required.
- **LNK-154** — Audit records principal/channel/Scan/FixPlan while AI/MCP attribution never grants identity or authorization.

## Group 15 — Multisite, domain mapping and site lifecycle — LNK-155…165

- **LNK-155** — Site-owned Scan Definition resolves source/site/domain server-side and cannot scan another site by forged site ID.
- **LNK-156** — Network scan enumerates exact target sites and applies per-site source Policy instead of reading all site content implicitly.
- **LNK-157** — Same normalized path on two mapped domains remains separate site URL identity and issue namespace.
- **LNK-158** — Cross-site internal link is classified network-cross-site with destination site/domain identity but does not grant destination content access.
- **LNK-159** — Domain mapping change invalidates affected normalized URL/cache/issue identities through explicit migration rather than silent reassignment.
- **LNK-160** — Host rate limit shared across sites hitting same external host prevents aggregate network abuse while preserving per-site fairness.
- **LNK-161** — Site deletion/deactivation fences queued scans/HTTP work and archives/removes issue state via Site Lifecycle contract.
- **LNK-162** — Site clone does not copy active schedules/checkpoints as same execution identity; cloned scan definitions receive environment/site remap.
- **LNK-163** — Network issue dashboard respects per-site permissions and cannot reveal protected URLs from sites the viewer cannot inspect.
- **LNK-164** — Export/import of scan definitions requires destination domain/site mapping and strips credentials/authenticated scan profiles unless safely remapped.
- **LNK-165** — Large-network 100/1k-site scan profile includes fairness, domain dedupe and host concurrency evidence reservations without claiming executed scalability.

## Group 16 — 1k–1M URLs, 10M occurrences, failures and recovery — LNK-166…176

- **LNK-166** — 1k URL baseline profile records source count, unique URL count, occurrences, host distribution, hardware/software and cache state before performance claim.
- **LNK-167** — 100k unique URL profile measures extraction, dedupe, queue depth, HTTP concurrency, DB writes and issue aggregation under bounded resources.
- **LNK-168** — 1M URL profile requires partitioning/checkpoint/backpressure evidence and cannot be certified from extrapolation alone.
- **LNK-169** — 10M occurrence graph profile separates occurrence storage/query cost from unique URL check cost.
- **LNK-170** — Remote outage/DNS failure storm triggers host/global backoff and does not create millions of false “broken” confirmations.
- **LNK-171** — DB/cache/job outage leaves Scan partial/recoverable with durable checkpoint and does not advance unchecked URLs to completed.
- **LNK-172** — Crash after check/issue update reconciles request/check identity before retry to avoid duplicate history/count inflation.
- **LNK-173** — Malicious oversized HTML/redirect chain/header response respects body/header/hop/parser limits and does not exhaust worker memory/CPU.
- **LNK-174** — Security regression suite covers SSRF, DNS rebinding, credential redirect leak, protected-source leak, Policy bypass and unbounded host load.
- **LNK-175** — Backup/restore/clone invalidates stale active Scan checkpoints where environment/site/URL inventory no longer matches and requires reconciliation.
- **LNK-176** — Golden end-to-end fixture covers source discovery → normalization → internal/safe external check → classification → issue lifecycle → Fix Plan → owner mutation handoff → re-scan verification with uncertainty/privacy preserved.

## Stop-the-line conditions

Certification stops on SSRF/private-network access, credential leakage, protected URL/content leakage, false broken certainty for inconclusive responses, unbounded host load, source mutation without Plan, cross-site leakage or AI/MCP bypass of Safe HTTP/Policy.

## Execution gate

All 176 fixtures are documented only. No crawl, HTTP request, WordPress mutation, test, benchmark, provider/API/AI/MCP call or build has executed. ADR-0014 remains mandatory.