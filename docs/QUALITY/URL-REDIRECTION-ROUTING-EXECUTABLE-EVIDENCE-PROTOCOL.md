# WPEssential — URL Redirection & Routing Executable Evidence Protocol

Status: **Exact planning evidence / NOT EXECUTED / no development authorization**  
Date: 2026-08-29  
Work package: **WP113**  
Namespace: **RDR-001…RDR-176**  
Documented: **176/176**  
Executed: **0/176**  
Runtime certification: **0**

## Purpose

Freeze the exact executable-evidence fixtures for Surface 44 URL Redirection & Routing without running WordPress, HTTP, server-config, provider, AI/MCP, benchmark or mutation work. The canonical group ownership remains the 16 groups fixed by `MARKET-EXPANSION-EXECUTABLE-EVIDENCE-MASTER-PLAN.md` and the behavior contract in `MODULES/URL-REDIRECTION-ROUTING-EXHAUSTIVE-SPEC.md`.

## Truth boundaries

- Redirect match/simulator output is not authorization.
- A generated Apache/Nginx artifact is not proof that server configuration is active.
- An external target existence check is advisory and cannot bypass Safe HTTP/SSRF policy.
- Client-controlled cookie/header/user-agent/referrer conditions cannot grant protected access.
- AI/MCP may draft/analyze only through normal Policy; publication/bulk mutation remains governed.
- Unsupported export semantics must be reported as lossy/unsupported, never silently approximated.
- Fragment identifiers are not server-visible source-match authority.
- Unknown cache/server/provider outcome remains unknown until reconciled.

---

## Group 1 — identity, revisions, groups, priority — RDR-001…011

- **RDR-001** — Create a Draft redirect with name, stable UUID/key, site owner, group, enabled=false, priority, tags and revision; evidence must preserve identity across reads.
- **RDR-002** — Publish a validated Draft revision and prove the published revision is immutable while later edits create a new revision rather than rewriting history.
- **RDR-003** — Disable an enabled rule and prove definition/history remain intact while runtime eligibility becomes false; disable must not delete logs or source metadata.
- **RDR-004** — Disable a group containing mixed enabled rules and prove all member rules become ineligible without mutating each rule’s own enabled flag or revision.
- **RDR-005** — Two groups share the same priority band; prove deterministic group/rule ordering uses the accepted tie-break contract and never database retrieval order.
- **RDR-006** — Move a rule between groups and prove only the new published revision changes group ownership; prior execution/log references remain bound to their original revision.
- **RDR-007** — Attempt duplicate stable key/UUID within the same owner scope; creation must fail with typed identity conflict rather than silently aliasing/overwriting.
- **RDR-008** — Import a definition whose source provenance references another plugin; prove provenance is metadata only and grants no extra permissions or authority.
- **RDR-009** — Apply effective start/end scheduling and prove pre-start, active-window and post-end eligibility are deterministic in the configured timezone/profile.
- **RDR-010** — Concurrent editors update the same base revision; the second publish must detect stale revision and require rebase/review instead of last-write-wins overwrite.
- **RDR-011** — Archive/delete request for a definition referenced by logs/import plans or migration history must follow lifecycle policy and never erase required historical evidence silently.

## Group 2 — exact/prefix/wildcard/regex matching and budgets — RDR-012…022

