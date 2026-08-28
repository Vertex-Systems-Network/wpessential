# WPEssential — Free CPT & Taxonomy Executable Evidence Protocol

Status: **Accepted planning protocol / execution pending**  
Date: 2026-08-28  
Work package: `P0-M00-WP21`  
Execution mode: `PLANNER_ONLY`  
Development authorization: **NOT GRANTED**

Related: `docs/MODULES/FREE-CPT-TAXONOMY-EXHAUSTIVE-SPEC.md`, `docs/MODULES/CONTENT-MODEL-SPECS.md`, `docs/MODULES/MULTISITE-SCOPE-OPTION-MATRIX.md`, ADR-0002, ADR-0014, ADR-0049, ADR-0069, ADR-0071, ADR-0075, ADR-0092/0132, ADR-0123, ADR-0128.

## 1. Purpose

Freeze the future executable evidence required before WPEssential Free may claim production-safe Custom Post Type or Taxonomy registration, rewrite/query behavior, REST/editor compatibility, capability mapping, definition lifecycle, external-registration coexistence, Multisite rollout or upgrade safety.

This protocol does **not** authorize PHP registration hooks, rewrite flushes, REST route creation, content/term mutation, migration, build, WordPress execution, browser tests, CI or benchmarks.

## 2. Runtime truth model

The following are distinct truths:

`Draft Definition ≠ Published Revision ≠ validated registration descriptor ≠ WordPress registered object ≠ rewrite/query state ≠ REST/editor state ≠ persisted posts/terms ≠ migration state ≠ certified runtime behavior`

Likewise:

`WPE ownership ≠ runtime key collision ≠ external registration discovery ≠ safe takeover/import-to-ownership`.

A successful `register_post_type()` or `register_taxonomy()` call alone does not prove permalink correctness, REST/editor compatibility, capability safety, preserved data, Multisite correctness or safe coexistence.

## 3. First operational baseline

Future implementation should compile published WPE definitions into validated native WordPress registration descriptors and register them at the canonical supported lifecycle boundary.

Core invariants:

1. Draft definitions never alter runtime registration.
2. WPE owns only WPE-created/imported definitions; core/external registrations are read-only unless a separately certified takeover path exists.
3. Post type/taxonomy runtime keys become migration-class identities after publication/reference.
4. rewrite changes are dirty-marked and flushed only at a controlled safe lifecycle point, never every request.
5. Definition disable/delete does not delete posts, terms, term relationships or meta by implication.
6. capability changes pass impact/anti-lockout review.
7. REST/controller/callback extension points accept registered adapters, not arbitrary executable class/function input.
8. CPT↔taxonomy associations are represented consistently across both WordPress registration surfaces.
9. site context is authoritative in Multisite; a network template does not make site content shared.
10. unsupported/version-dependent registration arguments degrade explicitly according to the accepted compatibility floor.

## 4. Certification classes

Certify independently:

- `CPTX-CPT` — CPT native registration and editor/admin behavior;
- `CPTX-TAX` — Taxonomy native registration and term UI behavior;
- `CPTX-RW` — rewrite/archive/query-var/permalink lifecycle;
- `CPTX-REST` — REST/block-editor/controller compatibility;
- `CPTX-CAP` — capability/meta-cap/anti-lockout behavior;
- `CPTX-OWN` — collision/discovery/coexistence/ownership safety;
- `CPTX-LC` — Definition/activation/update/disable/delete/rollback lifecycle;
- `CPTX-MIG` — key/high-risk migration evidence;
- `CPTX-MS` — Multisite rollout/scope/site-lifecycle behavior;
- `CPTX-COMP` — WordPress/theme/plugin compatibility and diagnostics.

Current certifications: **0**.

## 5. Fixed fixture matrix — CPTX-01…CPTX-176

### A. Definition identity, validation and publish boundary — CPTX-01…CPTX-16

