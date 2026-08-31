# WPEssential — User Profile Executable Security Evidence Protocol

Status: **Phase 0 consent-gated protocol / 0 fixtures executed**  
Date: 2026-08-28  
Work package refinement: `P0-M00-WP41`  
Related: ADR-0030, ADR-0096, ADR-0113, `docs/SECURITY/USER-PROFILE-RUNTIME-AUTHORITY-EVIDENCE-PROFILE.md`, FST, DSR, KPA, RA, PDL, ERR, CAC, VER, MLC, REST, Membership, Multisite, ADR-0014.

## 1. Purpose

Define executable evidence required before User Profile Builder can claim secure self/admin profile editing, identity changes, credential/session actions, Application Password actions, public projection, Field Storage integration, privacy, Multisite or scale behavior.

The original **UP-01…UP-48** semantics remain preserved. This canonical refinement extends the fixed matrix to **UP-01…UP-176**.

Current execution truth: **0/176 executed**.

Authority invariant:

**WordPress remains native identity/auth authority; generic Profile fields cannot mutate credentials, roles/capabilities, sessions, Membership entitlement, Vault/provider secrets or other protected security internals.**

Passing FST/DSR/KPA/RA/PDL/CAC/REST evidence never auto-certifies User Profile behavior.

## 2. Runtime certification profile

Record exact WordPress/PHP/database versions; single-site/Multisite topology; UP1/UP2/UP3 storage profiles; Field Storage/Data Source/Profile Definition versions; recent-auth/email delivery/session/Application Password environment; third-party protected-meta providers; public/REST/listing projection profile; CAC cache profile; privacy exporter/eraser profile; role/membership integrations; media/avatar adapter; scale fixture.

Independent certification classes:
- `UP-I` native identity/user-field authority;
- `UP-F` ordinary/custom Field Storage editing;
- `UP-S` protected security boundaries;
- `UP-E` email/identity change workflows;
- `UP-C` password/session/Application Password actions;
- `UP-P` public/REST/listing projection;
- `UP-M` media/avatar and external integrations;
- `UP-R` privacy/retention/audit/cache;
- `UP-N` Multisite/network authority;
- `UP-O` concurrency/versioning/observability/scale.

# 3. Original self/admin/protected-field fixtures — UP-01…UP-16
- **UP-01** — authenticated user edits only allowlisted ordinary self fields.
- **UP-02** — submitted user-ID spoof cannot edit another user.
- **UP-03** — authorized administrator edits declared ordinary fields subject to `edit_user`/Policy.
- **UP-04** — insufficient actor cannot edit another user even when action URL is known.
- **UP-05** — generic binding/read/write to `user_pass`/password hash is blocked.
- **UP-06** — activation/reset internals cannot be generic Profile fields.
- **UP-07** — role/capability meta families cannot be generic read/write fields.
- **UP-08** — session internals/cookies are not Profile fields.
- **UP-09** — Application Password hashes/protected metadata cannot be mass-read/written generically.
- **UP-10** — forged field cannot mutate Membership/Vault/Product License protected state.
- **UP-11** — registered protected third-party meta blocks generic access; unknown meta is not automatically safe.
- **UP-12** — hidden/unknown/protected submitted keys cannot mass-assign user internals.
- **UP-13** — ordinary FS1 typed custom user-meta field stores/reads through approved Field Storage route.
- **UP-14** — same network user can hold site-scoped WPE profile value without cross-site collision.
- **UP-15** — UP2 Custom Table field preserves user/scope/Policy semantics and cannot become parallel identity authority.
- **UP-16** — presentation/key migration preserves ordinary value identity through explicit mapping.

