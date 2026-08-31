# WPEssential — Platform Surfaces Executable Evidence Protocol

Status: **Phase 0 fixed executable-evidence contract / execution NOT AUTHORIZED**  
Date: 2026-08-28  
Work package: `P0-M00-WP23`  
Related: ADR-0014, ADR-0034, ADR-0044, ADR-0050, ADR-0054, ADR-0060, ADR-0069, ADR-0070, ADR-0072, ADR-0076, ADR-0091, ADR-0101, ADR-0102, ADR-0128; `PLATFORM-SURFACES-SPEC.md`; Product License, OAuth Account Link, TUF, Remote Service privacy/retention and Multisite architecture.

## 1. Purpose

Freeze the future executable evidence required before WPEssential can claim its platform-facing Home, Modules, Onboarding, Account/License, Documentation, Changelog, Support and Diagnostics surfaces are coherent, truthful, secure, scope-aware and operationally safe.

This protocol covers **cross-surface product composition and UX/runtime orchestration**. It intentionally does not duplicate lower-level protocols that already own their domains.

Nothing in this file authorizes implementation, a remote service, OAuth flow, support API, diagnostics upload, package download, updater, WordPress runtime, browser test, network call or fixture execution.

## 2. Existing protocols remain authoritative

PLT depends on, but does not replace:

- **FP-01…FP-144** — Free↔Pro artifact/API/schema/entitlement compatibility;
- **OA-01…OA-32** — OAuth Account Link security/token lifecycle;
- **TU-01…TU-44** — Pro updater/TUF trust and anti-rollback;
- **RS-001…RS-030** — remote-service privacy, minimization, retention, deletion, clone and trust separation;
- **VT-01…VT-128** — Vault secret handling;
- **UI-01…UI-104** — design-system/accessibility/RTL/runtime UI compatibility;
- **BT/CI/CF** — build, quality and platform compatibility evidence;
- Multisite/Site Lifecycle evidence where target scope changes.

If a PLT fixture requires one of these truths and the dependency is not certified, the PLT result is **BLOCKED/NOT CERTIFIED**, not an excuse to reproduce a weaker ad-hoc version of that test.

## 3. Truth boundaries

These facts remain separate:

`local onboarding state ≠ remote Account connection ≠ OAuth credential validity ≠ commercial Account/Plan state ≠ Site/Network Allocation ≠ signed Product Entitlement ≠ Free/Pro binary compatibility ≠ update trust ≠ local module enabled state ≠ module runtime health ≠ Support remote authority ≠ local Support cache/draft ≠ Diagnostics generated ≠ Diagnostics transmitted ≠ Docs/Changelog cache freshness ≠ remote service status ≠ local service health ≠ certified platform behavior`

A green Home card or successful remote request must never collapse these domains into one generic `Connected`, `Active`, `Healthy` or `Verified` claim.

## 4. Result model

Each fixture result is one of:

- `PASS` — expected behavior observed under the pinned environment;
- `FAIL` — expected contract violated;
- `BLOCKED` — prerequisite protocol/capability absent or uncertified;
- `NOT_EXECUTED` — no authorized run occurred;
- `INCONCLUSIVE` — evidence insufficient or ambiguous.

Current global state: **all PLT fixtures NOT_EXECUTED**.

## 5. Certification classes

Certify independently:

- `PLT-H` — onboarding/Home/platform-shell composition;
- `PLT-MOD` — Modules lifecycle/dependency/degraded-state UX;
- `PLT-A` — Account/License/Plan/entitlement presentation composition;
- `PLT-D` — Docs/Changelog/release-content behavior;
- `PLT-S` — Support tickets/messages/attachments composition;
- `PLT-X` — Diagnostics/System Status/report/repair behavior;
- `PLT-R` — remote-service transport/cache/error/degraded-state composition;
- `PLT-MS` — Multisite/environment/allocation/clone/transfer UX isolation;
- `PLT-P` — trust/privacy/consent/update-authority separation;
- `PLT-O` — authorization/accessibility/operability/performance/observability.

One class passing does not certify another.

---