- **CPTX-01** valid one-character CPT key.
- **CPTX-02** valid 20-character CPT key.
- **CPTX-03** >20-character CPT key rejected before Publish.
- **CPTX-04** CPT key normalization/case/space behavior is explicit and collision-safe.
- **CPTX-05** valid one-character taxonomy key.
- **CPTX-06** valid 32-character taxonomy key.
- **CPTX-07** >32-character taxonomy key rejected before Publish.
- **CPTX-08** taxonomy key normalization/case/space behavior is explicit and collision-safe.
- **CPTX-09** reserved/core CPT key rejected.
- **CPTX-10** reserved/core taxonomy key rejected.
- **CPTX-11** duplicate WPE CPT definition key rejected.
- **CPTX-12** duplicate WPE taxonomy definition key rejected.
- **CPTX-13** Draft CPT revision does not register runtime type.
- **CPTX-14** Draft taxonomy revision does not register runtime taxonomy.
- **CPTX-15** Publish resolves immutable Definition UUID + published revision and validated descriptor.
- **CPTX-16** historical revision remains immutable; rollback promotes known revision rather than rewriting history.

### B. CPT native visibility/admin registration — CPTX-17…CPTX-32

- **CPTX-17** public-content CPT preset expands to explicit supported values.
- **CPTX-18** admin-only CPT preset has no unintended public query/rewrite exposure.
- **CPTX-19** headless/API preset requires explicit public/query/rewrite review.
- **CPTX-20** hierarchical CPT registration and parent semantics.
- **CPTX-21** `exclude_from_search` inherited vs explicit true/false truth.
- **CPTX-22** `publicly_queryable` inherited vs explicit truth.
- **CPTX-23** `show_ui` inherited vs explicit truth.
- **CPTX-24** `show_in_menu` top-level behavior.
- **CPTX-25** `show_in_menu` hidden behavior.
- **CPTX-26** `show_in_menu` validated submenu-parent behavior.
- **CPTX-27** `show_in_nav_menus` inherited/explicit behavior.
- **CPTX-28** `show_in_admin_bar` inherited/explicit behavior.
- **CPTX-29** menu position collision does not imply deterministic exclusive slot ownership.
- **CPTX-30** Dashicon/default menu icon registration.
- **CPTX-31** sanitized registered custom icon adapter path; arbitrary unsafe SVG/data URI rejected.
- **CPTX-32** internal `_builtin`/`_edit_link` are not user-writable configuration.

### C. CPT supports, capabilities and taxonomy association — CPTX-33…CPTX-48

- **CPTX-33** title + editor default supports.
- **CPTX-34** editor/autosave effective behavior matches supported WordPress semantics.
- **CPTX-35** supports=false/no-feature configuration.
- **CPTX-36** thumbnail support + theme-support diagnostic.
- **CPTX-37** hierarchical + page-attributes parent behavior.
- **CPTX-38** page-attributes/menu-order behavior when hierarchy is false.
- **CPTX-39** revisions/comments/author/excerpt/custom-fields support parity.
- **CPTX-40** unknown discovered support preserved read-only; not silently discarded.
- **CPTX-41** registered extension support descriptor arguments validated by schema.
- **CPTX-42** default `post/posts` capability model.
- **CPTX-43** `page/pages` capability model.
- **CPTX-44** generated singular/plural capability model + effective primitive/meta-cap inventory.
- **CPTX-45** advanced explicit capability map validity.
- **CPTX-46** capability change impact preview and current-admin anti-lockout.
- **CPTX-47** WPE-owned taxonomy attachment appears consistently in CPT + taxonomy/object-type registration.
- **CPTX-48** external taxonomy attachment disappearance degrades safely without deleting stored relationships.

### D. CPT REST, archive, rewrite, query var and lifecycle — CPTX-49…CPTX-64