- **RDR-012** — Exact path `/old-page` matches only the normalized exact source under the configured host/scheme profile; `/old-page/child` must not match.
- **RDR-013** — Prefix rule `/docs/` matches descendants but respects segment/boundary semantics so `/docs-old/` is not captured when boundary mode is enabled.
- **RDR-014** — Suffix matching accepts only the bounded supported grammar and proves a suffix rule cannot consume host/query components outside its declared source field.
- **RDR-015** — Wildcard/glob rule with one capture expands the intended bounded path set and proves wildcard syntax cannot become an unbounded regex/code primitive.
- **RDR-016** — Valid regex with named/numbered captures matches expected paths and produces typed captures for target substitution with deterministic encoding.
- **RDR-017** — Invalid regex syntax is rejected at validation/publish time and never reaches request evaluation as a runtime fatal.
- **RDR-018** — Catastrophic/backtracking-prone regex exceeds the configured complexity/execution budget and is rejected/quarantined with a typed diagnostic.
- **RDR-019** — Exact and regex rules both match one request; prove priority/specificity contract selects one deterministic winner and records shadowed candidates in diagnostics.
- **RDR-020** — Unicode path characters and percent-encoded equivalents are matched only after the selected normalization profile; no accidental double-decode occurs.
- **RDR-021** — A rule using WordPress object/permalink identity tracks the object’s canonical permalink reference without converting the object ID into broad request authorization.
- **RDR-022** — 100k-rule candidate-set plan proves exact-match fast path and bounded prefix/regex candidate selection are specified; paper estimates do not count as performance certification.

## Group 3 — URL normalization, case, trailing slash, query, encoding — RDR-023…033

- **RDR-023** — Case-sensitive source distinguishes `/Case` from `/case`; case-insensitive mode normalizes only according to the declared Unicode/case policy.
- **RDR-024** — Trailing-slash `exact` treats `/path` and `/path/` separately; `ignore/normalize` mode produces one canonical comparison identity without redirect loops.
- **RDR-025** — Repeated-slash normalization applies only when enabled and does not alter encoded path data or collapse scheme delimiters.
- **RDR-026** — Query mode `exact` requires the declared key/value set including duplicates/order semantics defined by profile; unmatched extra query data must not silently pass.
- **RDR-027** — Query mode `ignore` matches regardless of query content while target generation follows explicit pass/discard policy rather than inheriting by accident.
- **RDR-028** — `selected keys` query mode preserves/maps only allowlisted keys and proves sensitive/unselected parameters cannot leak to the destination.
- **RDR-029** — Percent-encoding normalization handles `%2F`, spaces, UTF-8 and reserved characters without unsafe double decoding or path traversal reinterpretation.
- **RDR-030** — Invalid percent encoding/control bytes are rejected or preserved under a typed invalid-input state; they must not become header injection or malformed target output.
- **RDR-031** — Host normalization handles IDN/punycode and case deterministically while keeping cross-host matching explicit rather than treating arbitrary Host headers as trusted ownership.
- **RDR-032** — Protocol-relative or scheme-variant source behavior follows explicit scheme policy; HTTP→HTTPS canonicalization must not create a flip-flop loop.
- **RDR-033** — Fragment supplied to simulator is recorded only as client-side diagnostic input and never influences server-side source match or protected routing decisions.

## Group 4 — conditions: login, capability, referrer, cookie, header, IP, time — RDR-034…044

- **RDR-034** — Logged-in condition uses authenticated WordPress principal state; a forged cookie alone cannot satisfy it.
- **RDR-035** — Capability condition calls server-side capability/Policy evaluation for the current resource context and cannot be replaced by role-name string matching.
- **RDR-036** — Membership/entitlement condition delegates to canonical Policy; redirect eligibility/visibility must not become entitlement or access authority itself.
- **RDR-037** — Referrer condition treats missing/malformed/spoofable referrer as ordinary input and never as authentication/authorization evidence.
- **RDR-038** — Cookie presence/value condition is privacy-classified and advisory; client-set cookie cannot unlock a protected route or resource.
- **RDR-039** — Header condition accepts only allowlisted header names/typed comparison and rejects control characters, multi-line injection and forbidden headers.
- **RDR-040** — IP/CIDR condition resolves client IP through the accepted trusted-proxy chain and rejects spoofed forwarding headers from untrusted peers.
- **RDR-041** — Schedule condition proves inclusive/exclusive boundaries, timezone conversion and DST transition semantics without using client clock as authority.
- **RDR-042** — Device/browser class condition changes routing presentation only and is explicitly prohibited from granting security-sensitive access.
- **RDR-043** — Geo/Territory condition remains unavailable/degraded until the F11 provider/profile is certified; missing geo result cannot be guessed as a match.
- **RDR-044** — Multiple conditions with AND/OR grouping produce deterministic short-circuit trace and preserve the exact condition revision used for the decision.