# 6. Fixed evidence matrix — PLT-01…PLT-176

## A. First-run, onboarding and platform shell — PLT-01…PLT-16

- **PLT-01** Fresh Free activation can reach normal wp-admin and WPE Home without creating or linking a WPE Account.
- **PLT-02** First-run wizard failure, missing JS asset or remote outage never traps global wp-admin or blocks normal WordPress administration.
- **PLT-03** `Continue Free` completes or skips onboarding locally without a hidden remote account/telemetry dependency; RS-001/RS-006 remain prerequisite privacy proof.
- **PLT-04** Onboarding completion is stored as local wizard state and is not displayed as proof that Account, Allocation, Entitlement or Pro installation succeeded.
- **PLT-05** Returning to an incomplete wizard resumes deterministic local step state without repeating already-completed remote mutations blindly.
- **PLT-06** Browser back/forward/reload during a remote-account step does not duplicate allocation, trial, support or package operations.
- **PLT-07** Sign-in/sign-up/recovery choices clearly identify remote-service interaction and never persist account password in local option/transient/log state.
- **PLT-08** Marketing consent is separate, optional and not preselected by account creation, trial or onboarding completion.
- **PLT-09** Trial/plan offer appears only from validated current service data and can degrade to unavailable/stale without blocking Free onboarding.
- **PLT-10** Trial duration, billing requirement, price/currency and plan feature claims are rendered only from allowlisted validated fields, never executable remote markup.
- **PLT-11** Pro acquisition CTA cannot directly execute an arbitrary package URL supplied by ordinary content/catalog JSON; TU/FP trust remains required.
- **PLT-12** Module preset expands to explicit module selections/dependencies before completion and cannot secretly enable a destructive/high-risk surface.
- **PLT-13** Onboarding under insufficient WordPress capability is read-only/denied for mutations while harmless documentation/help remains available where intended.
- **PLT-14** Account/session expiry during onboarding produces recoverable state, not silent success or loss of already valid local Free configuration.
- **PLT-15** Multisite onboarding displays the actual target Site/Network scope; Network connection is never implied by visiting one child site's wizard.
- **PLT-16** Onboarding accessibility/keyboard/focus behavior satisfies the applicable UI protocol and remains usable in degraded/no-network mode.

## B. Home and Modules composition — PLT-17…PLT-32

- **PLT-17** Home cards appear only for installed/enabled/relevant modules/services and no disabled Pro module creates broken placeholder state.
- **PLT-18** Home distinguishes setup progress, module health, service connectivity, entitlement status and update availability as separate status domains.
- **PLT-19** A module `healthy` badge requires that module's defined health source; Account connection or entitlement alone cannot mark it healthy.
- **PLT-20** A remote service outage cannot mark unrelated local Free modules unhealthy merely because Account/Catalog/Docs/Support is unreachable.
- **PLT-21** Home quick actions enforce current WordPress capability, WPE Policy and entitlement before mutation; UI visibility alone is not authorization.
- **PLT-22** Unauthorized quick-action deep links fail server-side even if a caller bypasses Home UI.
- **PLT-23** Modules list accurately distinguishes installed/available, enabled/disabled, degraded/read-only/paused/unhealthy and edition state.
- **PLT-24** Enabling a module performs declared compatibility/dependency/preflight checks before module initialization/migration mutation.
- **PLT-25** Failed enable/init/migration returns a truthful degraded/prior-safe state and never reports Enabled solely because the toggle request returned 2xx.
- **PLT-26** Disable explains dependent surfaces and runtime enforcement implications before action; security/access modules cannot silently remove protection.
- **PLT-27** Disable never equals data deletion and no remote Account/Entitlement change causes automatic local module-data deletion.
- **PLT-28** Delete-data controls remain outside ordinary enable/disable card action and require their owning destructive workflow/authorization.
- **PLT-29** Required dependency auto-enable is explicit and bounded; circular/missing dependency produces actionable failure rather than partial hidden bootstrap.
- **PLT-30** Pro unavailable/expired/incompatible state preserves Free module actions and local safe deployed output according to FP/ADR-0007.
- **PLT-31** Module state cache invalidates after package/version/dependency/entitlement/health changes without stale `Enabled/Healthy` presentation.
- **PLT-32** Large module/health datasets render within measured admin budgets without one remote call per module/card or repeated N+1 health probes.

