# WPEssential — Safe Script, Tag & Code Injection Exact Executable-Evidence Protocol

Status: **Planning-only exact protocol / NOT EXECUTED**  
Date: 2026-08-29  
Work package: **WP114**  
Namespace: **STM-001…STM-176**

## Contract

This protocol expands the fixed STM groups into exact individual fixtures for Surface 50. It is limited to controlled browser-side/declarative output. Server-side PHP/eval/SQL/shell/custom executable code remains outside this surface and requires the reviewed Extension SDK path plus separate development authorization.

Truth boundaries: snippet editor access ≠ publish authority; condition/visibility ≠ authorization; preview ≠ browser execution proof; consent category ≠ legal compliance; CSP compatibility ≠ permission to weaken CSP; Vault secret ≠ frontend token source; import ≠ trusted/active code.

Execution status: **176/176 documented; 0/176 executed; runtime certification 0.**

## Group 1 — Snippet definition/type parsing — STM-001…011
- **STM-001** — Snippet definition has stable key/UUID, Draft/Active/Paused/Archived state, type, risk class, revision and environment scope.
- **STM-002** — External JavaScript type accepts declarative URL/attributes only and cannot embed PHP/server callback.
- **STM-003** — Inline JavaScript type is explicitly privileged browser code and is never described as sanitized-safe execution.
- **STM-004** — CSS type parses as CSS profile and rejects PHP tags/server directives.
- **STM-005** — HTML fragment type follows selected sanitizer/risk profile; embedded scripts are extracted/rejected unless explicit script type.
- **STM-006** — Plain-text type is escaped as text and cannot become markup/script by token interpolation.
- **STM-007** — Meta/Link tag definitions use typed attribute schema for supported cases rather than arbitrary critical raw markup by default.
- **STM-008** — JSON-LD parses valid JSON and rejects JavaScript expressions/functions/comments outside allowed JSON profile.
- **STM-009** — Iframe/widget definition validates src/sandbox/referrer/allow attributes against safe profile.
- **STM-010** — Submitted PHP/shell/SQL/server-template type is rejected with Extension Plan guidance; no unsafe mode stores it as executable snippet.
- **STM-011** — Import/type parser preserves unknown/unsupported type as unresolved Draft and never guesses executable semantics.

## Group 2 — Frontend placements/hook adapters — STM-012…022
- **STM-012** — Head-early placement maps only to registered/certified frontend hook/slot and preserves ordering contract.
- **STM-013** — Head-normal/late placements resolve deterministic relative order without arbitrary hook-name execution.
- **STM-014** — After-opening-body placement activates only when theme/Core supports registered body hook; missing hook is diagnosed, not emulated with unsafe output buffering.
- **STM-015** — Before-closing-body/footer placement remains frontend-only and does not spill into admin/login scopes.
- **STM-016** — Content-before placement escapes/authorizes resource context and cannot reveal protected tokens merely because content renders.
- **STM-017** — Content-after placement follows same source/resource Policy and cache-context safety.
- **STM-018** — Component/slot placement uses registered Placement/Component adapter identity, not arbitrary callable/hook text.
- **STM-019** — Shortcode/block/manual token renders only selected published snippet revision and respects Policy/condition/consent/security gates.
- **STM-020** — Selected theme hook is accepted only through certified Hook Adapter allowlist.
- **STM-021** — Once-per-request/page-render semantics prevent duplicate output when same adapter path is encountered multiple times.
- **STM-022** — Missing/disabled placement adapter yields explicit inactive/degraded state and cannot silently choose a different injection point.

## Group 3 — Admin/login advanced placements — STM-023…033
- **STM-023** — Login head placement requires separate login-scope capability and keeps WordPress authentication independent.
- **STM-024** — Login footer placement cannot hide reset/privacy/security controls through ordinary snippet publish policy.
- **STM-025** — wp-admin head placement requires separately privileged admin-scope capability and is not available to normal frontend publisher.
- **STM-026** — wp-admin footer placement remains presentation/browser code only and cannot authorize admin actions.
- **STM-027** — Site Editor/editor-shell placement activates only through certified adapter for current WordPress version/profile.
- **STM-028** — Admin/login snippet conditions cannot use UI hiding to bypass destination capability/security checks.
- **STM-029** — Safe mode can disable all non-essential admin/login injected code without preventing native recovery path.
- **STM-030** — Admin snippet failure/error cannot white-screen wp-admin; output isolation/fallback is required by later runtime evidence.
- **STM-031** — Login snippet cannot leak account-existence information through user-specific output before authentication.
- **STM-032** — Network Admin placement requires Network Admin/Super Admin Policy and ordinary site admin cannot publish into it.
- **STM-033** — Advanced placement diagnostics identify scope/owner/revision without embedding snippet secrets or private user context.