- **CPTX-49** `show_in_rest=true` CPT with expected block-editor interoperability.
- **CPTX-50** REST-disabled CPT explicitly reports editor/API impact.
- **CPTX-51** default REST base/namespace.
- **CPTX-52** custom REST base validated and collision-checked.
- **CPTX-53** custom REST namespace validated/versioned and treated as integration-impacting.
- **CPTX-54** default REST controller path.
- **CPTX-55** registered custom controller/autosave/revision adapter path only; arbitrary class input rejected.
- **CPTX-56** late route registration behavior only where compatibility floor supports it.
- **CPTX-57** archive disabled.
- **CPTX-58** archive enabled with CPT key/default slug.
- **CPTX-59** custom archive slug + rewrite interaction.
- **CPTX-60** rewrite disabled/default/custom including `with_front`, `feeds`, `pages`, controlled `ep_mask`.
- **CPTX-61** query-var default/disabled/custom with collision validation.
- **CPTX-62** `can_export` controls WP content export independently from WPE definition export.
- **CPTX-63** `delete_with_user` inherit/keep/delete effective semantics including author-support interaction.
- **CPTX-64** disable/archive/delete definition preserves existing posts/meta/revisions and reports inaccessible admin/public paths truthfully.

### E. Taxonomy native registration, UI and object types — CPTX-65…CPTX-80

- **CPTX-65** category-like taxonomy preset.
- **CPTX-66** tag-like taxonomy preset.
- **CPTX-67** internal-classification preset has no unintended public exposure.
- **CPTX-68** hierarchical taxonomy parent semantics.
- **CPTX-69** hierarchy toggle preserves existing parent data and hierarchy-sensitive labels.
- **CPTX-70** public/publicly-queryable inheritance truth.
- **CPTX-71** show-ui/show-menu inheritance truth.
- **CPTX-72** show-in-nav-menus behavior.
- **CPTX-73** show-tagcloud behavior.
- **CPTX-74** show-in-quick-edit behavior.
- **CPTX-75** core simple `show_admin_column` behavior remains distinct from Pro Admin Columns.
- **CPTX-76** publish with no object type warns/requires explicit accepted behavior.
- **CPTX-77** WPE-owned object type add preserves terms/relationships.
- **CPTX-78** WPE-owned object type remove preserves terms/relationships unless separate cleanup is authorized.
- **CPTX-79** missing external object type degrades safely.
- **CPTX-80** WPE does not overwrite third-party taxonomy object-type ownership merely because same taxonomy is discovered.

### F. Taxonomy REST, editing, capabilities, rewrite and advanced semantics — CPTX-81…CPTX-96

- **CPTX-81** taxonomy REST enabled for block-editor term-panel interoperability.
- **CPTX-82** taxonomy REST disabled with explicit editor/API impact.
- **CPTX-83** taxonomy REST base/namespace default + validated custom collision behavior.
- **CPTX-84** registered taxonomy REST controller adapter only; arbitrary class rejected.
- **CPTX-85** automatic hierarchical/non-hierarchical meta-box behavior.
- **CPTX-86** meta box disabled path.
- **CPTX-87** registered `meta_box_cb` / sanitize adapter only; arbitrary callable rejected.
- **CPTX-88** WordPress default manage/edit/delete/assign term capabilities.
- **CPTX-89** generated/explicit taxonomy capability map + anti-lockout impact.
- **CPTX-90** taxonomy rewrite disabled/default/custom.
- **CPTX-91** taxonomy data hierarchy and `rewrite.hierarchical` remain distinct.
- **CPTX-92** taxonomy query-var default/disabled/custom collision behavior.
- **CPTX-93** automatic vs registered `update_count_callback`; arbitrary callback rejected.
- **CPTX-94** default-term new/existing creation is idempotent; deletion later diagnoses missing default.
- **CPTX-95** `sort` true/false persistence semantics are truthful without promising consumer display ordering.
- **CPTX-96** taxonomy `args` are typed/allowlisted; arbitrary serialized PHP arrays rejected.