## Group 5 — actions, codes, errors, targets, capture substitution — RDR-045…055

- **RDR-045** — 301 action emits a valid permanent redirect only after target validation; method/body semantics that require preservation must not be silently converted from 307/308 behavior.
- **RDR-046** — 302/303/307/308 profiles preserve their declared HTTP method semantics and are distinguishable in simulator/evidence.
- **RDR-047** — Return 404/410/451 action emits the configured bounded response contract without exposing arbitrary response bodies/server code.
- **RDR-048** — `pass/no-op` action terminates/continues exactly as defined by group execution semantics and cannot accidentally trigger a later conflicting action.
- **RDR-049** — Site-relative target resolves against the server-owned canonical site origin rather than attacker-controlled Host/forwarded headers.
- **RDR-050** — WordPress entity target resolves current permalink at execution/simulation time under the owning site and reports missing/private/unavailable target distinctly.
- **RDR-051** — External absolute target requires allowed `http/https` scheme, normalized host and configured external-host policy/warning.
- **RDR-052** — Regex capture substitution URL-encodes path/query output by destination context and rejects missing/out-of-range captures.
- **RDR-053** — Mapped query parameters distinguish missing, empty and repeated values and cannot smuggle CR/LF or unsafe schemes into the Location header.
- **RDR-054** — Dynamic target from DVR is allowed only from registered typed sources; untrusted request values cannot become an unrestricted redirect destination.
- **RDR-055** — Registered internal route/Ability action remains disabled unless its explicit certified profile exists; ordinary redirect definitions cannot execute arbitrary PHP/SQL/shell logic.

## Group 6 — open redirect, header injection, unsafe scheme, adversarial URL safety — RDR-056…066

- **RDR-056** — Target beginning `javascript:` is rejected even when encoded/whitespace-obfuscated; no unsafe-scheme redirect can publish.
- **RDR-057** — `data:`, `file:`, `php:`, `gopher:` and other non-allowlisted schemes are rejected by target validation.
- **RDR-058** — Target containing CR/LF percent-encoded or literal control characters cannot produce extra response headers or split Location output.
- **RDR-059** — Protocol-relative external target `//evil.example` is normalized/classified as external and cannot bypass external-host policy.
- **RDR-060** — User-controlled query/cookie/header capture cannot form a free-form host target; host substitutions require a bounded allowlist/typed map.
- **RDR-061** — Unicode/punycode homograph target is displayed/canonicalized sufficiently for review and does not bypass host allowlists through alternate representation.
- **RDR-062** — Backslash, mixed slash, dot-segment and encoded traversal inputs are normalized under URL rules without creating filesystem traversal semantics.
- **RDR-063** — Target existence check to loopback/private/link-local/cloud-metadata address is blocked by Safe HTTP SSRF policy before network access.
- **RDR-064** — Redirected target-existence check revalidates every hop; public→private redirect cannot bypass SSRF controls.
- **RDR-065** — Response-header action rejects hop-by-hop/forbidden headers and invalid names/values; security-header ownership conflicts are surfaced instead of overridden silently.
- **RDR-066** — Malicious imported rule combining encoded scheme, CR/LF and regex capture is rejected atomically and produces a sanitized diagnostic with no secret/request-data leak.

## Group 7 — loops, chains, collisions, shadowing, priority — RDR-067…077