# 4. Original public/identity/security-action fixtures — UP-17…UP-35
- **UP-17** — public projection default-denies private email/phone/address/security/internal fields.
- **UP-18** — explicit public field renders only after server-side Profile/Field Policy.
- **UP-19** — public identifier/slug change cannot reveal another user's private fields.
- **UP-20** — REST projection returns only authorized allowlisted fields and no protected internals.
- **UP-21** — user Listing/search reauthorizes field projection and cannot leak values through sort/filter/facet/count.
- **UP-22** — public slug collision/rename deterministic without requiring `user_login` mutation.
- **UP-23** — email candidate normalized/unique; invalid/duplicate fails before authoritative change.
- **UP-24** — old email remains authoritative until accepted confirmation completes.
- **UP-25** — used/expired/cancelled email confirmation cannot replay.
- **UP-26** — old confirmation cannot overwrite newer email intent/current state.
- **UP-27** — email delivery failure leaves old email authoritative and recoverable.
- **UP-28** — high-risk action rejects missing/expired recent-auth assertion.
- **UP-29** — recent-auth assertion is purpose-bound and cannot authorize unrelated security action.
- **UP-30** — password change uses certified native WP semantics; plaintext/hash never logged/returned.
- **UP-31** — declared session invalidation after password change is verified; failure not assumed away.
- **UP-32** — logout-one invalidates selected session without exposing raw token.
- **UP-33** — logout others/all obeys scope/current-session semantics and target authorization.
- **UP-34** — Application Password create shows secret only according to core creation semantics; later plaintext retrieval impossible.
- **UP-35** — typed authorized actions revoke intended Application Password(s); no generic meta mutation.

# 5. Original race/Multisite/privacy fixtures — UP-36…UP-48
- **UP-36** — racing email intents produce deterministic pending/winning state; no stale overwrite.
- **UP-37** — concurrent admin+self ordinary edit uses field/document conflict semantics to avoid unintended loss.
- **UP-38** — stale form cannot recreate/mutate deleted/removed user authority.
- **UP-39** — remove user from site is distinct from global user deletion and requires site authority.
- **UP-40** — network/global user deletion requires proper authority/impact semantics and cannot be ordinary Profile checkbox.
- **UP-41** — Site Admin cannot mutate Super Admin/network authority through Profile Builder.
- **UP-42** — one-site profile export excludes another site's scoped custom fields without explicit network authority.
- **UP-43** — privacy exporter includes WPE-owned eligible fields without credential material.
- **UP-44** — eraser deletes/anonymizes only owned eligible fields and preserves required authority/audit/legal records.
- **UP-45** — Audit redacts password/reset/session/Application Password/OAuth/Vault secrets.
- **UP-46** — missing/corrupt Profile Definition cannot disable native WordPress login/account/recovery.
- **UP-47** — Pro expiry preserves safe deployed runtime/custom values without corrupting identity.
- **UP-48** — reference user/profile/Multisite workload stays bounded with zero protected-field/cross-site leakage.

# 6. Native WordPress identity and core user fields — UP-49…UP-64
- **UP-49** — user ID is canonical native identity; mutable username/email/display name never substitutes for object identity.
- **UP-50** — `user_login` mutability follows actual WordPress semantics; UI cannot promise unsupported rename.
- **UP-51** — `user_nicename`/public slug updates are normalized, collision-safe and distinct from login identity.
- **UP-52** — `display_name` is presentation data and cannot become authorization identity.
- **UP-53** — first/last/nickname/description fields follow native sanitization and allowed write scope.
- **UP-54** — `user_url` validates approved URL semantics and cannot inject unsafe scheme/markup.
- **UP-55** — `user_registered`/internal timestamps are read-only unless a separately certified admin operation exists.
- **UP-56** — `user_status` or legacy/internal fields are not exposed as arbitrary security switches.
- **UP-57** — core locale setting changes only through declared field/action and cannot control another user's site authority.
- **UP-58** — user option/meta keys with site prefixes are not guessed/written by generic custom-field key.
- **UP-59** — WordPress core reserved user-meta keys remain protected even when hidden field name is forged.
- **UP-60** — native user update filters/hooks are observed; effective post-write state is re-read before success claim.
- **UP-61** — plugin/filter rejection/alteration of user update yields truthful result, not assumed requested state.
- **UP-62** — deleting ordinary optional field uses owner schema semantics and never deletes whole user accidentally.
- **UP-63** — user not found/deleted during request returns stable safe error without recreating metadata.
- **UP-64** — ordinary profile read has no mutation/repair side effect.

