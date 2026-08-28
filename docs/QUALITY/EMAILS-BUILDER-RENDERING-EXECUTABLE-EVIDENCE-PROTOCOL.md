# WPEssential — Emails Builder Rendering & Composition Executable Evidence Protocol

Status: **Accepted planning protocol / execution pending**  
Date: 2026-08-28  
Work package: `P0-M00-WP22`  
Execution mode: `PLANNER_ONLY`  
Development authorization: **NOT GRANTED**

Related: `docs/ARCHITECTURE/EMAIL-RENDERING-DELIVERY-ARCHITECTURE.md`, `docs/MODULES/EMAILS-BUILDER-EXHAUSTIVE-SPEC.md`, `docs/MODULES/AUTOMATION-COMMUNICATION-SPECS.md`, `docs/MODULES/MULTISITE-SCOPE-OPTION-MATRIX.md`, `docs/QUALITY/EMAIL-TRANSPORT-CERTIFICATION-EVIDENCE-PROTOCOL.md`, ADR-0029, ADR-0058, ADR-0063, ADR-0067, ADR-0079, ADR-0120, ADR-0014.

## 1. Purpose

Freeze the future executable evidence required before WPEssential Emails Builder may claim production-safe template compilation, permission-aware dynamic rendering, HTML/plaintext composition, sender/recipient envelope construction, link/asset/attachment safety, deterministic preview/test/production semantics, Multisite scope, and handoff to Notification/transport.

This protocol **does not duplicate or replace ET0–ET5 transport/provider certification**. SMTP/provider connections, actual sends, delivery events, DNS authentication, bounce/complaint handling and provider reconciliation remain governed by `EMAIL-TRANSPORT-CERTIFICATION-EVIDENCE-PROTOCOL.md` and are not certified by EBR.

This protocol does **not** authorize rendering code, email sends, provider calls, REST endpoints, asset downloads, attachment reads, jobs, WordPress execution, browser/client tests or benchmarks.

## 2. Canonical truth boundary

The following remain distinct:

`Email Definition ≠ Published Template Revision ≠ Layout Revision ≠ Compiled Email Descriptor ≠ authorized render context ≠ Email IR ≠ HTML output ≠ plaintext output ≠ recipient/sender envelope ≠ immutable Rendered Message snapshot ≠ Transport Attempt ≠ provider/delivery truth ≠ certified runtime behavior`

A valid HTML preview does not prove:
- recipient authorization;
- deterministic production rendering;
- plaintext quality;
- safe headers/links/assets/attachments;
- transport acceptance;
- delivery;
- inbox placement;
- human read/open.

## 3. Rendering baseline under test

First operational baseline:

`Published Template Revision + resolved Layout Revision + typed authorized context → compiled Email Descriptor → permission-aware token/query resolution → Email IR → email-safe HTML + plaintext + normalized headers/envelope → immutable Rendered Message snapshot → Notification/transport handoff`

Core invariants:

1. Draft templates/layouts never render as production send inputs.
2. rendering is deterministic for the chosen revisions + approved context + renderer profile;
3. tokens are typed, privacy-classified, context-authorized and escaped by destination;
4. secrets/credential material are not generic renderable content;
5. browser/page-builder HTML is not canonical email markup;
6. HTML and plaintext are distinct outputs from one authorized composition model;
7. To/CC/BCC/From/Reply-To composition is validated and header-injection safe;
8. private assets/attachments are resolved only through authorized delivery semantics;
9. preview/test/production are separate execution modes and test never mutates business workflow state;
10. transport/provider delivery truth starts only after a Rendered Message is handed to a transport attempt;
11. site/network template sharing does not share sender credentials or recipient datasets by implication;
12. already-created deterministic Rendered Message snapshots are not silently re-rendered on retry unless an explicitly versioned retry policy says so.

## 4. Certification classes

Certify independently:

- `EBR-D` — Definition/revision/dependency correctness;
- `EBR-C` — compiled descriptor/context/token authorization;
- `EBR-H` — email-safe HTML/Email IR rendering;
- `EBR-T` — plaintext/subject/preheader/localization;
- `EBR-E` — envelope/header/sender/recipient composition;
- `EBR-A` — links/assets/images/attachments;
- `EBR-P` — privacy/personalization/fan-out/cache safety;
- `EBR-I` — preview/test/Notification/Workflow/transport handoff integration;
- `EBR-M` — Multisite/import/export/clone/restore scope behavior;
- `EBR-O` — compatibility/failure/performance/observability.

Current certifications: **0**.

## 5. Fixed fixture matrix — EBR-01…EBR-176

### A. Definition, revision and dependency truth — EBR-01…EBR-16

- **EBR-01** new template is Draft and unavailable to production send path.
- **EBR-02** publish requires valid subject for each send-ready locale policy.
- **EBR-03** published Template revision is immutable.
- **EBR-04** published Layout revision is immutable.
- **EBR-05** follow-current Layout policy resolves one explicit published layout revision at render/snapshot time.
- **EBR-06** pinned Layout revision remains deterministic after newer Layout publish.
- **EBR-07** missing Layout dependency produces typed degraded/error state, not arbitrary fallback HTML.
- **EBR-08** missing block/provider dependency produces typed diagnostic.
- **EBR-09** missing token provider is detected before production handoff where required.
- **EBR-10** unsupported imported block version is rejected/deferred safely.
- **EBR-11** reusable partial/layout include cycle is detected.
- **EBR-12** duplicate/clone creates new Definition identity while preserving explicit dependency mapping.
- **EBR-13** archive/unpublish prevents new production use while preserving historical render references.
- **EBR-14** delete with active Notification/override dependency is blocked or impact-reviewed.
- **EBR-15** editing Draft does not mutate already-published revision.
- **EBR-16** queued/rendered historical message retains exact Template/Layout/renderer profile references.

### B. Typed render context, tokens, conditions and policy — EBR-17…EBR-32

- **EBR-17** Site token resolution in target-site scope.
- **EBR-18** Recipient safe-profile token resolution.
- **EBR-19** Event token resolution from declared schema only.
- **EBR-20** Entity Field token uses owning Field/Data Source Policy.
- **EBR-21** Form Entry safe field token excludes protected/unapproved fields.
- **EBR-22** Membership safe-state/entitlement token uses recipient/target Policy.
- **EBR-23** bounded Query token executes under declared delivery principal/scope.
- **EBR-24** custom registered token provider publishes type/privacy/escaping contract.
- **EBR-25** unknown token fails/warns according to required/optional contract.
- **EBR-26** nullable token uses declared fallback semantics.
- **EBR-27** required token missing at production render blocks handoff with actionable error.
- **EBR-28** password hashes/application passwords/OAuth/API tokens/Vault secrets/raw protected meta are unavailable to generic token browser.
- **EBR-29** arbitrary object dump/token path traversal is rejected.
- **EBR-30** conditional block evaluates server-side from authorized typed context only.
- **EBR-31** unauthorized condition input cannot be used as protected existence oracle beyond accepted policy.
- **EBR-32** raw PHP/template language/arbitrary callback expression is rejected.

### C. Email IR, HTML blocks, escaping and sanitizer — EBR-33…EBR-48

- **EBR-33** section/container compiles to email-safe structural IR.
- **EBR-34** one-to-four-column layout compiles without assuming unsupported browser Grid/Flex semantics.
- **EBR-35** rich text uses allowlisted semantic tags.
- **EBR-36** script/event-handler/form/iframe/video-autoplay/canvas payload is removed/rejected.
- **EBR-37** plain text token is HTML-escaped in text destination.
- **EBR-38** trusted rich token/provider uses explicit sanitizer/render contract.
- **EBR-39** heading semantic level remains distinct from visual style.
- **EBR-40** button label and URL are separately encoded/validated.
- **EBR-41** divider/spacer emit bounded email-safe structure without empty-content hacks.
- **EBR-42** list block emits readable semantic/fallback structure.
- **EBR-43** key/value table bounded-row rendering.
- **EBR-44** tabular data table bounded rows/columns and safe mobile fallback.
- **EBR-45** repeater bounded item count and explicit truncation behavior.
- **EBR-46** registered custom email block cannot inject arbitrary script/unsafe headers.
- **EBR-47** sanitizer strictness/profile change is renderer-version/revision-impacting and not silently retroactive to historical snapshot.
- **EBR-48** malformed one-block content degrades/fails according to typed renderer policy without leaking stack/secret data.