### G. Collision, discovery, external ownership and coexistence — CPTX-97…CPTX-112

- **CPTX-97** runtime CPT key already registered by core/plugin/theme before WPE registration.
- **CPTX-98** runtime taxonomy key already registered externally.
- **CPTX-99** CPT key vs harmful taxonomy/query namespace collision.
- **CPTX-100** taxonomy key vs harmful CPT/query namespace collision.
- **CPTX-101** CPT query-var collision with reserved/public/private vars.
- **CPTX-102** taxonomy query-var collision.
- **CPTX-103** CPT REST route collision.
- **CPTX-104** taxonomy REST route collision.
- **CPTX-105** CPT rewrite/permalink collision preflight known collision detected.
- **CPTX-106** taxonomy rewrite/permalink collision preflight known collision detected.
- **CPTX-107** ambiguous/undetectable rewrite collision is surfaced as limitation, not guaranteed collision-free.
- **CPTX-108** external CPT discovery is read-only and does not create WPE ownership.
- **CPTX-109** external taxonomy discovery is read-only.
- **CPTX-110** create-WPE-definition-from-effective-external-registration remains inactive while original owner still registers same key.
- **CPTX-111** hook priority/race is not used as generic takeover strategy.
- **CPTX-112** core objects remain read-only outside separately designed presentation integrations.

### H. Definition revision, high-risk migration and data preservation — CPTX-113…CPTX-128

- **CPTX-113** label-only CPT revision update does not mutate content.
- **CPTX-114** label-only taxonomy revision update does not mutate terms.
- **CPTX-115** CPT visibility/admin setting update preserves posts/meta/terms.
- **CPTX-116** taxonomy visibility/UI update preserves terms/relationships.
- **CPTX-117** CPT taxonomy detach changes registration but not term relationships automatically.
- **CPTX-118** taxonomy object-type detach preserves relationship rows unless explicit cleanup exists.
- **CPTX-119** CPT key change is blocked as ordinary edit after publication/reference.
- **CPTX-120** taxonomy key change is blocked as ordinary edit.
- **CPTX-121** future CPT key migration plan inventories `wp_posts.post_type`, revisions/autosaves, relationships, nav links, REST/rewrite/dependencies/third-party refs.
- **CPTX-122** future taxonomy-key migration plan inventories `term_taxonomy.taxonomy`, relationships, options/default term, nav links, REST/rewrite/dependencies/third-party refs.
- **CPTX-123** capability namespace change is migration/high-risk when roles/users depend on old caps.
- **CPTX-124** large permalink identity change requires impact/redirect/recovery planning rather than silent rewrite.
- **CPTX-125** block-template change does not rewrite existing post content automatically.
- **CPTX-126** missing registered block preserves Definition data and degrades editor diagnostic.
- **CPTX-127** generated PHP preview is deterministic/read-only and never canonical execution/storage.
- **CPTX-128** delete Definition with hard WPE dependencies is blocked/impact-reviewed while underlying WP data remains preserved.

### I. Activation, update, rewrite-flush and rollback lifecycle — CPTX-129…CPTX-144

- **CPTX-129** registration occurs no earlier than accepted `init` lifecycle boundary.
- **CPTX-130** plugin activation/bootstrap does not rely on every-request rewrite flush.
- **CPTX-131** first Publish marks rewrite state dirty only when required.
- **CPTX-132** one controlled rewrite flush clears matching dirty generation after successful registration state.
- **CPTX-133** repeated normal requests do not reflush unchanged rules.
- **CPTX-134** multiple CPT/Tax changes coalesce into bounded safe flush behavior.
- **CPTX-135** failed/partial registration does not clear rewrite-dirty truth prematurely.
- **CPTX-136** plugin deactivation does not delete WPE Definitions or WP content/terms.
- **CPTX-137** plugin reactivation restores published WPE registrations without duplicating default terms or destructive mutation.
- **CPTX-138** WordPress/plugin update revalidates compatibility-dependent arguments before claiming healthy registration.
- **CPTX-139** rollback to prior labels restores descriptor without touching content.
- **CPTX-140** rollback to prior rewrite settings schedules controlled refresh rather than mutating history.
- **CPTX-141** rollback to unsupported historical option on older floor degrades visibly instead of executing unknown behavior.
- **CPTX-142** dependency missing during boot cannot fatal entire WordPress request; affected Definition is degraded where safely possible.
- **CPTX-143** conflicting external late registration after WPE boot is detected/diagnosed where WordPress exposes enough evidence.
- **CPTX-144** registration health reflects effective runtime object, not merely stored published Definition.