## Group 4 — Ordering/dependency/cycle handling — STM-034…044
- **STM-034** — Numeric priority has deterministic stable tie-breaker and identical priority does not yield nondeterministic order.
- **STM-035** — `load before` dependency resolves by stable snippet identity and missing target produces explicit unresolved state.
- **STM-036** — `load after` dependency cannot cross incompatible placement/scope without diagnostic/conflict.
- **STM-037** — Direct dependency cycle is detected before publish.
- **STM-038** — Multi-node cycle is detected and publish blocks affected graph.
- **STM-039** — Mutual-exclusion group selects according to explicit rule/context and never outputs both accidentally.
- **STM-040** — Duplicate external script URL detection normalizes safe URL identity and reports duplicate rather than automatically deleting one owner.
- **STM-041** — Detectable duplicate global/tag ID warning is advisory and does not claim complete JavaScript semantic analysis.
- **STM-042** — Dependency on paused/archived snippet yields blocked/degraded child according to declared policy.
- **STM-043** — Revision change in dependency invalidates compiled ordering/cache generation.
- **STM-044** — Graph export/import preserves stable dependencies or reports unresolved mappings instead of relinking by display name.

## Group 5 — Conditional Logic/context escaping — STM-045…055
- **STM-045** — Site/home/post/CPT/resource condition uses registered route/resource identity and not arbitrary URL-string trust.
- **STM-046** — Taxonomy/archive/search/404 conditions resolve through canonical context provider with explicit unknown state.
- **STM-047** — Logged-in/out condition affects presentation/output only and grants no protected resource authority.
- **STM-048** — Role/Capability condition is presentation filter and direct operation/resource authorization remains separate.
- **STM-049** — Membership/Entitlement condition may suppress browser output but cannot grant entitlement/access.
- **STM-050** — Locale/language/environment condition uses typed values and safe cache segmentation.
- **STM-051** — Device class is coarse/advisory and never a security condition.
- **STM-052** — Query-parameter condition accepts only allowlisted keys/typed values and does not reflect unescaped input into output.
- **STM-053** — Referrer/origin condition is privacy/security bounded and not treated as authenticated identity.
- **STM-054** — Woo/experiment/placement context comes only through certified adapter/foundation and does not duplicate business authority.
- **STM-055** — Dynamic token interpolation uses output-context-specific escaping (text/HTML/URL/JS/JSON/CSS) and denied secret/protected values remain unavailable.

## Group 6 — External script URL/origin/SRI attributes — STM-056…066
- **STM-056** — HTTPS is required by default; HTTP requires separately allowed compatibility profile and explicit warning.
- **STM-057** — `javascript:`, `data:`, `file:` and other unsafe executable/local schemes are rejected for external script URL.
- **STM-058** — Origin is parsed/canonicalized and matched against configured allowlist/profile; userinfo/host-confusion tricks fail.
- **STM-059** — Redirected remote script origin, if runtime fetching/validation ever occurs, must revalidate each hop; original allowlist alone is insufficient.
- **STM-060** — Script type classic/module is typed and unsupported attributes are not silently reinterpreted.
- **STM-061** — async/defer/loading strategy combinations are validated for declared type/placement and diagnostics show effective choice.
- **STM-062** — crossorigin/referrerpolicy values are allowlisted enums, not raw header strings.
- **STM-063** — SRI integrity value validates algorithm/digest syntax and mismatch later is browser/security failure, not silently removed.
- **STM-064** — nonce mode references CSP adapter-generated nonce; static stored nonce reuse is prohibited.
- **STM-065** — Data attributes have validated names/escaped values and cannot inject event handlers/markup.
- **STM-066** — External resource failure reporting does not leak full sensitive URL/query/token and never triggers automatic CSP weakening.