## C. Account, License, Plan and Entitlement presentation — PLT-33…PLT-48

- **PLT-33** Account UI distinguishes disconnected, connecting, connected, auth-required, service-unreachable, restricted and cached/degraded states.
- **PLT-34** `Connected` requires a valid local Account-connection state; it does not mean current Product Entitlement is valid or every site is allocated.
- **PLT-35** Token expiry shows `auth required`/refresh behavior, never `license expired` unless signed entitlement authority independently says so.
- **PLT-36** Account summary exposes only safe display identity and never access/refresh token, verifier, completion artifact or Vault plaintext.
- **PLT-37** License/entitlement view distinguishes plan/subscription fact, allocation state, signed entitlement state, freshness and service availability.
- **PLT-38** Ordinary Account/Plan REST response cannot activate Pro UI/runtime without the separately verified signed entitlement/FP contract.
- **PLT-39** Last-verified timestamp and stale/offline state are visible and cannot be reset to fresh merely by loading cached Account JSON.
- **PLT-40** Service outage is displayed separately from expiry/revocation/allocation conflict; public safe runtime follows accepted offline policy.
- **PLT-41** Revoked, expired and revalidation-required states produce distinct safe actions and do not delete local definitions/data.
- **PLT-42** Plans/catalog malformed/unknown fields are rejected/ignored according to schema policy rather than rendered as trusted HTML or feature authority.
- **PLT-43** Checkout/manage-billing/renew links resolve only to trusted service destinations and the plugin never handles raw card data.
- **PLT-44** Upgrade/downgrade request success is not reflected as effective entitlement until authoritative commercial and signed-entitlement state converges.
- **PLT-45** Disconnect Account removes/invalidates local usable Account credentials per OA/RS while preserving Free data and distinguishing remote-revoke uncertainty.
- **PLT-46** `Disconnect`, `deactivate allocation`, `release site`, `delete account` and `delete local cache` are separate actions/labels with separate impact.
- **PLT-47** Concurrent Account/entitlement refreshes cannot produce last-writer stale state that resurrects older authority or hides a newer revoke/expiry fact.
- **PLT-48** Account/License actions are capability and target-scope checked at request time; cached UI permission never substitutes for current server authorization.

## D. Documentation, contextual help, Changelog and release content — PLT-49…PLT-64

- **PLT-49** Bundled quick-start/version-critical documentation remains usable when remote Docs service is unavailable.
- **PLT-50** Remote Docs failure is isolated to remote Docs functionality and never blocks module settings/Free local runtime.
- **PLT-51** Remote docs results are consumed as validated structured data or opened on trusted docs origin; arbitrary remote HTML/JS is never injected into wp-admin.
- **PLT-52** Docs query includes only declared query/module/locale/version context and does not append current post/form/log/private content implicitly; RS-029 remains privacy proof.
- **PLT-53** Contextual help uses stable article/resource identity and resolves version-aware target safely rather than trusting mutable arbitrary URL fields.
- **PLT-54** Docs cache key separates product/version/locale and authenticated/private context where applicable; one site's private context cannot leak to another.
- **PLT-55** Cached remote docs expose freshness/stale state when relevant and do not present stale security/compatibility instructions as newly verified.
- **PLT-56** Unsupported locale/version fallback is explicit/deterministic and cannot silently select unrelated product documentation.
- **PLT-57** Installed-release bundled changelog remains authoritative for what the installed artifact claims offline.
- **PLT-58** Remote newer-release metadata is labeled remote/latest-known and cannot rewrite historical installed-release changelog facts.
- **PLT-59** Security-fix claims require matching release evidence and cannot be created by generic remote content field alone.
- **PLT-60** Release notes, docs and changelog cannot supply executable package/update authority; TU/TUF remains the updater trust root.
- **PLT-61** Malformed release date/version/URL/category data degrades safely without XSS or version-comparison authority confusion.
- **PLT-62** Docs/changelog search and pagination handle rate limits/partial results without request storms or duplicate rows.
- **PLT-63** Public docs/changelog access does not require broad Account OAuth scopes where the service contract marks it public.
- **PLT-64** Docs/changelog links and UI satisfy keyboard/focus/external-link safety and applicable accessibility/RTL requirements.