# 7. Field Storage, Data Source and custom profile schema — UP-65…UP-80
- **UP-65** — Profile Definition pins field UUID/type/storage mapping/revision before edit/render.
- **UP-66** — draft Profile Definition changes do not mutate published runtime form/output.
- **UP-67** — unknown future Profile Definition schema fails/degrades safely.
- **UP-68** — DSR readable user adapter does not imply generic writable security fields.
- **UP-69** — each custom field write requires FST/DSR declared write capability and target Policy.
- **UP-70** — missing/null/empty/default semantics preserve typed Field Schema distinctions.
- **UP-71** — required/enum/range/format validation runs server-side regardless of UI control.
- **UP-72** — repeaters/structured fields enforce depth/count/size and item typing.
- **UP-73** — unique custom profile field concurrency uses target constraint/precondition, not race-prone pre-check alone.
- **UP-74** — computed/derived profile value is not written as canonical storage unless schema explicitly owns projection.
- **UP-75** — Relation field uses Relation engine and target Policy rather than serialized arbitrary user meta.
- **UP-76** — Vault-backed secret reference never returns plaintext through generic profile renderer/export.
- **UP-77** — storage route migration FS1↔FS2 follows reviewed migration/version evidence and preserves ownership.
- **UP-78** — field deletion/disable preserves/removes runtime values only according to explicit retention/lifecycle policy.
- **UP-79** — third-party profile field adapter declares ownership/schema/capability; WPE does not silently take over unknown meta.
- **UP-80** — FST/DSR/REL certification never auto-certifies Profile field mapping.

# 8. Protected security domains and mass assignment — UP-81…UP-96
- **UP-81** — role/capability keys and native role assignments can only be changed through RA actions, never generic Profile submit.
- **UP-82** — Super Admin/network authority cannot be represented as ordinary checkbox/field.
- **UP-83** — password hash, activation/reset, session-token, Application Password and recovery internals stay on protected denylist/registry.
- **UP-84** — Membership Enrollment/Entitlement/Team role cannot be edited through generic user field mapping.
- **UP-85** — Product Entitlement/Site Allocation/Account identity cannot be profile fields.
- **UP-86** — Vault/provider/OAuth tokens cannot be exposed via token resolver/profile field/export.
- **UP-87** — arbitrary `meta_input`-style payload is filtered by declared field schema; unknown keys do not pass through.
- **UP-88** — nested JSON/object payload cannot smuggle protected key at deeper path.
- **UP-89** — duplicate form/body key ambiguity follows deterministic safe parsing; protected key cannot shadow allowed key.
- **UP-90** — client-side hidden/disabled field status never determines server authorization.
- **UP-91** — UI field conditional visibility never grants write permission.
- **UP-92** — administrator editing another user still cannot mutate separately protected domain via generic Profile field.
- **UP-93** — REST/Ability/CLI Profile adapters use same protected field registry and target Policy.
- **UP-94** — Import/Export cannot map package field into protected security meta through Profile adapter.
- **UP-95** — legacy plugin field collision with protected namespace enters explicit conflict/degraded behavior.
- **UP-96** — security deny occurs before sensitive current value is disclosed in validation/error response.

# 9. Email/identity-change lifecycle — UP-97…UP-112
- **UP-97** — self-email change requires current authenticated principal and explicit candidate state.
- **UP-98** — admin-email change for another user follows separate authority/notification/confirmation profile.
- **UP-99** — email uniqueness rechecked immediately before authoritative commit, not only at initial form validation.
- **UP-100** — normalization handles case/Unicode/domain semantics according to WordPress/email policy without creating duplicate authority.
- **UP-101** — confirmation token is high-entropy, one-time, user+intent+purpose+expiry bound and never logged plaintext.
- **UP-102** — confirmation request cannot be replayed for another account/email candidate.
- **UP-103** — confirmation token stored representation follows one-way/secret-safe design where applicable.
- **UP-104** — cancellation invalidates intended pending email without changing current email.
- **UP-105** — newer password/account-security event may invalidate pending sensitive identity intent according to policy.
- **UP-106** — email provider submission/delivery truth is separated; send accepted ≠ delivered/read.
- **UP-107** — failed notification to old/new email cannot silently produce partial authoritative change contrary to policy.
- **UP-108** — email change after account deletion/disable is rejected and token cannot resurrect user.
- **UP-109** — site removal does not invalidate global email identity arbitrarily; global/site ownership remains explicit.
- **UP-110** — identity-change Audit records intent/confirmation/result without token or sensitive payload.
- **UP-111** — stale CAC/public profile entries invalidate after authoritative email/public-projection change as required.
- **UP-112** — exact email-change workflow certification is scoped to tested WP/plugin/provider version profile.