- **RDR-067** — Direct self-loop `/a → /a` after normalization is detected before publish and cannot become active.
- **RDR-068** — Two-rule cycle `/a→/b`, `/b→/a` is detected across groups and reported with exact rule/revision chain.
- **RDR-069** — Multi-host/scheme loop (HTTP→HTTPS plus conflicting HTTPS→HTTP rule) is detected after canonical host/scheme normalization.
- **RDR-070** — Three-plus-hop chain is reported with hop count and final target; collapse is a proposal only and never auto-mutates rules.
- **RDR-071** — Duplicate normalized exact sources in same effective scope cause deterministic collision handling instead of nondeterministic winner selection.
- **RDR-072** — Exact rule shadowed by higher-priority prefix/regex produces an explicit reachability warning and simulator evidence.
- **RDR-073** — Prefix rules overlap; longest/specificity and configured priority semantics choose the accepted winner consistently.
- **RDR-074** — Regex rule that subsumes another regex is marked potential shadowing with bounded static analysis; uncertain analysis is labeled uncertain, not certain.
- **RDR-075** — Disabled/expired rule is excluded from active chain analysis but remains visible in historical/revision diagnostics.
- **RDR-076** — External-return chain observed through Safe HTTP is advisory because remote behavior may change; it cannot be treated as immutable WPE graph truth.
- **RDR-077** — Concurrent publication of colliding rules rechecks active generation under lock/version precondition so both cannot become silently conflicting winners.

## Group 8 — permalink monitoring, migration, duplicate prevention — RDR-078…088

- **RDR-078** — Post permalink change records canonical old/new URLs and creates Draft redirect when profile is `Draft` without changing content ownership.
- **RDR-079** — `Active` auto-create profile still performs duplicate/loop/policy checks; failure leaves a visible unresolved migration event rather than silent omission.
- **RDR-080** — `Ask` profile creates a reviewable proposal and never publishes merely because the permalink hook fired.
- **RDR-081** — Included/excluded post-type configuration is honored; private/non-public types are not monitored unless explicitly supported.
- **RDR-082** — Taxonomy term permalink monitoring uses stable term identity and does not confuse term slug reuse across taxonomies/sites.
- **RDR-083** — Repeated edits A→B→C prevent duplicate A→B/B→C chains when accepted collapse policy can propose A→C with provenance.
- **RDR-084** — Existing manually-authored redirect on old URL triggers collision/review rather than being overwritten by automatic permalink monitoring.
- **RDR-085** — WordPress old-slug compatibility is detected as an existing behavior source and does not cause redundant duplicate redirect creation without policy.
- **RDR-086** — Bulk permalink-structure migration previews affected URLs/rules and produces a versioned Plan; preview alone changes nothing.
- **RDR-087** — Rollback planning for permalink migration restores configuration/content through owning systems and does not claim external caches/search engines can be rolled back.
- **RDR-088** — Crash/partial migration preserves journaled created rules and unresolved items so retry is idempotent and does not duplicate redirects.

## Group 9 — 404 logging, privacy, retention, log pollution — RDR-089…099

- **RDR-089** — `disabled` 404 profile stores no detailed request rows; aggregate/dashboard must not imply data was captured.
- **RDR-090** — `aggregate-only` profile records bounded normalized counts without raw IP/query/referrer values.
- **RDR-091** — Detailed profile redacts query values by classification and stores only explicitly enabled referrer/user-agent/IP fields.
- **RDR-092** — IP `off/anonymized/truncated/full` modes yield distinct storage behavior and full IP requires explicit privacy/purpose policy.
- **RDR-093** — Logged-in user attribution is optional, Policy-gated and not used to expose private request history to unauthorized roles.
- **RDR-094** — Dedupe bucket increments occurrence counts atomically under concurrent identical 404s instead of creating unbounded duplicate rows.
- **RDR-095** — Ignore/noise/scanner filters suppress configured noise without deleting historical rows outside retention policy.
- **RDR-096** — Retention job removes eligible detailed rows while preserving only allowed aggregate/audit facts; failed deletion is reported, not assumed.
- **RDR-097** — Log export applies Policy, privacy redaction and spreadsheet/CSV formula safety; request URL fields cannot inject formulas.
- **RDR-098** — Adversarial high-cardinality/random-path flood triggers rate/storage budgets and cannot exhaust DB/storage indefinitely.
- **RDR-099** — AI/nearest-target suggestion for a 404 is clearly advisory and cannot auto-create/publish a redirect without a reviewed Plan.