## E. Support ticket authority, lifecycle and message composition — PLT-65…PLT-80

- **PLT-65** Remote Support service is authoritative for submitted ticket/thread state; local cache never becomes an independent conflicting ticket database.
- **PLT-66** Ticket list cache clearly distinguishes fresh, stale/offline and unsynchronized data.
- **PLT-67** Ticket creation requires local support capability plus valid remote Account scope; Account connection alone does not grant local WordPress authority.
- **PLT-68** Ticket create validates required subject/category/body and rejects unsafe remote-schema drift rather than blindly serializing arbitrary fields.
- **PLT-69** Related module/version metadata is previewed/minimized and does not silently include full plugin/theme/site inventory.
- **PLT-70** Creating ticket without selecting Diagnostics sends no diagnostics bundle; RS-007 is the privacy prerequisite.
- **PLT-71** One logical create operation uses stable idempotency/correlation identity so timeout/retry cannot create duplicate remote tickets.
- **PLT-72** Unknown create outcome enters reconciliation/pending state rather than immediately creating a fresh second ticket.
- **PLT-73** Thread rendering sanitizes remote message content/metadata and cannot inject arbitrary admin HTML/script/event handlers.
- **PLT-74** Reply uses idempotency/correlation so retry after lost response cannot create duplicate message silently.
- **PLT-75** Optimistic reply UI never marks a message as remotely committed until service authority confirms/reconciles it.
- **PLT-76** Close/reopen state transition reflects authoritative remote result and stale local state cannot overwrite a newer remote status blindly.
- **PLT-77** Search/filter/pagination cannot expose tickets outside current Account/site/support scope even if identifiers are guessed.
- **PLT-78** Local unsent draft deletion is clearly distinct from remote ticket/message deletion or retention request.
- **PLT-79** Service-retained/undeletable/deletion-requested records use truthful state labels rather than a generic `Deleted` success.
- **PLT-80** Support outage preserves safe local draft only if explicitly designed, with no false `sent` state and bounded retry/backoff.

## F. Support attachments, downloads and concurrency — PLT-81…PLT-96

- **PLT-81** Attachment client preflight enforces declared local/server count/size/type limits before upload where feasible.
- **PLT-82** Server remains authoritative for MIME/type/size policy; client validation cannot bypass remote attachment policy.
- **PLT-83** Executable/script/polyglot or unsafe attachment classes fail according to certified service policy; no local filename extension optimism.
- **PLT-84** Upload session/pre-signed URL is treated as secret, short-lived and scoped to exact Account/ticket/file operation.
- **PLT-85** Reusing/stealing upload session for another Account/site/ticket/file fails; RS-010 remains access/privacy proof.
- **PLT-86** Lost upload response reconciles by operation/file identity rather than producing duplicate attachment records on retry.
- **PLT-87** Partial multi-file failure reports per-file committed/failed state and never claims all attached if only a subset committed.
- **PLT-88** Private attachment download requires current local capability plus authorized remote Account/ticket access; permanent public URL is not exposed.
- **PLT-89** Cross-ticket/cross-account guessed attachment identifiers cannot retrieve metadata/content.
- **PLT-90** Expired download URL/session produces recoverable reauthorization rather than exposing a longer-lived hidden URL.
- **PLT-91** Attachment filename/content-disposition are normalized against header/path injection and hostile Unicode/control characters.
- **PLT-92** Service malware/scanning/quarantine state, if offered, is presented as its actual source fact and not fabricated locally.
- **PLT-93** Attachment delete/deletion-request result reflects remote authority and local cache cleanup cannot imply remote erasure.
- **PLT-94** Concurrent replies/uploads/close operations produce deterministic thread state or visible conflict, not silent message loss.
- **PLT-95** Support attachment metadata/logging excludes signed URLs/tokens and unnecessary private content.
- **PLT-96** Large ticket history/attachment sets paginate/stream within measured resource budgets without loading entire private history into one admin response.