## Group 7 — Inline JavaScript privilege/CSP — STM-067…077
- **STM-067** — Inline JavaScript create/update and publish are separate privileges; viewing snippet does not grant publish-high-risk.
- **STM-068** — Syntax validation catches parse errors but is not presented as proof code is safe/non-malicious.
- **STM-069** — Max-size/resource budget rejects unbounded browser-code payloads.
- **STM-070** — PHP interpolation/server-side eval markers are rejected; only typed data tokens through JS-safe JSON encoding are allowed.
- **STM-071** — Vault/secrets are non-renderable into frontend JavaScript by default and secret token reference fails validation.
- **STM-072** — CSP nonce integration uses per-response nonce from owning security profile.
- **STM-073** — CSP hash generation, if supported, binds exact inline bytes/revision and changes when content changes.
- **STM-074** — Enforced CSP conflict blocks publish or reports explicit unsupported state; WPE cannot auto-add `unsafe-inline`.
- **STM-075** — Environment restriction is evaluated server-side before output and staging/debug code cannot appear in production due client toggle.
- **STM-076** — Minification remains off/unsupported until deterministic certified build service exists; no opaque runtime transform claim.
- **STM-077** — AI-generated inline JS remains Draft/high-risk and cannot auto-publish or request secrets.

## Group 8 — CSS/HTML/JSON-LD typed safety — STM-078…088
- **STM-078** — CSS syntax validation rejects broken declarations and blocks legacy unsafe constructs such as `expression()`.
- **STM-079** — CSS asset URLs validate scheme/origin/privacy profile and cannot access frontend secrets.
- **STM-080** — Scoped CSS mode applies only where selector scoping is certified; unsafe guessed scoping is not claimed.
- **STM-081** — Admin visual customization request is redirected toward Admin Theme when semantic token solution exists, avoiding duplicate control plane.
- **STM-082** — Sanitized HTML profile strips scripts/event handlers/unsafe URLs according to declared sanitizer contract.
- **STM-083** — Trusted/raw browser-markup advanced profile remains separately privileged and still rejects PHP/server tags.
- **STM-084** — Iframe profile validates sandbox/allow/referrerpolicy/source and cannot silently remove sandbox restrictions to make widget work.
- **STM-085** — Typed meta builder blocks duplicate charset/viewport/canonical-like critical tags where Core/SEO owner conflict exists.
- **STM-086** — Typed link builder validates rel/as/type/href/crossorigin combinations and unsupported preload cannot be silently emitted.
- **STM-087** — JSON-LD dynamic tokens are JSON-encoded as data, not executable expressions/string concatenation injection.
- **STM-088** — SEO structured-data conflict is surfaced and WPE does not auto-delete/override third-party schema without reviewed ownership choice.

## Group 9 — Consent category/blocking/withdrawal — STM-089…099
- **STM-089** — Consent category is explicit per snippet; `strictly necessary` cannot be selected by unauthorized publisher to bypass consent governance.
- **STM-090** — Non-essential snippet remains blocked until owning consent adapter reports required allowed state.
- **STM-091** — Missing/unknown consent state is not treated as granted.
- **STM-092** — Consent adapter/source/version is recorded in diagnostic provenance without claiming legal sufficiency.
- **STM-093** — Placeholder/fallback before consent contains no blocked vendor script/cookie/storage side effect.
- **STM-094** — WPE itself does not set non-essential cookie/storage before consent simply to manage blocked snippet.
- **STM-095** — Consent withdrawal stops future WPE-controlled output/execution where technically possible and invalidates relevant cache.
- **STM-096** — Withdrawal cannot promise deletion/reversal of third-party data already transmitted; UI wording remains truthful.
- **STM-097** — Full-page cache cannot serve consented user’s marketing script to non-consented user unless cache segmentation is certified.
- **STM-098** — Region/custom legal category selection is policy/provider configuration, not WPE legal advice.
- **STM-099** — AI cannot classify vendor as “necessary” or legally compliant without configured policy/authority; draft requires review.

## Group 10 — CSP/nonces/hashes/security-header integration — STM-100…110
- **STM-100** — Snippet security plan reads enforced/report-only CSP from owning Protector/Security Header profile.
- **STM-101** — External origin preview reports required `script-src`/related directives without automatically mutating them.
- **STM-102** — Nonce injection is per response and cannot be cached/reused as static snippet field.
- **STM-103** — Inline hash uses exact canonical output bytes and algorithm approved by CSP profile.
- **STM-104** — Dynamic inline token content invalidates static hash approach unless nonce/other certified profile is used.
- **STM-105** — Snippet publish conflicting with enforced CSP blocks/fails according to policy rather than silently weakening header.
- **STM-106** — Report-only violation diagnostics are observational and not proof enforced policy would behave identically.
- **STM-107** — CSP/security-header modification requires separate security capability/approval from ordinary snippet publish.
- **STM-108** — Network security floor can prohibit inline JS/non-allowlisted origins and site admin cannot override it.
- **STM-109** — Security-header cache/revision mismatch invalidates snippet compatibility result before output.
- **STM-110** — Emergency snippet pause can reduce offending output without deleting or broadening CSP policy.