### D. CSS/style compatibility, responsive and accessibility — EBR-49…EBR-64

- **EBR-49** global width/background/type tokens compile to bounded email-safe CSS/markup.
- **EBR-50** inline/embedded CSS strategy records renderer profile/version.
- **EBR-51** unsupported CSS property is warned/removed rather than marketed as universally supported.
- **EBR-52** responsive column stacking output.
- **EBR-53** mobile padding/text/button/image overrides remain within supported renderer subset.
- **EBR-54** no remote font dependency is required for semantic readability.
- **EBR-55** dark-mode hints are optional compatibility hints, not exact-client guarantee.
- **EBR-56** meaningful image alt text retained.
- **EBR-57** decorative image semantics suppress misleading alt content.
- **EBR-58** meaningful link/button purpose remains understandable.
- **EBR-59** essential information is not image/color-only where validator can detect it.
- **EBR-60** heading hierarchy warning behavior.
- **EBR-61** data vs layout table accessibility semantics follow renderer contract.
- **EBR-62** RTL locale renders ordering/alignment without unsafe content reversal.
- **EBR-63** client preview widths are labeled approximation, not Gmail/Outlook proof.
- **EBR-64** email-client-specific visual differences do not alter security/content authorization semantics.

### E. Plaintext, subject, preheader, locale and formatting — EBR-65…EBR-80

- **EBR-65** auto plaintext removes raw HTML remnants.
- **EBR-66** headings become readable plaintext.
- **EBR-67** buttons become label + validated URL.
- **EBR-68** images become meaningful alt/link text where relevant.
- **EBR-69** lists/tables/repeaters become bounded readable lines.
- **EBR-70** decorative-only content omitted from plaintext.
- **EBR-71** custom plaintext override is escaped/normalized according to text channel semantics.
- **EBR-72** subject token resolution uses header-safe encoding.
- **EBR-73** CR/LF/header injection in subject is rejected/normalized safely.
- **EBR-74** preheader sanitization and optional omission.
- **EBR-75** Unicode subject/display-name/plaintext behavior.
- **EBR-76** date/time formatter uses explicit site/recipient/event timezone semantics.
- **EBR-77** number/currency formatter requires explicit type/currency/locale truth.
- **EBR-78** locale fallback chain is deterministic and visible in diagnostics.
- **EBR-79** missing requested locale cannot silently substitute wrong compliance-sensitive content when policy forbids fallback.
- **EBR-80** HTML/plaintext pair references same authorized context/revision snapshot.

### F. Sender, recipient, reply-to and header composition — EBR-81…EBR-96

- **EBR-81** allowed sender profile inherited from Notification/send policy.
- **EBR-82** explicitly selected sender profile must be authorized for site/template/purpose.
- **EBR-83** From display name header-injection payload rejected.
- **EBR-84** From address validation.
- **EBR-85** Reply-To address/profile validation.
- **EBR-86** sender credential material never enters renderer/output/log.
- **EBR-87** single To recipient normalization.
- **EBR-88** recipient display-name/header injection rejection.
- **EBR-89** CC list requires explicit send-policy capability and bounded validation.
- **EBR-90** BCC list requires explicit send-policy capability and bounded validation.
- **EBR-91** template cannot silently own hidden CC/BCC recipients by default.
- **EBR-92** token-derived recipient address must use declared recipient source/policy, not generic text token.
- **EBR-93** invalid recipient prevents handoff for that recipient with truthful partial-fanout status upstream.
- **EBR-94** recipient privacy prevents To/CC leakage between independently personalized recipients.
- **EBR-95** custom arbitrary email headers are unavailable unless a separately registered safe header adapter exists.
- **EBR-96** SPF/DKIM/DMARC/deliverability state is not inferred from From composition; transport/provider diagnostics remain ET scope.

