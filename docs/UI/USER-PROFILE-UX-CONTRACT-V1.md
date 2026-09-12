# User Profile — UX Contract V1

Surface: **14 / User Profiles**  
Machine source: `config/product/option-contracts/profiles.json`  
Lifecycle: **UX certification candidate**. The machine contract is `OPTION_CONTRACT_COMPLETE`; lifecycle/runtime promotion is not performed here.

## Lifecycle preconditions

- Machine status must remain `OPTION_CONTRACT_COMPLETE` or later with `missing=0` and `unclassified=0`.
- All 17 current Atomic Option IDs are mapped exactly once below.
- Self-service and admin-edit flows remain server-authoritative and must preserve owner-specific validation and authorization.

## Canonical route and information architecture

Canonical admin location: **WPEssential → Identity → User Profiles**.

The IA separates **Profile Schema**, **Public Profile**, **Registration**, **Access & Recovery**, **Directory**, **Portability**, and **Privacy**. Admin configuration is distinct from the member-facing profile/edit/register/login/directory experiences.

## UX state classes

### Authored definition
Profile content/privacy/editability, public route/template/visibility, registration policy, verification and directory controls are revisioned Profile-owned definitions.

### Effective/runtime state
Current actor, self-vs-admin scope, effective field editability, visibility and verification state are derived/read-only context.

### Diagnostic/provider state
Fields, Media, Roles and Query/Directory sources are canonical-owner references. Missing providers show explicit unavailable/degraded state and cannot be silently replaced.

### Deferred / prohibited state
Profile UX cannot directly grant roles/capabilities, bypass Media authorization, or redefine Fields validation. Privacy export/erasure is never a simple destructive UI toggle.

## Atomic Option → UX map

- `profiles.schema.fields` — Profile Schema → Fields-owned field-group/schema reference picker with owner diagnostics.
- `profiles.schema.media` — Profile Schema → Media-owned avatar/cover/file policy reference.
- `profiles.schema.content` — Profile Schema → profile content/layout composition owned by Profiles.
- `profiles.schema.privacy` — Profile Schema → field/profile privacy policy editor.
- `profiles.schema.editability` — Profile Schema → self/admin editability policy with effective-state preview.
- `profiles.public.slug` — Public Profile → canonical user-profile slug/route configuration with collision validation.
- `profiles.public.visibility` — Public Profile → visibility policy; hidden presentation never grants/revokes authorization.
- `profiles.public.template` — Public Profile → registered profile template/layout selection.
- `profiles.registration.policy` — Registration → registration availability/eligibility policy.
- `profiles.registration.role` — Registration → Roles/Policy-owned default-role reference; Profiles cannot mint or grant capabilities.
- `profiles.registration.verification` — Registration → verification/activation state policy and recovery messaging.
- `profiles.access.login-reset` — Access & Recovery → native login/password-reset integration and safe high-impact recovery states.
- `profiles.directory.source` — Directory → Query/data-source reference with canonical filtering ownership.
- `profiles.directory.controls` — Directory → search/sort/pagination/presentation controls.
- `profiles.directory.privacy` — Directory → privacy-aware inclusion/exclusion policy with effective preview.
- `profiles.portability.definition` — Portability → secret-free profile-definition import/export and reference remapping.
- `profiles.privacy.export-erasure` — Privacy → WordPress privacy export/erasure integration shown as privileged owner workflow, not local destructive execution.

## Interaction and persistence

Admin definition edits use draft → validate → save revision. Member-facing edits distinguish self-service from privileged admin scope and validate through canonical Fields/Media owners before persistence. Registration never assumes a role grant succeeded from client state. Failed saves preserve values and return focus to the first invalid control.

## Loading, empty, validation, conflict and recovery

Required states include first-run empty schema, loading profile, missing Fields/Media owner, route collision, invalid registration role reference, verification pending/failed, password recovery pending, directory empty, privacy-filtered empty, stale revision conflict, saved and recovery. Provider failures remain local to the affected capability.

## Security and ownership

Roles/Policy owns role and capability grants. Fields owns field schema/value validation. Media owns protected media. Query/data owners retain directory/query semantics. Profiles owns profile composition and lifecycle policy. Public visibility is presentation/privacy behavior and never substitutes for authorization. Self-service requests cannot elevate to admin scope from client payload.

## Accessibility

Profile/register/edit/directory forms require programmatic labels, instructions, error associations, visible focus and keyboard access. Validation summaries link to fields. Media controls expose textual status. Verification and privacy status use live regions without relying on color alone.

## Multisite and scope

Site/network user context, role scope and profile definition scope are explicit. Network/super-admin context is server-derived. Site-scoped imports cannot silently alter network-wide profile behavior or roles.

## Portability and reference remapping

Exports contain profile definitions and safe references, not credentials or private user data by default. Imports validate Fields/Media/Role/Query references and report unresolved mappings before save. Privacy export/erasure runtime records are not imported as authored configuration.

## Performance and scale

Directory UI uses paginated/bounded queries and avoids N+1 field/media lookups. Profile admin assets remain scoped. Large schemas may group/lazy-render controls without changing validation semantics. Privacy checks remain server-side even when cached presentation is used.

## Degraded/provider states

Missing Fields, Media, Roles, Query or privacy-provider capabilities show owner-specific degraded states and block unsafe mutation. The surface never fabricates a role grant, verification result, media authorization or privacy completion.

## UX lifecycle exit criteria

Certification requires complete 17-ID mapping, zero missing/unclassified machine semantics, reviewed self/admin boundaries, owner references, privacy/accessibility/portability/performance states and exact-head validator CI. Shared lifecycle promotion remains Supervisor-only.

## Non-certifications

Runtime implementation is not certified by this document. Product parity is not certified. Production deployment or release is not verified. No role/capability grant, user mutation, privacy erasure or provider execution is authorized by this UX contract.