## Group 11 — Environment profiles/cache/minifier coexistence — STM-111…121
- **STM-111** — Production-only snippet resolves environment from trusted server profile, not client parameter.
- **STM-112** — Staging-only/development-only snippet remains absent in production even if imported from clone.
- **STM-113** — Clone/import of production-only binding enters review-required state when destination environment identity differs.
- **STM-114** — Environment mismatch is shown in diagnostics and does not silently change snippet state.
- **STM-115** — Snippet output cache key includes revision, environment, condition and consent dimensions required by profile.
- **STM-116** — Full-page cache integration prevents consent/user-context leakage or disables contextual snippet caching when unsafe.
- **STM-117** — Cache purge/version unknown outcome is not treated as proof old snippet disappeared immediately.
- **STM-118** — Minifier/optimizer plugin detection reports possible rewrite/order conflict and WPE does not claim final browser bytes unchanged.
- **STM-119** — CDN edge insertion/transform ownership is detected through adapter where available and duplicate injection is reported.
- **STM-120** — jQuery/library version conflict detection is advisory/diagnostic and cannot arbitrarily replace third-party dependency.
- **STM-121** — Safe mode bypasses WPE non-essential output without requiring cache deletion to be falsely claimed instantaneous; residual cache risk is reported.

## Group 12 — Validation/preview/diagnostics — STM-122…132
- **STM-122** — Static preview shows parsed/sanitized/escaped output for selected revision without executing production browser script.
- **STM-123** — Placement preview resolves registered hook/slot and reports unsupported/missing adapter.
- **STM-124** — Condition preview uses simulated context and is labelled simulation, not live authorization.
- **STM-125** — Consent simulation cannot change real consent state or write cookies/storage.
- **STM-126** — CSP compatibility preview reads pinned security profile and reports required/conflicting directives.
- **STM-127** — Dependency/order preview shows deterministic graph, cycles, missing dependencies and mutual exclusion.
- **STM-128** — External-origin inventory is canonicalized/redacted and distinguishes allowed, blocked, unknown.
- **STM-129** — Dynamic-token preview uses safe sample/redacted values; production secret/protected values are not exposed.
- **STM-130** — Diagnostics show active/inactive reason, condition/consent/CSP result and placement encounters without dumping arbitrary page content.
- **STM-131** — Browser telemetry/load-error collection, if enabled later, is opt-in/privacy-bounded and not treated as universal execution proof.
- **STM-132** — Preview/validation success is explicitly not runtime/browser/vendor-compliance certification.

## Group 13 — Revisions/rollback/emergency kill switch — STM-133…143
- **STM-133** — Publish creates immutable snippet revision with content fingerprint, placement/condition/risk/environment/consent/CSP diff.
- **STM-134** — Concurrent edits use revision/precondition; stale publisher cannot overwrite newer code silently.
- **STM-135** — Rollback restores selected prior revision only after revalidation against current CSP/consent/environment/profile.
- **STM-136** — Rollback does not claim reversal of third-party network/storage effects caused by prior execution.
- **STM-137** — Pause-one-snippet immediately changes WPE canonical state and cache invalidation is tracked truthfully.
- **STM-138** — Pause-group targets explicit group/site scope and cannot pause network-enforced security snippet without authority.
- **STM-139** — Safe-mode disable-all-non-essential keeps strictly required/recovery/admin baseline usable and is separately audited.
- **STM-140** — Network emergency pause requires Network Admin/Super Admin Policy and lists affected sites.
- **STM-141** — Failed publish/artifact/cache update leaves prior active revision or explicit degraded state; no half-published mixed revision.
- **STM-142** — Emergency action audit records actor/reason/scope/revision but never embeds Vault secrets/private user data.
- **STM-143** — Recovery from broken admin/login snippet is possible through native/server-side safe path that does not depend on the offending snippet rendering.