### G. Links, unsubscribe/preferences, images and remote assets — EBR-97…EBR-112

- **EBR-97** absolute/local HTTP(S) link validation.
- **EBR-98** unsafe javascript/data/file URL schemes rejected.
- **EBR-99** trusted dynamic URL provider emits destination-safe encoded URL.
- **EBR-100** signed account/security action link comes only from owning registered provider.
- **EBR-101** generic token editor cannot expose session/API/reset secrets directly.
- **EBR-102** optional-category unsubscribe link generated from single-purpose approved token provider.
- **EBR-103** manage-preferences link preserves recipient/site/category scope.
- **EBR-104** required security/transactional classification does not incorrectly apply optional unsubscribe behavior.
- **EBR-105** media attachment image resolves authorized public/email-safe rendition.
- **EBR-106** trusted remote image URL validation.
- **EBR-107** protected/private media URL is denied unless an explicit email-safe exposure design certifies it.
- **EBR-108** giant base64 embedded image blocked by default.
- **EBR-109** image dimensions/rendition obey safe bounds.
- **EBR-110** linked-image URL is independently validated.
- **EBR-111** externally hosted image/tracking privacy implications remain explicit.
- **EBR-112** renderer does not make arbitrary remote fetches merely to compose normal email unless an approved asset adapter contract requires it.

### H. Attachments and private-file exposure — EBR-113…EBR-128

- **EBR-113** static approved attachment reference resolution.
- **EBR-114** generated document reference resolution through owning service.
- **EBR-115** event/form upload attachment requires explicit Policy.
- **EBR-116** missing/deleted attachment produces typed preflight/runtime result.
- **EBR-117** max attachment count enforced.
- **EBR-118** max total attachment bytes enforced before transport handoff where known.
- **EBR-119** MIME/extension policy enforced using owning asset metadata/validation.
- **EBR-120** executable/script content is not attached through generic slot.
- **EBR-121** private/protected asset exposure requires recipient-specific authorization at resolution time.
- **EBR-122** authorization revoked before render blocks private attachment.
- **EBR-123** expired generated artifact does not silently attach stale/reused bytes.
- **EBR-124** secure expiring download link path preferred/validated when policy selects link instead of sensitive large attachment.
- **EBR-125** attachment filename/header encoding is injection-safe.
- **EBR-126** renderer/diagnostics do not log attachment secret URLs/raw private bytes.
- **EBR-127** retry policy cannot attach a newly different private file under an old deterministic message snapshot without explicit re-render policy.
- **EBR-128** attachment resolution failure remains separate from provider submission failure.

### I. Preview, test send, production snapshot and handoff — EBR-129…EBR-144

- **EBR-129** preview uses explicit authorized sample context.
- **EBR-130** preview cannot select/read arbitrary protected user/entity data without capability/Policy.
- **EBR-131** preview token/context inspector redacts sensitive values.
- **EBR-132** HTML developer-source preview requires dedicated capability.
- **EBR-133** plaintext preview uses same revision/context as HTML preview.
- **EBR-134** preflight reports missing required tokens, invalid URLs, unsupported blocks, plaintext/alt warnings and estimated size without claiming transport health it cannot prove.
- **EBR-135** test recipient entered by authorized actor is validated independently from production recipient rule.
- **EBR-136** test send is marked as test in downstream request/log.
- **EBR-137** test send does not mutate real Form/Order/Membership/Workflow business state.
- **EBR-138** test/sample context cannot invoke destructive token/action side effects.
- **EBR-139** production render creates immutable Rendered Message snapshot containing exact template/layout revisions and normalized outputs.
- **EBR-140** downstream transport receives normalized Rendered Message; it does not re-open Draft template state.
- **EBR-141** transport rejection/failure does not mutate historical rendered content.
- **EBR-142** safe retry reuses frozen snapshot when policy says deterministic retry.
- **EBR-143** explicit re-render-on-retry policy creates a new render generation and records why; never silently substitutes content.
- **EBR-144** provider acceptance/delivery states are not generated by renderer; ET protocol remains authoritative.