## G. System Status, Diagnostics, report and repair actions — PLT-97…PLT-112

- **PLT-97** System Status distinguishes environment facts, WPE package/schema facts, module health, queue/runner health, service connectivity and integration health.
- **PLT-98** Unknown/unavailable probes render `Unknown/Unavailable`, never an optimistic green success.
- **PLT-99** Diagnostics collection excludes secrets/protected classes by default and exposes a redaction/include summary before export/upload; RS-008 is privacy proof.
- **PLT-100** Generated diagnostics report is local until the user explicitly chooses a remote send/attachment path; generation ≠ transmission.
- **PLT-101** Text/JSON report contains schema/version/timestamp so Support cannot mistake old diagnostics for live state.
- **PLT-102** Absolute paths, host details and identifiers are minimized/redacted according to declared diagnostic profile without making troubleshooting claims misleading.
- **PLT-103** Unauthorized users cannot view/download privileged diagnostics merely because they know an admin route/nonce-like URL.
- **PLT-104** Diagnostics export/download resists formula/HTML/script injection when consumed by common viewers and never includes Vault plaintext.
- **PLT-105** Remote service status shown in Diagnostics identifies its source/freshness and is not substituted for direct local connectivity probe when semantics differ.
- **PLT-106** Repair actions are a registered allowlist; arbitrary callback/PHP/shell/SQL/URL action cannot be supplied by remote content or request input.
- **PLT-107** Each repair action performs current capability/Policy/target-scope checks and shows impact before mutation.
- **PLT-108** `refresh rewrite`, `rebuild cache`, `retry jobs`, `rerun safe migration`, `refresh entitlement` remain distinct typed actions and invoke their owning subsystem contract.
- **PLT-109** Repair failure/partial success is visible and does not mark overall platform healthy merely because the request returned.
- **PLT-110** Dangerous/destructive repair is not smuggled into Diagnostics; it routes to the owning reviewed destructive workflow/recovery gate.
- **PLT-111** Diagnostics probe set has bounded timeouts/cost and does not make a page load wait indefinitely on every remote integration.
- **PLT-112** Repeated Diagnostics refresh cannot produce uncontrolled remote-call storms, job duplication or expensive full-table scans.

## H. Remote service transport, cache, schema and degraded-state composition — PLT-113…PLT-128

- **PLT-113** Every WPE remote resource uses its approved HTTPS trusted-origin profile; user/content input cannot redirect core Account/Support/Docs calls to arbitrary hosts.
- **PLT-114** Per-resource timeout/cancellation prevents one remote service from blocking unrelated wp-admin/local module operation.
- **PLT-115** API version/schema mismatch enters explicit unsupported/degraded state; unknown fields do not become new executable capabilities.
- **PLT-116** RFC 9457/problem-style errors are mapped by stable machine type/code; human detail is escaped and never parsed as authority.
- **PLT-117** 401/403/404/409/412/429/5xx/network-timeout states remain semantically distinct where required for safe recovery.
- **PLT-118** 429/Retry-After respects bounded retry/backoff and no page-load thundering herd is created across multiple platform cards.
- **PLT-119** Idempotent mutation retries reuse the same operation identity; non-idempotent unknown outcomes reconcile before fresh mutation.
- **PLT-120** Cache key includes the exact Account/install/network/site/environment/resource dimensions required to prevent cross-scope reuse.
- **PLT-121** Public Docs/Status cache never stores or serves authenticated Account/Support content under the same namespace.
- **PLT-122** Security authority such as signed entitlement/token expiry/TUF metadata cannot be extended merely by ordinary HTTP cache TTL.
- **PLT-123** Stale cache is visibly stale where freshness affects decisions and cannot overwrite newer authoritative mutation response.
- **PLT-124** Offline mode identifies which features are locally usable, cached-read-only or remote-unavailable without global `offline=everything disabled` behavior.
- **PLT-125** Service partial outage isolates Account/Catalog/Docs/Support/Status domains instead of reducing them to one generic service state.
- **PLT-126** Correlation/request IDs are safe to expose for support but never contain tokens, raw email/content, DB credentials or signed URLs.
- **PLT-127** Remote response/log/diagnostic handling does not persist full sensitive bodies by default and complies with RS retention classes.
- **PLT-128** Contract/API deprecation/version transition can run old/new compatible client profiles during the declared window without silent schema coercion.