### J. Multisite, network templates, import/export, clone and restore — CPTX-145…CPTX-160

- **CPTX-145** normal site-scoped CPT registers only in target site context.
- **CPTX-146** normal site-scoped taxonomy resolves object types in target site.
- **CPTX-147** network CPT template targets explicit eligible sites; no implicit all-site data sharing.
- **CPTX-148** network taxonomy template resolves missing object types independently per site.
- **CPTX-149** site-specific CPT slug/rewrite conflict can skip/report/block rollout according to explicit policy.
- **CPTX-150** site-specific taxonomy rewrite/query-var conflict preflight.
- **CPTX-151** site labels/options override only fields allowed by network policy.
- **CPTX-152** network-enforced registration blocks unauthorized site divergence while preserving site content.
- **CPTX-153** new-site lifecycle applies only configured template/inheritance behavior.
- **CPTX-154** archived/spam/deleted site is skipped/deprovisioned according to Site Lifecycle policy without cross-site data loss.
- **CPTX-155** unlink/deprovision keeps site-local posts/terms unless explicit migration/destructive action says otherwise.
- **CPTX-156** network Definition delete shows dependent-site impact and does not delete site content/terms by implication.
- **CPTX-157** site export/import preserves Definition UUID/dependencies and reports runtime-key collisions.
- **CPTX-158** import never treats key collision as identity equality and never automatically deletes existing content/terms.
- **CPTX-159** clone/restore reconciles UUID/key dependencies without accidental cross-site remap or duplicate ownership.
- **CPTX-160** same runtime key on different sites remains site-scoped identity; network coordination never assumes site A owner equals site B owner.

### K. Compatibility, security, diagnostics and scale — CPTX-161…CPTX-176

- **CPTX-161** accepted minimum WordPress floor registration argument matrix.
- **CPTX-162** planning-reference/newer WordPress argument matrix with graceful unsupported-option handling on minimum floor.
- **CPTX-163** block editor compatibility when `show_in_rest` and supports are compatible.
- **CPTX-164** classic editor/no-block context remains functional where supported.
- **CPTX-165** theme compatibility diagnostic for thumbnails/templates/public rendering does not overclaim theme behavior.
- **CPTX-166** third-party plugin observing CPT taxonomy association sees consistent object-type relation.
- **CPTX-167** external code attempting unsupported takeover/collision cannot silently mutate WPE Definition ownership.
- **CPTX-168** no user-entered arbitrary callback/class/PHP/serialized executable value reaches registration args.
- **CPTX-169** REST exposure follows capability/WordPress permission behavior and does not grant WPE Policy bypass.
- **CPTX-170** generated labels/descriptions/route values are escaped/sanitized at correct context without corrupting stored human text.
- **CPTX-171** diagnostics identify content/term counts at risk before disable/delete/high-impact change.
- **CPTX-172** 100 WPE CPT/taxonomy Definitions boot/register workload records registration/rewrite/admin/REST cost.
- **CPTX-173** 1k sites × bounded target-template rollout planning workload records Job/fan-out requirements without executing until consent.
- **CPTX-174** large hierarchical CPT/taxonomy datasets expose admin/permalink/query warnings based on measured evidence rather than hidden limits.
- **CPTX-175** concurrent Definition publish/update uses stale-revision conflict protection before runtime descriptor replacement.
- **CPTX-176** pathological collision/invalid/unsupported configuration is blocked/degraded with diagnostics rather than corrupting runtime registration.