### J. Personalization, fan-out, caching and Notification/Workflow integration — EBR-145…EBR-160

- **EBR-145** one event → multiple recipients produces recipient-specific authorized contexts.
- **EBR-146** one recipient's personalized values cannot leak into another recipient's Rendered Message.
- **EBR-147** batch render coalesces public/shared template compilation without sharing protected resolved values.
- **EBR-148** compiled revision cache is separate from personalized rendered-output cache.
- **EBR-149** personalized/protected output is never stored under public shared cache key.
- **EBR-150** access/policy generation participates where protected render caching is allowed at all.
- **EBR-151** Notification recipient snapshot-vs-resolve-at-send policy is honored explicitly.
- **EBR-152** recipient revoked before send-time resolution is excluded/blocked according to Notification policy.
- **EBR-153** Workflow passes typed event/context identifiers, not arbitrary trusted render object dumps.
- **EBR-154** Email render failure returns typed result to Workflow/Notification without false delivery state.
- **EBR-155** large recipient fan-out routes through Notification/Job orchestration after future consent, not one request loop.
- **EBR-156** bounded Query/repeater work avoids per-recipient/per-item N+1 where batching contract exists.
- **EBR-157** tracking flag is off by default candidate and cannot enable unsupported provider tracking by renderer alone.
- **EBR-158** provider tracking capability/consent remains transport/policy evidence, not visual template setting truth.
- **EBR-159** full sensitive rendered body is not retained indefinitely by default; retention/redaction boundary is explicit.
- **EBR-160** correlation links Template/Rendered Message/Recipient Delivery/Transport Attempt without conflating state ownership.

### K. Multisite, import/export, compatibility, failures and scale — EBR-161…EBR-176

- **EBR-161** site-scoped Template renders only in target site context.
- **EBR-162** shared/network Template/Layout use still resolves target-site branding/recipient data according to policy.
- **EBR-163** network/shared sender connection delegation grants use-right only; credentials are never revealed to site renderer/admin.
- **EBR-164** From/domain/profile validity is evaluated for exact site/provider/account profile, not assumed from network template.
- **EBR-165** correlation includes site scope so provider events map to exact site Recipient Delivery downstream.
- **EBR-166** global provider suppression facts remain distinct from site preference state.
- **EBR-167** export includes template/layout/dependency/locale schema but excludes sender credentials/secrets.
- **EBR-168** import performs semantic dependency/conflict preview and stays Draft/deferred when required provider/layout/block dependency is missing.
- **EBR-169** clone/restore reconciles Template/Layout/brand/token-provider UUIDs without accidental cross-site remap.
- **EBR-170** WordPress core email override adapter supported event uses semantic hook/adapter path.
- **EBR-171** unsupported/partial core email override is labeled Partial/Unsupported rather than global `wp_mail()` interception.
- **EBR-172** third-party transactional email override is certified by plugin/version/event schema and falls back to original behavior when adapter is absent/unsupported.
- **EBR-173** Pro expiry/dependency degradation preserves already-deployed safe transactional runtime only according to accepted entitlement policy; editing stays locked as specified.
- **EBR-174** 100 templates × locale/layout revisions compile-cache workload records compile/render time/memory/output size.
- **EBR-175** bounded high-fan-out personalized render workload records p50/p95, token/query batch counts, rendered size and memory without sending email.
- **EBR-176** pathological template (excessive nesting/repeater/size/unsupported block) triggers warning/block/degraded result rather than unbounded render or false send-readiness.

## 6. Required future measurements

For applicable fixtures record:

- WordPress/PHP/DB versions;
- WPE renderer/compiler profile and dependency versions;
- Template/Layout UUIDs + published revisions;
- locale/site/network scope;
- render principal/context schema identity;
- token/provider/query counts and authorization decisions;
- compile cache hit/miss and personalized cache classification;
- HTML/plaintext bytes;
- component/table/repeater/attachment counts;
- p50/p95 compile/render duration;
- peak memory;
- header/envelope normalization result;
- private asset authorization outcome;
- downstream Notification/Transport correlation IDs where applicable;
- exact test-vs-production mode;
- artifacts/diagnostics references.

Exact CSS inliner/renderer dependency, email size warnings, attachment limits, client compatibility matrix and performance budgets remain evidence-gated.

## 7. MUST NOT / negative requirements

Emails Builder MUST NOT:

- render Draft template/layout as production send input;
- treat arbitrary Elementor/WPBakery/Gutenberg/browser markup as canonical email output;
- execute arbitrary PHP/JS/template language or admin-entered callback/class code;
- expose Vault/provider credentials/passwords/application passwords/OAuth tokens/raw protected user meta as generic tokens;
- resolve protected token/entity/attachment data without render-time Policy;
- inject unvalidated token text into HTML attributes, URLs or mail headers;
- put hidden recipients in template by default;
- allow CR/LF header injection;
- attach private files merely because the actor who designed the template can view them;
- execute unbounded Query/repeater results;
- share personalized/member/private rendered output under a public cache key;
- let preview/test send mutate production business state;
- silently re-render queued deterministic messages on retry;
- claim transport acceptance/delivery/inbox/read truth from rendering success;
- infer SPF/DKIM/DMARC or deliverability from configured From address;
- claim exact client rendering from approximate desktop/mobile preview;
- make network-shared template imply shared sender credentials or recipient data.

## 8. Stop-the-line conditions

Stop future executable certification immediately for:

- cross-recipient personalized/protected data leakage;
- cross-site rendered-content or credential leakage;
- secret/credential exposure in body/header/preview/log;
- XSS/script/event-handler or unsafe URL scheme surviving renderer where prohibited;
- mail-header injection;
- private attachment delivered without recipient-specific authorization;
- test send causing production workflow mutation;
- stale/retry render silently changing recipient-visible content under same logical snapshot;
- public cache serving protected personalized output;
- renderer marking provider/delivery state it cannot observe;
- unbounded Query/repeater/fan-out render behavior;
- unsupported override intercepting unrelated WordPress/plugin emails globally.

## 9. Relationship to ET transport certification

EBR ends at a normalized, immutable **Rendered Message** handoff boundary.

ET starts at transport/provider interaction:

- ET0 configured/connectable;
- ET1 submission;
- ET2 resilient submission;
- ET3 delivery truth;
- ET4 feedback/suppression/reconciliation;
- ET5 production provider profile.

An EBR pass never promotes ET state. An ET pass does not prove template/token/render correctness unless the corresponding EBR fixtures also pass.

## 10. Current evidence state

- Documented fixtures: **176**.
- Executed fixtures: **0/176**.
- `EBR-D/EBR-C/EBR-H/EBR-T/EBR-E/EBR-A/EBR-P/EBR-I/EBR-M/EBR-O` certifications: **0**.
- exact renderer/CSS inliner dependency: **OPEN**.
- exact email-client compatibility matrix: **OPEN**.
- exact size/attachment/render budgets: **OPEN**.
- WordPress core email override adapter certifications: **0**.
- third-party email override adapter certifications: **0**.
- existing transport/provider evidence remains **6 EE3 / 0 ET-certified** and is unchanged by this protocol.

## 11. Evidence report format

Every future execution batch reports:

`Status / Changed / Why / Research / Tests / Security / Data-Migration / Affected / VCS / Docs-Memory / Known Issues / Not Verified / Next Safe Action`

Additionally record fixture IDs, pass/fail/blocked, renderer profile/dependencies, Template/Layout revisions, context/provider versions, certification classes established/rejected, output/size/performance measurements, security/privacy findings, and downstream ET state separately.

## 12. Development gate

Execution of EBR-01…EBR-176 requires explicit scoped owner authorization under ADR-0014 and the Approval Ledger.

Planning acceptance of this protocol is not implementation, rendering, test-send, transport or provider consent.