## I. Multisite, environment, clone, restore and allocation composition — PLT-129…PLT-144

- **PLT-129** Network Account connection, Network Activation and child Site Allocation are displayed as separate states.
- **PLT-130** Connecting a Network Account does not auto-allocate every child site unless an explicit accepted commercial policy says so.
- **PLT-131** Site Admin cannot view Network-level Account credentials/private support/commercial details solely because the site belongs to the network.
- **PLT-132** Site Admin cannot consume/release paid allocation unless explicit capability/plan policy authorizes that action.
- **PLT-133** Child-site platform screens resolve exact target Site/Network context and cannot mutate another site by changing a request `site_id` alone.
- **PLT-134** Site allocation UI uses stable WPE allocation identity; WordPress numeric blog ID reuse cannot inherit prior site's paid state.
- **PLT-135** Environment class production/staging/development/migration/DR is explicit and not inferred as commercial authority from hostname alone.
- **PLT-136** Production clone with copied DB does not silently display itself as a second valid connected/allocated production site; FP/OA/RS clone evidence remains prerequisite.
- **PLT-137** Approved staging clone cannot use production Account refresh credential/support private state by copied local database alone.
- **PLT-138** Domain rename updates metadata/revalidation state without fabricating a new allocation or destroying existing local data.
- **PLT-139** Host migration overlap/transfer presents pending/unknown/complete states and cannot consume duplicate seats through blind retry.
- **PLT-140** Site transfer between Multisite networks does not carry old Network Vault/shared credentials/account authority implicitly.
- **PLT-141** Site deletion distinguishes local site missing, deallocation pending/released and retained remote commercial history.
- **PLT-142** Backup restore with stale Account/entitlement/cache state enters reconciliation; restored cache alone cannot resurrect obsolete signed authority.
- **PLT-143** Disaster-recovery environment preserves safe public runtime according to entitlement policy while remote revalidation state is explicit.
- **PLT-144** Multisite platform aggregation is bounded/batched and does not disclose one site's private status/support/account fields to another site's administrators.

## J. Trust separation, privacy, update authority and failure containment — PLT-145…PLT-160

- **PLT-145** Free activation/local CPT/Taxonomy use does not require WPE-controlled remote calls; RS-001 provides network-capture certification.
- **PLT-146** Account link does not imply analytics/telemetry consent; RS-006 remains authoritative.
- **PLT-147** Diagnostics upload remains a separate explicit approval even when creating a Support ticket from a Diagnostics screen.
- **PLT-148** Ordinary Account/Catalog/Docs/Support/Status REST content can never grant signed Product Entitlement.
- **PLT-149** Ordinary Release/Catalog REST content can never authorize/install a Pro update artifact; TU/TUF verification remains mandatory.
- **PLT-150** A malicious/compromised Docs/Support response cannot supply PHP/JS/callback/command/SQL/repair implementation for local execution.
- **PLT-151** Account service outage cannot remove Membership/content protection or expose protected end-user content.
- **PLT-152** Pro expiry/revocation/outage never deletes local WPE data and never disables Free CPT/Taxonomy according to FP/ADR-0007.
- **PLT-153** Support/Docs/status public requests do not include Account/site/install identifiers unless resource policy requires and discloses them; RS-002 certifies transmission.
- **PLT-154** Account/support errors never reveal cross-account/site resource existence beyond authorized error semantics.
- **PLT-155** Clipboard/download/export surfaces redact OAuth/Vault/private attachment secrets and do not produce a reusable authentication artifact.
- **PLT-156** Cached stale Account/Support/Docs/Status data cannot be represented as `verified now` after local clock changes or cache restore.
- **PLT-157** Account disconnect does not equal Account deletion, subscription cancellation, entitlement destruction, site-data deletion or Support-history erasure.
- **PLT-158** Privacy/export/erasure UI accurately separates local WordPress data from remote Account/Support/commercial/security records and their retention authority.
- **PLT-159** Immutable/provider-retained backups are documented as retention boundary rather than falsely promising instant physical erasure.
- **PLT-160** Any critical trust-boundary violation above is a stop-the-line failure for platform production certification even if happy-path screens work.