# 10. Password, sessions and Application Passwords — UP-113…UP-128
- **UP-113** — self password change requires native current-auth/recent-auth policy as accepted; generic field never receives hash.
- **UP-114** — admin reset/change for another user is a separate privileged action with target Policy and audit.
- **UP-115** — password quality/validation follows declared policy without logging plaintext candidate.
- **UP-116** — password update failure/filters are re-read and never reported success by request intent alone.
- **UP-117** — session invalidation semantics after password change are measured against exact WP profile.
- **UP-118** — logout-current, logout-other and logout-all are distinct actions with explicit target/session semantics.
- **UP-119** — raw session verifier/token is never exposed in UI/REST/Audit/diagnostics.
- **UP-120** — concurrent session revocations remain idempotent and cannot revoke another user's sessions by forged target.
- **UP-121** — Application Password list shows approved metadata only, never secret/hash.
- **UP-122** — Application Password creation is purpose/name-bound and secret reveal occurs once according to core semantics.
- **UP-123** — duplicate/replayed creation request does not create uncontrolled multiple credentials when idempotency profile applies.
- **UP-124** — revoke-one identifies exact credential reference, not caller-supplied raw secret.
- **UP-125** — revoke-all for another user requires explicit privileged authority/recent-auth where policy requires.
- **UP-126** — Application Password availability/version feature detection fails safely on unsupported environment.
- **UP-127** — Profile Definition/module disable cannot disable native core credential recovery/management paths required for safety.
- **UP-128** — KPA/RA credential/security action registrations remain typed and cannot become arbitrary user-meta writes.

# 11. Public projection, discovery and cache safety — UP-129…UP-144
- **UP-129** — public profile route exists only for explicitly published/public profile configuration.
- **UP-130** — user enumeration through slug/search/count/error is bounded by accepted disclosure policy.
- **UP-131** — non-public user does not become discoverable through alternate numeric ID/REST/listing path accidentally.
- **UP-132** — public fields are allowlisted by field/revision, not “all meta except denylist”.
- **UP-133** — field publicness change public→private invalidates CAC/page/fragment caches before continued exposure.
- **UP-134** — private→public change does not reuse admin/private representation blindly.
- **UP-135** — per-viewer/protected profile output keys cache by authorization generation/principal class as required.
- **UP-136** — listing sort/filter/facet/count cannot infer protected field values or membership/role facts beyond Policy.
- **UP-137** — search indexing stores only approved public/searchable projection and reauthorizes result fetch.
- **UP-138** — avatar/media URL/reference follows approved privacy/protected-delivery policy and cannot expose private attachment by possession.
- **UP-139** — rich bio/content output follows typed sanitization/escaping; generic value is not trusted HTML.
- **UP-140** — external/social URL fields reject unsafe schemes/control characters and escape per render context.
- **UP-141** — schema.org/SEO metadata includes only public authorized profile values.
- **UP-142** — browser/page/CDN caching of public profile is safe only for genuinely public representation.
- **UP-143** — cache backend failure never causes private/admin representation to fall back into public output.
- **UP-144** — REST/Listings/CAC/DVR certification remains separate from UP public-projection certification.

# 12. Media/avatar and third-party integrations — UP-145…UP-152
- **UP-145** — avatar upload uses approved media pipeline, MIME/size/dimension/storage/Policy and never arbitrary executable path.
- **UP-146** — replacing/removing avatar does not delete shared/unowned Media item blindly.
- **UP-147** — external avatar provider URL/data is treated as provider data, not trusted HTML/JS.
- **UP-148** — media offload/private-object credentials remain Vault-backed and absent from profile payload.
- **UP-149** — third-party profile hook/field registration cannot claim reserved WPE/core protected namespace.
- **UP-150** — third-party field/provider outage degrades only owned field where possible; native account screen/recovery remains usable.
- **UP-151** — provider/version drift invalidates stale adapter assumptions and requires revalidation.
- **UP-152** — deactivating third-party provider does not authorize WPE to delete its user meta/media automatically.