## Group 10 — headers, server profiles, Apache/Nginx lossiness — RDR-100…110

- **RDR-100** — Allowlisted redirect-response header can be appended/replaced/removed according to exact scope and revision.
- **RDR-101** — Header name/value validation rejects CR/LF, invalid token names and forbidden hop-by-hop headers.
- **RDR-102** — CSP/HSTS/security-header conflict with Protector ownership is surfaced and routes to the owning security profile rather than creating competing control planes.
- **RDR-103** — Apache export of a simple exact redirect produces a deterministic supported representation with source revision metadata where format permits.
- **RDR-104** — Nginx export of a supported exact/prefix rule produces deterministic output but remains an artifact, not proof of active server configuration.
- **RDR-105** — Conditional rule unsupported by Apache profile is marked unsupported/lossy and is never silently flattened into an unconditional redirect.
- **RDR-106** — Regex semantics differing between WPE/Apache/Nginx trigger a compatibility warning or unsupported result instead of false equivalence.
- **RDR-107** — Header action unsupported by target server profile is itemized separately from redirect export success.
- **RDR-108** — Import from `.htaccess` parses only the certified subset; unknown directives remain untouched/unimported with diagnostics and no execution.
- **RDR-109** — Server-config write/activation, when a future adapter exists, requires separate provider/permission evidence; export success cannot set status `active_on_server`.
- **RDR-110** — Re-importing WPE-generated server artifact is idempotent or produces explicit duplicates/collisions rather than multiplying semantically identical rules.

## Group 11 — import/export, CSV/JSON/plugin adapters/WP-CLI — RDR-111…121

- **RDR-111** — WPE JSON package round-trip preserves stable semantics, revision/provenance and supported typed conditions/actions without secrets/log data.
- **RDR-112** — Unknown future package schema/version is rejected or explicitly migrated; it is never interpreted as current schema silently.
- **RDR-113** — CSV import validates required headers/types, trims only declared fields and reports row-level errors without shifting columns.
- **RDR-114** — CSV cells beginning `=`, `+`, `-`, `@` are escaped safely on export to prevent spreadsheet formula injection.
- **RDR-115** — Duplicate imported source against existing rule produces configured merge/skip/conflict result with no silent overwrite.
- **RDR-116** — Import adapter for a known redirect plugin maps only documented semantics and reports unmapped conditions/actions as lossiness.
- **RDR-117** — Partial import failure records applied/skipped/failed identities in a journal so retry does not duplicate successful rows.
- **RDR-118** — Export filtered by group/site preserves Policy scope and cannot leak rules/log metadata from another tenant/site.
- **RDR-119** — WP-CLI dry-run/import requires explicit site/network scope and plan fingerprint; noninteractive execution cannot infer broad scope from current directory alone.
- **RDR-120** — Import containing unsafe scheme/header injection/invalid regex fails validation before publish even if source plugin previously accepted it.
- **RDR-121** — Import/export never embeds Vault credentials, auth cookies, private request logs or provider secrets in portable definition packages.

## Group 12 — simulator, diagnostic trace, cache-generation truth — RDR-122…132