## K. Authorization, accessibility, operability, observability and scale — PLT-161…PLT-176

- **PLT-161** Every mutating platform action has a registered capability/Policy/Ability boundary and server-side authorization independent of menu visibility.
- **PLT-162** Network-only actions require Network/Super Admin authority where specified; site capability cannot escalate through shared Account connection.
- **PLT-163** Account/Support/Diagnostics read permissions are separated enough that a user allowed to read docs cannot read private commercial/support/diagnostic data.
- **PLT-164** High-risk actions such as allocation release, account disconnect, repair/migration trigger or package operation use the declared confirmation/re-auth class.
- **PLT-165** Abilities expose typed inputs/outputs and never expose arbitrary remote URL, PHP, shell, raw SQL or unrestricted filesystem execution.
- **PLT-166** AI-facing platform Abilities default to safe read/explain operations; account/package/support-send/repair mutations require explicit opt-in and normal authorization.
- **PLT-167** Platform screens satisfy keyboard navigation, focus return, live-region/error announcement and no-mouse operation under applicable UI evidence.
- **PLT-168** RTL/localization does not corrupt ticket/thread identifiers, prices/currencies, timestamps, account states or destructive confirmation semantics.
- **PLT-169** Screen-reader labels distinguish similarly named states such as Account disconnected, Site unallocated, Entitlement expired and Service unavailable.
- **PLT-170** Large Multisite/support/module datasets use bounded pagination/batching and measured request/query/memory budgets.
- **PLT-171** Home/Modules/Account page load does not require synchronous calls to every remote service/provider on every request.
- **PLT-172** Background refresh, if implemented, uses JobService/idempotency/backpressure and cannot cause uncontrolled duplicate Account/Support/status operations.
- **PLT-173** Observability records resource/action/scope/correlation/result without passwords, OAuth tokens, Vault plaintext, signed upload URLs or full private ticket/diagnostic payloads.
- **PLT-174** Fatal/exception/service-schema failure degrades only the affected platform surface where safe and preserves normal wp-admin recovery path.
- **PLT-175** Recovery/support diagnostics can explain which dependency blocked a PLT class (OA/FP/TU/RS/VT/UI/BT/CI/etc.) without converting dependency `0 executed` into a false platform pass.
- **PLT-176** Final certification report pins client/service/schema/environment/scope versions, lists unsupported/degraded behavior and preserves all zero/unverified dependencies truthfully.

---

## 7. Capability/certification matrix

A surface may claim only the classes actually proven. Examples:

- local Free Home can potentially reach `PLT-H` before remote Account is certified;
- Support production readiness needs `PLT-S` plus the applicable OA/RS/Vault/service evidence;
- Account/License production readiness needs `PLT-A` plus OA/FP/RS and signed entitlement evidence;
- update UI may pass presentation fixtures while automated update authority remains uncertified until TU/TUF passes;
- Multisite platform readiness requires `PLT-MS` plus applicable Multisite/Site Lifecycle evidence.

No umbrella `Platform certified` label is allowed from one partial class.

## 8. Negative requirements — MUST NOT

Platform surfaces MUST NOT:

- require a remote Account for ordinary Free CPT/Taxonomy use;
- hide remote transmission behind ordinary local activation;
- persist account passwords or reusable OAuth credentials in plain options/logs/browser state;
- treat Account connection as Product Entitlement or Site Allocation;
- treat Product Entitlement as Membership/user authorization;
- treat service outage as expiry/revocation;
- treat module disable as data deletion;
- render arbitrary remote HTML/JS/PHP as trusted admin code;
- execute arbitrary remote package URL/content as update authority;
- expose private Support/Diagnostics/Account data cross-site/cross-account;
- auto-upload Diagnostics with ticket creation;
- duplicate support mutations after unknown outcomes;
- show stale cache as newly verified authority;
- allow remote responses to register arbitrary repair commands;
- let child-site admins infer/read Network Account secrets;
- use numeric blog ID/hostname alone as commercial identity;
- claim remote ticket deletion from local cache deletion;
- claim local generated diagnostics were transmitted when they were not;
- collapse Docs/Status/Account/Support partial outage into one misleading global state;
- infer update trust from release notes/catalog JSON;
- promote an unexecuted dependency protocol into a PLT pass.

## 9. Stop-the-line conditions

Future execution stops and marks the relevant certification FAILED if evidence shows:

- secret/token/password leakage to browser/log/diagnostics/export;
- remote content leads to arbitrary local code/package/repair execution;
- unsigned ordinary REST state grants Pro or bypasses TUF;
- cross-account/cross-site Support or Account data disclosure;
- unauthorized Site/Network allocation/release/account mutation;
- Diagnostics transmitted without explicit approval;
- remote outage disables Free or weakens Membership/security enforcement;
- retry creates uncontrolled duplicate ticket/allocation/package mutation;
- stale/restored cache resurrects revoked/expired security/commercial authority;
- child site gains Network secret/authority by copied scope identifier;
- destructive module/data action occurs from a non-destructive platform control.

## 10. Required future execution prerequisites

Before any PLT fixture executes:

- explicit scoped owner consent under ADR-0014;
- exact client/service test builds pinned;
- sandbox/test Account/Support/catalog resources only;
- no production customer/payment/private data;
- required lower-level protocol prerequisites identified per fixture;
- safe network/log capture with secret redaction;
- disposable WordPress single-site + Multisite environments where needed;
- recoverable state for mutation/clone/restore cases;
- exact API/schema/environment profiles recorded.

Unavailable prerequisites produce `BLOCKED`/`NOT_EXECUTED`, never simulated success.

## 11. Evidence artifact contract

For each future fixture record:

- fixture ID;
- WPE Free/Pro/client build IDs;
- remote service/API/schema build/profile;
- WordPress/PHP/DB/Multisite environment;
- Account/install/network/site/environment scope using safe opaque refs;
- prerequisite protocol/certification state;
- expected behavior;
- observed behavior;
- request/response facts with secrets redacted;
- local state before/after;
- remote authoritative state before/after where applicable;
- cache/freshness state;
- screenshots/network traces/log refs only when privacy-safe;
- `PASS/FAIL/BLOCKED/NOT_EXECUTED/INCONCLUSIVE`;
- remediation/retest notes.

## 12. Current evidence state

- PLT fixtures documented: **176**.
- PLT fixtures executed: **0/176**.
- `PLT-H`: 0 certified.
- `PLT-MOD`: 0 certified.
- `PLT-A`: 0 certified.
- `PLT-D`: 0 certified.
- `PLT-S`: 0 certified.
- `PLT-X`: 0 certified.
- `PLT-R`: 0 certified.
- `PLT-MS`: 0 certified.
- `PLT-P`: 0 certified.
- `PLT-O`: 0 certified.

Dependent current truth remains unchanged:
- FP **0/144**;
- OA **0/32**;
- TU **0/44**;
- Remote privacy RS **0/30**;
- Vault VT **0/128**;
- UI **0/104**;
- Multisite **0 MS1+**;
- Site Lifecycle **0/40**.

No platform runtime/service certification exists.

## 13. Development-consent gate

This protocol authorizes no source code, service endpoint, OAuth request, entitlement refresh, catalog/docs/support/status call, package/update operation, support mutation, attachment transfer, diagnostics generation/upload, repair action, clone/restore, browser test or benchmark.

Execution remains blocked until the owner explicitly grants scoped development/executable-spike consent and the Approval Ledger records it.