# 13. Privacy, retention, lifecycle and Audit — UP-153…UP-164
- **UP-153** — PDL classification distinguishes public, personal, sensitive, security/internal and operational profile data.
- **UP-154** — privacy export uses current authorization/owner mapping and excludes security credential material.
- **UP-155** — privacy erase/anonymize follows per-field owner policy and does not destroy native authorization/security records required for account integrity.
- **UP-156** — user deletion lifecycle invokes owned cleanup/reassignment through explicit domain rules, not generic meta wipe only.
- **UP-157** — site removal cleans site-owned profile values according to policy without deleting global user identity.
- **UP-158** — module disable preserves data by default; uninstall cleanup follows MLC and explicit delete-data choice.
- **UP-159** — Pro expiry cannot make existing profile data inaccessible in a way that breaks native account recovery or security enforcement.
- **UP-160** — Definition/schema version migration preserves values/classification and cannot silently reclassify private field public.
- **UP-161** — backup/restore revalidates user/profile scope and does not resurrect stale sessions/tokens/access authority as current truth.
- **UP-162** — Audit records identity/security action outcomes and ordinary sensitive admin changes with redaction/retention profile.
- **UP-163** — support diagnostics expose schema/health/conflict metadata but not private field values/credentials by default.
- **UP-164** — privacy/Audit/Backup/MLC certifications remain independently earned; UP never auto-promotes them.

# 14. Multisite, concurrency, performance and final truth — UP-165…UP-176
- **UP-165** — network-global core user identity and site-owned custom Profile documents remain explicitly distinct.
- **UP-166** — Site A admin cannot edit Site B scoped profile values/roles by forged site coordinate.
- **UP-167** — Network Admin/Super Admin profile action still obeys exact target scope and protected-domain separation.
- **UP-168** — `switch_to_blog()` changes operational context only and never grants target/network authority.
- **UP-169** — site clone does not copy another user's/global identity/security tokens; site-scoped profile configuration/value copying follows explicit clone policy.
- **UP-170** — site transfer/network move remaps site-owned profile scope without changing global user identity unexpectedly.
- **UP-171** — concurrent self/admin edits on disjoint fields preserve both; overlapping changes conflict/version according to document profile.
- **UP-172** — concurrent email/security/custom-field actions cannot lose or overwrite protected state through stale full-user write.
- **UP-173** — 100k-user public/list/admin profile workload measures Query/cache/memory latency with zero protected projection leakage.
- **UP-174** — 100/1k/10k-site network fixture measures site-scoped profile isolation and avoids unbounded synchronous all-site loops.
- **UP-175** — performance optimization cannot bypass target Policy, protected-field registry, RA separation, PDL or CAC revoke semantics.
- **UP-176** — final report scopes certification to exact WP/storage/provider/topology/version profile and refuses generic “User Profile secure/certified” overclaim.

## 15. MUST NOT / stop-the-line gates

Stop affected certification if:
- generic Profile field mutates password/role/cap/session/Application Password/Membership/Vault/provider secret;
- self target spoof edits another user;
- Site Admin gains network/Super Admin authority;
- public/REST/listing/search/cache projection exposes protected field or private existence beyond accepted policy;
- stale email confirmation overwrites newer identity state;
- plaintext credential/token appears in logs/history/export/cache;
- one site reads/writes another site's scoped profile data;
- WPE Profile failure disables native WordPress login/account recovery;
- generic mass assignment writes reserved core/protected/third-party security meta;
- passing FST/DSR/KPA/RA/CAC/shared evidence is used to claim UP certification.

## 16. Required future evidence report

Include runtime/authority/storage/provider/topology profile; UP-01…UP-176 pass/fail/N/A; protected binding/mass-assignment/IDOR; core/custom fields; email/recent-auth race/replay; password/session/Application Password; public/REST/listing/search/cache leakage; media/third-party adapters; privacy/lifecycle/Audit; Multisite; concurrency and large-user/site performance; certification classes earned; unsupported/degraded profiles.

## 17. Current state

- UP fixtures documented: **176**.
- UP fixtures executed: **0/176**.
- UP runtime certifications: **0**.

No user mutation, email change, password/session/Application Password action, privacy operation, cache invalidation, media upload, role mutation or Multisite runtime test has been executed.

## 18. Development gate

Execution requires explicit owner consent under ADR-0014 and the Approval Ledger.