## 6. Required measurements/evidence

For applicable future fixtures record:

- WordPress/PHP/DB versions and Multisite mode;
- active theme/editor/plugin compatibility context;
- Definition UUID + revision + runtime key;
- effective registration args after inheritance/default resolution;
- registered WordPress object snapshot after lifecycle hook;
- REST route/controller/editor availability where applicable;
- rewrite dirty generation + flush count/timing + resulting route/permalink observation;
- content/term/relationship counts before and after lifecycle changes;
- role/capability impact and current-admin access result;
- collision/discovery owner evidence and hook timing when relevant;
- site/network target identity for Multisite;
- request/boot query/time/memory measurements for scale fixtures;
- diagnostics/audit/correlation artifact references.

Exact rewrite-collision detection limits, performance thresholds and compatibility version matrix remain evidence-gated.

## 7. MUST NOT / negative requirements

Free CPT/Taxonomy runtime MUST NOT:

- register Draft definitions;
- flush rewrite rules on every request;
- treat Definition delete/disable as content/term deletion;
- silently rename a published runtime key as a normal edit;
- race hook priority to generically take over third-party/core registrations;
- accept arbitrary PHP callback/class/function, executable serialized values or raw code in registration configuration;
- mark client/admin hiding as authorization;
- expose REST/editor compatibility claims when `show_in_rest`/controller/support evidence disagrees;
- silently rewrite existing post content when block template changes;
- silently delete terms/relationships when taxonomy object types detach;
- assume taxonomy data hierarchy equals URL rewrite hierarchy;
- treat import key collision as Definition identity;
- let a network template imply shared post/term data;
- clear rewrite-dirty state before effective registration/flush success is established;
- claim collision-free rewrite routing when WordPress/plugin/theme interactions cannot be proven exhaustively.

## 8. Stop-the-line conditions

Stop future executable certification immediately for:

- content/term/relationship loss caused by non-destructive Definition lifecycle action;
- current-admin lockout caused by capability change without recovery path;
- cross-site registration/data mutation outside authorized target site;
- dual ownership overriding core/third-party registration unexpectedly;
- unsafe arbitrary callback/class/code execution from saved configuration;
- rewrite flush loop or material request-wide performance regression;
- REST route exposing content beyond native capability/declared policy boundary;
- key migration partially applied with mixed runtime/data identity;
- rollback that makes stored content/terms unrecoverable;
- silent runtime registration disagreement while health reports success.

## 9. Current evidence state

- Documented fixtures: **176**.
- Executed fixtures: **0/176**.
- `CPTX-CPT/CPTX-TAX/CPTX-RW/CPTX-REST/CPTX-CAP/CPTX-OWN/CPTX-LC/CPTX-MIG/CPTX-MS/CPTX-COMP` certifications: **0**.
- accepted WordPress compatibility floor: not runtime certified.
- exact reserved-name/query-var registry strategy: evidence/update process still OPEN.
- exact rewrite collision-detection completeness: OPEN.
- external import-to-ownership/takeover runtime certification: **0 / unsupported by default**.
- key migrations: **not implemented / not executed**.

## 10. Evidence report format

Every future execution batch reports:

`Status / Changed / Why / Research / Tests / Security / Data-Migration / Affected / VCS / Docs-Memory / Known Issues / Not Verified / Next Safe Action`

Additionally record fixture IDs, pass/fail/blocked, environment versions, certification classes established/rejected, measured rewrite/REST/admin behavior, data-preservation checks and remaining unsupported contexts.

## 11. Development gate

Execution of CPTX-01…CPTX-176 requires explicit scoped owner authorization under ADR-0014 and the Approval Ledger.

Planning acceptance of this protocol is not implementation or runtime consent.