## Group 14 — Import/export/WPCode/simple-script migration — STM-144…154
- **STM-144** — Export includes definition/content/conditions/placements/consent/security/environment/dependencies/version but no plaintext secrets.
- **STM-145** — Vault references export as placeholders/required bindings, never resolved credential values.
- **STM-146** — Import validates package/schema/content type before creating Draft snippet.
- **STM-147** — Imported browser code remains Draft when risk policy requires; import does not equal publish.
- **STM-148** — Create/merge-metadata/replace/skip conflict actions are explicit and replace shows code/security diff.
- **STM-149** — WPCode/headers-footers importer accepts only recognized browser-side snippets/placements through preview.
- **STM-150** — Imported PHP snippet is rejected as executable WPE snippet and routed to unresolved/Extension Plan report.
- **STM-151** — Imported server callback/hook code is not converted into arbitrary hook-name browser placement.
- **STM-152** — Competitor consent/environment semantics that cannot map safely are marked unresolved/lossy.
- **STM-153** — Migration operation is idempotent by source identity/fingerprint and retry cannot duplicate active snippets.
- **STM-154** — Coexistence scan detects duplicate active output from legacy plugin/theme fields and requires owner choice before disabling anything.

## Group 15 — Multisite/network policy/AI/MCP — STM-155…165
- **STM-155** — Snippet is site-scoped by default and request site ID cannot cross-resolve another site’s definition.
- **STM-156** — Network library template is reusable definition, not automatically active across sites.
- **STM-157** — Network rollout Plan lists explicit target sites/revisions and dry-run conflicts before apply.
- **STM-158** — Network security floor can prohibit snippet types/origins and site admin cannot override.
- **STM-159** — Site admin cannot edit network-enforced snippet content/security settings.
- **STM-160** — Shared network snippet cannot expose shared secret/Vault data to site/frontend; secret rendering remains prohibited.
- **STM-161** — Site clone copies definitions according to profile but production environment bindings/live consent/cache state are re-evaluated/quarantined.
- **STM-162** — Site deletion removes/archives site-owned snippet definitions/output caches according to lifecycle without deleting network templates.
- **STM-163** — AI/MCP defaults to read/explain/draft/validate; publish/admin-login scope/security/emergency mutations remain excluded unless separately governed.
- **STM-164** — AI cannot generate/use PHP/eval/server-code path or grant itself high-risk publish capability.
- **STM-165** — Cache/idempotency/audit keys include site/network/environment identity to prevent cross-site collision/leakage.

## Group 16 — Adversarial security/performance/regression — STM-166…176
- **STM-166** — Security fixture submits PHP/eval/shell/SQL payload disguised as HTML/JS/filename; expected result rejects server-execution path.
- **STM-167** — Security fixture attempts JS/context breakout through dynamic token; expected output remains context-safe encoded or denied.
- **STM-168** — Security fixture attempts unsafe URL scheme/CRLF/origin confusion in external script/meta/link/iframe; expected validation blocks it.
- **STM-169** — Security fixture attempts CSP weakening (`unsafe-inline`, wildcard origin) through ordinary publish; expected result blocks/escalates separate security governance.
- **STM-170** — Security fixture attempts consent bypass via client query/cookie/role condition; canonical consent adapter remains authority.
- **STM-171** — Security fixture attempts Vault/API key interpolation into frontend JS/HTML/URL; expected result denies secret rendering.
- **STM-172** — Performance fixture defines 1k active snippets/order/condition/cache evaluation budget and prevents per-request unbounded graph/DB work.
- **STM-173** — Regression fixture covers cache/minifier/CDN/theme hook changes and reports duplicate/missing/reordered output without silently increasing privileges.
- **STM-174** — Failure of snippet subsystem degrades by omitting WPE output rather than blocking core frontend/admin/login recovery unless explicitly essential certified profile says otherwise.
- **STM-175** — AI adversarial fixture requests auto-publish high-risk tracker, disable CSP/consent or execute PHP; expected result is Draft/refusal/governed escalation only.
- **STM-176** — Golden end-to-end fixture covers Draft external/inline/HTML/JSON-LD snippet → placement/condition → consent/CSP/environment → validation → publish → cache/diagnostics → pause/rollback, with browser output and authorization truths separated.

## Completion truth

`STM-001…STM-176` are **176/176 documented and 0/176 executed**. This protocol does not certify browser execution, third-party vendor privacy behavior, consent legality, CSP runtime enforcement, cache/CDN behavior or any imported competitor snippet.