- **RDR-122** — Simulator normalizes a supplied URL and returns candidate count, ordered evaluation and selected rule without writing hit/log counters by default.
- **RDR-123** — Authorized simulated login/capability context is clearly synthetic and cannot create a real authenticated session or grant live access.
- **RDR-124** — Simulated header/cookie/referrer inputs are treated as test data and cannot bypass real Policy in any linked resource preview.
- **RDR-125** — Trace includes skipped disabled/expired groups and exact reason while avoiding secret/raw protected values.
- **RDR-126** — Target-render trace shows capture/query transformations and validation warnings with deterministic escaped output.
- **RDR-127** — Loop/chain analyzer runs against the same published generation as match simulation; mixed generations are rejected as stale diagnostics.
- **RDR-128** — Compiled cache key includes site/network scope and published generation so site A rules cannot serve site B.
- **RDR-129** — Rule/group publication invalidates/advances compiled generation; old cache entries cannot be reported as current after successful activation.
- **RDR-130** — Cache rebuild failure leaves previous known generation or typed degraded state according to risk profile; it never claims new rules active without proof.
- **RDR-131** — Diagnostics distinguish definition published, cache compiled and server-profile exported/activated states as separate facts.
- **RDR-132** — Simulation of a stale requested revision returns explicit historical/stale context and cannot be mistaken for current production routing truth.

## Group 13 — REST, Abilities, MCP, AI Prompt authorization — RDR-133…143

- **RDR-133** — REST list/get returns only actor-visible rules/groups under site/network Policy and does not expose protected logs by default.
- **RDR-134** — REST create/update produces Draft only unless the actor has the separate publish capability/approval required by policy.
- **RDR-135** — Ability `simulate` remains read-only and cannot mutate hit counters, redirect status or unrelated resource authorization.
- **RDR-136** — Ability `publish/enable/disable` validates current revision, actor capability, target scope and approval profile before mutation.
- **RDR-137** — MCP discovery exposes only explicitly opt-in redirect abilities/resources and only those visible to the authenticated WordPress principal.
- **RDR-138** — MCP client attempting arbitrary PHP/SQL/shell routing finds no such capability; typed routing actions are allowlisted only.
- **RDR-139** — AI Prompt creates typed Draft redirect/Plan IR and unsupported fields/options are rejected by deterministic schema validation.
- **RDR-140** — Prompt injection embedded in source URL/log/referrer is untrusted data and cannot alter system permissions/tool allowlist.
- **RDR-141** — AI proposal containing unsafe external host/scheme fails ordinary target validation and cannot be privileged because it is AI-generated.
- **RDR-142** — Bulk AI redirect remediation requires explicit reviewed Plan/fingerprint; changes to underlying revisions invalidate stale approval where configured.
- **RDR-143** — REST/MCP/AI audit attribution identifies channel/principal/session without treating AI agent metadata as authentication authority.

## Group 14 — concurrency, cache, performance 1k–100k rules — RDR-144…154

- **RDR-144** — Two concurrent publishers touching different rules produce one coherent monotonically advancing compiled generation without lost updates.
- **RDR-145** — Concurrent edits to same rule use revision preconditions and second writer receives conflict instead of silent overwrite.
- **RDR-146** — Cache stampede after generation invalidation is bounded by lock/single-flight strategy; correctness does not rely on timing luck.
- **RDR-147** — Exact-match 1k-rule benchmark profile records hardware/software/dataset/query distribution before any performance claim.
- **RDR-148** — 10k mixed exact/prefix rules profile measures candidate count, DB/query count, latency and memory; paper targets remain unexecuted until run.
- **RDR-149** — 100k mixed rule profile includes bounded regex subset and proves planned index/generation shape without promising unsupported production scale before evidence.
- **RDR-150** — Regex budget enforcement under adversarial inputs prevents one request from monopolizing CPU beyond configured resource limit.
- **RDR-151** — Logging path under high redirect traffic is asynchronous/batched where profile says so and cannot block request path indefinitely due to log storage outage.
- **RDR-152** — High-cardinality 404 flood applies storage/rate budgets independently from redirect matching throughput.
- **RDR-153** — Cache eviction/cold start preserves correctness; performance degradation cannot cause wrong-rule selection or cross-site cache reuse.
- **RDR-154** — Performance result is invalidated when WordPress/PHP/DB/cache profile materially changes unless re-evidenced for that supported profile.

## Group 15 — Multisite, domain mapping, site lifecycle — RDR-155…165

- **RDR-155** — Same normalized source path may exist independently on site A and site B with separate rule identity/generation and no cross-site collision.
- **RDR-156** — Request-supplied `site_id` cannot select another site’s routing rules; owner scope is resolved server-side from trusted site/domain mapping.
- **RDR-157** — Network-owned rule/template applies only to explicitly authorized bound sites/domains and records network provenance separately from site overrides.
- **RDR-158** — Site admin cannot alter enforced network routing/security floor outside delegated capability.
- **RDR-159** — Domain-mapping collision between sites is detected as configuration ambiguity; WPE does not guess target tenant from conflicting host claims.
- **RDR-160** — Cross-site redirect is treated as explicit external/network target and does not grant access to protected content on destination site.
- **RDR-161** — Site clone creates/remaps rule identities, domains and environment-sensitive targets according to clone profile; live provider/server activation is quarantined.
- **RDR-162** — Site transfer/domain change produces reviewed redirect/search-replace mapping Plan rather than blind string substitution across network-owned rules.
- **RDR-163** — Site deletion/deactivation disables/fences active site-owned routing generation before lifecycle cleanup and preserves required historical evidence.
- **RDR-164** — Network export/import preserves per-site ownership and rejects ambiguous mapping when destination site/domain identities are missing.
- **RDR-165** — Network-wide diagnostics/reporting applies per-site Policy and does not reveal protected 404/log data merely because actor can view network routing metadata.

## Group 16 — upgrade, failure, recovery, coexistence, security regression — RDR-166…176

- **RDR-166** — Schema upgrade migrates definition/revision data idempotently; interruption resumes without publishing partially migrated rules.
- **RDR-167** — Downgrade/unsupported newer schema enters typed read-only/degraded state instead of corrupting or silently rewriting definitions.
- **RDR-168** — Storage/DB failure during publish does not claim new rule active; transaction/journal truth identifies committed vs uncommitted state.
- **RDR-169** — Compiled-cache write failure distinguishes published definition from effective generation and provides safe retry/rebuild path.
- **RDR-170** — Apache/Nginx export/write adapter failure cannot roll back/alter WordPress redirect truth silently; external activation remains separately reconciled.
- **RDR-171** — Coexistence with WordPress canonical redirects/old slugs identifies double-redirect/loop risk without hijacking core behavior blindly.
- **RDR-172** — Coexistence with another redirect plugin detects overlapping ownership where possible and reports ambiguity rather than claiming deterministic external execution order.
- **RDR-173** — Recovery from backup restores definitions/revisions but does not claim external server/CDN configuration was rolled back; external state requires reconciliation.
- **RDR-174** — Security regression suite replays open-redirect, CRLF, unsafe-scheme, regex DoS, SSRF, Policy-bypass and cross-site isolation fixtures before certification.
- **RDR-175** — AI/MCP/provider unavailable leaves deterministic manual redirect management usable and cannot turn a failed suggestion/tool call into an active redirect.
- **RDR-176** — Golden end-to-end migration fixture covers permalink change → Draft/approval → publish → compiled generation → request match → log/privacy → rollback/recovery with every fact separated and no false runtime/provider claim.

## Stop-the-line conditions

Future certification stops on any open redirect, CR/LF/header injection, unsafe scheme, unbounded regex path, cross-site rule leakage, Policy bypass, silent export lossiness, false server-activation claim, unbounded log pollution, or AI/MCP publish path bypassing approval.

## Execution gate

All 176 fixtures are **documented only**. No fixture has executed. ADR-0014 explicit scoped owner consent remains mandatory before runtime/source/build/test/provider/AI/MCP work.