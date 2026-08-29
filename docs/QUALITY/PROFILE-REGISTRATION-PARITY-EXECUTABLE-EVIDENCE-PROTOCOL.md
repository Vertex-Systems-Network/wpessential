# WPEssential — Profile & Registration Parity Executable Evidence Protocol

Status: **Planning-only exact executable-evidence specification / NOT EXECUTED**  
Namespace: `PBX-001…PBX-176`  
Documented: **176/176**  
Executed: **0/176**

## Truth boundaries

- User Profile presentation/editing ≠ authentication/authorization; WordPress account/session/password authority remains canonical.
- Registration Flow ≠ verified email ≠ admin approval ≠ Membership Enrollment ≠ Entitlement.
- Role assignment is an explicit side effect after validated flow, not canonical membership truth.
- Public profile visibility is presentation/privacy policy; it never grants access to protected account data.
- 2FA/passkey/OAuth are provider/account-security adapters; WPE does not invent a second credential/session stack.
- Woo profile mapping uses Woo-supported APIs and never turns profile fields into order/payment truth.
- Global WordPress user identity and site-scoped profile/membership data remain distinct in Multisite.

## Exact fixtures

### Group 1 — profile identity
- `PBX-001` Create profile composition with stable key, target user scope, field schema, view/edit mode and revision.
- `PBX-002` Reject profile whose referenced field/group/provider schema is unknown.
- `PBX-003` Same profile key may exist independently on isolated sites without cache/config collision.
- `PBX-004` Profile update requires expected revision and preserves prior published revision.
- `PBX-005` Archived profile no longer resolves as active route/component but remains historical.
- `PBX-006` Profile identity is distinct from WordPress user identity and cannot be supplied to impersonate a user.
- `PBX-007` Current-user profile target is server-resolved; request `user_id` is never authority.
- `PBX-008` Admin-editing-other-user mode requires separate capability/Policy and explicit target resolution.
- `PBX-009` Export contains schema/composition but not passwords/session tokens/provider secrets.
- `PBX-010` AI/MCP may draft profile definition but cannot publish broad data visibility or edit user data outside Policy.
- `PBX-011` Unknown profile schema version fails typed or migrates explicitly.

### Group 2 — multiple compositions
- `PBX-012` Multiple view compositions can target different roles/segments while resolving one deterministic composition per context.
- `PBX-013` Multiple edit compositions cannot expose fields outside caller/subject Policy merely because another composition contains them.
- `PBX-014` Composition precedence by explicit assignment/condition is deterministic and explainable.
- `PBX-015` No matching composition falls back to declared default, not arbitrary first-created profile.
- `PBX-016` Conflicting equally ranked compositions are blocked or require explicit precedence resolution.
- `PBX-017` Role/segment change invalidates affected composition cache without changing underlying profile data.
- `PBX-018` Preview of another composition uses redacted sample/authorized subject and does not grant live access.
- `PBX-019` Public and private compositions for same user keep field-visibility policies distinct.
- `PBX-020` Import remaps referenced fields/routes/segments and leaves unresolved dependencies explicit.
- `PBX-021` Multisite composition assignment remains site-scoped unless network template policy says otherwise.
- `PBX-022` AI may recommend composition assignment but cannot auto-publish user/role-wide reassignment without approval.

### Group 3 — multi-step edit
- `PBX-023` Multi-step profile edit defines ordered steps with declared fields, validation and completion semantics.
- `PBX-024` Step progression does not persist invalid field values when validation fails.
- `PBX-025` Save-as-draft vs atomic-final-save semantics are explicit per flow.
- `PBX-026` Returning to prior step preserves allowed draft state without bypassing validation on final submit.
- `PBX-027` Hidden/conditional field state is re-evaluated server-side at submit; client visibility is not trust.
- `PBX-028` Stale profile/user revision on final submit returns conflict rather than overwriting newer edits.
- `PBX-029` Sensitive-step reauth requirement is enforced before applying protected changes.
- `PBX-030` Upload/media step uses Media owner/validation and does not trust client attachment ID.
- `PBX-031` Partial provider/account-security step failure is represented explicitly and does not claim whole profile update success.
- `PBX-032` Accessibility supports keyboard/focus/error summary across steps.
- `PBX-033` AI/MCP cannot skip required steps/reauth/approval by calling final mutation ability directly.

### Group 4 — repeater/group/layout
- `PBX-034` Repeater field stores typed bounded rows through shared Fields owner and preserves stable row identity where needed.
- `PBX-035` Repeater row count obeys configured min/max and oversized payload is rejected.
- `PBX-036` Group field nested validation/sanitization applies per child schema and bounded depth.
- `PBX-037` Layout columns/order affect presentation only and never change field authorization.
- `PBX-038` Drag/reorder repeater rows is version-aware and does not duplicate/drop rows silently.
- `PBX-039` Hidden group children are not accepted from forged client payload when conditions deny them.
- `PBX-040` Private child field is excluded from public profile even if parent group is visible.
- `PBX-041` Import/export preserves group/repeater schema and row metadata types.
- `PBX-042` Deleting repeater row follows field retention/audit policy and does not delete referenced source object automatically.
- `PBX-043` Large repeater uses bounded payload/render limits and avoids unbounded nested processing.
- `PBX-044` AI-generated repeater/group definitions remain schema-valid and cannot introduce arbitrary executable callbacks.

### Group 5 — field approval
- `PBX-045` Field/group can require approval before proposed user change becomes canonical profile value.
- `PBX-046` Pending proposed value is stored separately from current approved value and never silently replaces it.
- `PBX-047` Approver capability/Policy is distinct from ordinary profile-edit permission.
- `PBX-048` Approve action pins expected pending revision and fails on newer proposal conflict.
- `PBX-049` Reject requires configured reason/audit semantics and preserves current approved value.
- `PBX-050` User cancel/replace proposal follows explicit pending-state lifecycle.
- `PBX-051` Public profile continues showing approved value while sensitive proposal is pending.
- `PBX-052` Notification/email about proposal redacts protected field values according policy.
- `PBX-053` Bulk approval cannot approve fields/users outside approver scope.
- `PBX-054` Approval of profile field does not grant membership/role/entitlement unless an explicit separate governed workflow follows.
- `PBX-055` AI/MCP cannot approve/reject sensitive profile changes by default; it may summarize pending changes only under Policy.

### Group 6 — public permalink/privacy
- `PBX-056` Public profile permalink uses explicit slug/identity strategy and does not expose internal user ID unnecessarily.
- `PBX-057` Private/no-public-profile setting returns configured concealment/not-found behavior without leaking account existence.
- `PBX-058` Public field allowlist is enforced server-side; edit/profile schema presence does not imply public visibility.
- `PBX-059` Sensitive account fields/email/phone are hidden unless explicit subject/privacy policy permits publication.
- `PBX-060` Slug collision resolves deterministically without exposing another user's profile data.
- `PBX-061` Username/email change does not silently break permalink when stable profile slug strategy is selected.
- `PBX-062` Search engine index/noindex controls are presentation/privacy hints, not access control.
- `PBX-063` Deleted/suspended user profile follows explicit route lifecycle and cache invalidation.
- `PBX-064` Full-page/CDN cache cannot serve one user's private profile fields to another context.
- `PBX-065` Privacy export/erase includes public-profile metadata according subject ownership and retention.
- `PBX-066` AI/MCP cannot turn private profile public or infer hidden field values from profile route.

### Group 7 — directory/search
- `PBX-067` Directory preset composes User Query + Profile presentation with explicit public-profile eligibility filter.
- `PBX-068` Search by allowed public fields does not query/display private fields by default.
- `PBX-069` Role/Plan/Entitlement filters are presentation/query filters and never authorization shortcuts.
- `PBX-070` Directory counts/facets do not leak existence of excluded/private users beyond policy.
- `PBX-071` Sorting/pagination is deterministic and stable for unchanged query revision.
- `PBX-072` No-result/empty state does not disclose hidden matches.
- `PBX-073` Avatar/display name fields use authorized presentation data and safe escaping.
- `PBX-074` Search query is bounded/rate-limited and cannot become arbitrary user-meta enumeration.
- `PBX-075` Network/user directory remains site-scoped unless explicit network directory policy exists.
- `PBX-076` Directory cache keys include site/audience/query/privacy revision and cannot bleed segments.
- `PBX-077` AI/MCP directory query sees only same authorized result set as caller.

### Group 8 — avatar/media
- `PBX-078` Avatar/profile-media upload uses Media owner, validates MIME/size/dimensions and resolves ownership server-side.
- `PBX-079` User cannot attach another user's private media by supplying attachment ID.
- `PBX-080` Existing Gravatar/external avatar provider is distinct from uploaded media ownership.
- `PBX-081` Crop/derivative operation preserves source immutability and records media revision/provenance.
- `PBX-082` Removing avatar clears profile reference according policy without deleting shared media blindly.
- `PBX-083` Public profile renders only delivery URL permitted by media privacy policy.
- `PBX-084` Private attachment URL is not exposed via directory thumbnail/cache/preload.
- `PBX-085` Media replacement follows MRL/Media owner semantics rather than direct file overwrite from profile module.
- `PBX-086` Export/import uses portable media refs/mappings, never source attachment numeric ID assumption.
- `PBX-087` Multisite avatar ownership/remap is site-aware despite network-global user identity.
- `PBX-088` AI/MCP cannot upload/replace/profile-publish media without same Media/Profile permissions.

### Group 9 — import/export
- `PBX-089` Profile-data export schema declares subject, fields, values, privacy class and source version.
- `PBX-090` Export excludes password hashes/session tokens/2FA secrets/OAuth tokens.
- `PBX-091` Import dry run maps fields/users/media/routes before mutation and reports unresolved mappings.
- `PBX-092` User matching requires explicit stable identity strategy; email/username collision is not guessed silently.
- `PBX-093` Import of field outside target schema is reject/ignore/capture according explicit policy.
- `PBX-094` Merge vs replace semantics are explicit per field/group and preserve unmentioned values under merge.
- `PBX-095` Import cannot set protected/admin-only field without target Policy approval.
- `PBX-096` Repeated package import is idempotent under selected operation identity/conflict policy.
- `PBX-097` Cross-site import remaps site-scoped profile data while respecting network-global user identity.
- `PBX-098` Corrupt/schema-mismatched package fails before mutation.
- `PBX-099` AI may draft mapping but cannot execute bulk profile import/replace automatically.

### Group 10 — account navigation/Policy
- `PBX-100` Account navigation definition adds/reorders presentation routes without granting destination authorization.
- `PBX-101` Hidden route remains server-protected by owning route/Policy if direct URL is requested.
- `PBX-102` Route visibility conditions use logged-in/role/Plan/Policy facts only as declared presentation conditions.
- `PBX-103` Custom account route component receives only authorized subject/context data.
- `PBX-104` WordPress wp-admin hide/restrict presentation composes Admin Menu/Policy owners rather than CSS-only security.
- `PBX-105` User unable to access wp-admin retains required recovery/profile/logout flows according policy.
- `PBX-106` Navigation cache varies by site/user/audience and cannot leak hidden route labels/data.
- `PBX-107` External/account links validate scheme/origin and avoid open redirect.
- `PBX-108` Route deletion/archive checks dependencies and does not delete user profile data automatically.
- `PBX-109` Multisite account navigation is site-scoped unless network template explicit.
- `PBX-110` AI/MCP cannot convert navigation visibility into capability grant or hidden route access.

### Group 11 — registration/Membership
- `PBX-111` Registration Flow can target intended role/Plan but account, role assignment and Enrollment remain separate transitions.
- `PBX-112` Email confirmation state is required only when configured and verified token does not automatically grant Membership unless flow qualifies it.
- `PBX-113` Admin approval state is explicit and account/enrollment side effects occur only after approved transition as configured.
- `PBX-114` Role assignment happens only after validated qualifying flow and does not become canonical membership truth.
- `PBX-115` Membership Enrollment is created only through Membership owner and does not infer paid/provider status.
- `PBX-116` CAPTCHA/spam/rate provider failure is separate from identity validation and produces bounded retry/error state.
- `PBX-117` Password/reset fields use native auth interfaces and are excluded from generic profile/form analytics/logs.
- `PBX-118` Registration redirect matrix validates safe internal/external targets and cannot become open redirect.
- `PBX-119` Existing user registration path follows explicit enroll/update/login policy and cannot create duplicate account silently.
- `PBX-120` Registration content/Woo/file restrictions delegate to Policy/protected-file/Woo owners; UI hiding is not access control.
- `PBX-121` AI/MCP cannot approve registrations, assign privileged roles or grant Enrollment/Entitlement outside normal workflow.

### Group 12 — reauth/2FA/passkey/OAuth
- `PBX-122` Sensitive profile change can require recent reauthentication through canonical WordPress/account-security owner.
- `PBX-123` Reauth result is scoped/time-bounded and cannot be reused as general admin authorization.
- `PBX-124` TOTP/2FA enrollment delegates to provider/adapter and WPE profile never stores raw secret in generic field/log.
- `PBX-125` Recovery codes remain provider/account-security secrets and are not included in profile export.
- `PBX-126` Passkey/WebAuthn adapter stores provider-defined credential metadata under account-security owner, not arbitrary profile field.
- `PBX-127` OAuth/social account linking requires authenticated subject and state/PKCE/provider verification as adapter defines.
- `PBX-128` OAuth email match alone does not silently link account when provider/link policy requires stronger verification.
- `PBX-129` Revoking linked provider account does not delete WordPress user unless explicit account lifecycle says so.
- `PBX-130` Provider timeout/link unknown state reconciles before duplicate link/create attempt.
- `PBX-131` Multisite global user account-security state is distinguished from site-scoped profile data.
- `PBX-132` AI/MCP cannot enroll/reveal/revoke 2FA/passkeys/OAuth secrets outside account-security approval.

### Group 13 — Woo mappings
- `PBX-133` Woo billing/shipping profile mappings use supported Woo customer APIs/Data Stores.
- `PBX-134` Profile field update to mapped customer field respects Woo validation/normalization and does not write private tables directly.
- `PBX-135` Checkout field synchronization direction/authority is explicit and checkout/order snapshot remains Woo truth.
- `PBX-136` My Account edit profile composition preserves Woo authentication/session/account ownership.
- `PBX-137` Store-access role/Plan rule composes Policy and does not rely on hiding Woo menu/products visually.
- `PBX-138` Billing/shipping fields marked private are excluded from public profile/directory.
- `PBX-139` Profile update does not mutate existing order historical billing/shipping snapshots unless Woo owner explicitly supports separate operation.
- `PBX-140` Woo customer ID is resolved from authenticated user/adapter, not trusted client payload.
- `PBX-141` HPOS compatibility uses supported Woo interfaces and no direct order-table assumptions.
- `PBX-142` Woo absent/disabled leaves profile core usable and mapped components degraded rather than fatal.
- `PBX-143` AI/MCP cannot create orders/payments/checkout mutations through profile mapping.

### Group 14 — Policy/REST/AI
- `PBX-144` Profile read/write REST endpoints enforce subject/field/route Policy server-side.
- `PBX-145` Field visibility in UI does not substitute for REST mutation authorization.
- `PBX-146` REST `user_id` cannot select another account without explicit edit-other-user authority.
- `PBX-147` Batch profile update validates each field and follows explicit atomic/per-field failure policy.
- `PBX-148` ETag/user/profile revision prevents stale overwrite.
- `PBX-149` Rate limit/abuse controls apply to public directory/registration/profile mutation separately.
- `PBX-150` Error responses avoid account enumeration and hidden-field disclosure.
- `PBX-151` AI/MCP principal sees only fields/actions caller could see/do and receives no hidden bypass flag.
- `PBX-152` AI-drafted profile change remains proposal/draft where approval/reauth is required.
- `PBX-153` Audit records actor/subject/field IDs/revision but redacts secrets/protected values as required.
- `PBX-154` Prompt injection in profile content cannot grant AI tool permission or expose other user data.

### Group 15 — Multisite/global-user
- `PBX-155` WordPress user identity may be network-global while site profile schema/values/visibility remain site-scoped as configured.
- `PBX-156` Same user can have different site profile compositions/fields without one site overwriting another.
- `PBX-157` Site role/Membership mapping is not inferred from another site's role/Enrollment.
- `PBX-158` Network user admin authority is separate from ordinary site profile edit authority.
- `PBX-159` Public profile permalink uniqueness/route is site-aware.
- `PBX-160` Network template can instantiate profile definitions but does not copy live user profile values by default.
- `PBX-161` Site clone remaps site-scoped profile/media/routes while preserving/quarantining global account-security state appropriately.
- `PBX-162` Site deletion removes/retains site-scoped profile data per privacy policy without deleting global user automatically.
- `PBX-163` Network directory/aggregate profile requires explicit network Policy and redaction.
- `PBX-164` Cache keys include site + subject + composition/audience revision.
- `PBX-165` AI/MCP site principal cannot edit global/network account-security or another site's profile through raw user ID.

### Group 16 — golden profile/security
- `PBX-166` Golden multi-role composition scenario resolves deterministic view/edit profile without exposing unauthorized fields.
- `PBX-167` Golden multi-step sensitive-edit scenario requires reauth and rejects stale revision.
- `PBX-168` Golden field-approval scenario keeps approved value live while pending proposal awaits authorized approver.
- `PBX-169` Golden public profile/directory scenario exposes only allowlisted fields and conceals private user/account existence appropriately.
- `PBX-170` Golden avatar/media scenario enforces ownership/private delivery and delegates replacement lifecycle.
- `PBX-171` Golden registration scenario separates account creation, email verification, admin approval, role assignment and Membership Enrollment transitions.
- `PBX-172` Golden 2FA/OAuth scenario keeps provider secrets out of generic profile/export and reconciles unknown provider outcome.
- `PBX-173` Golden Woo mapping scenario syncs supported customer fields without mutating order/payment truth.
- `PBX-174` Golden Multisite scenario proves network-global user vs site-scoped profile/role/Membership separation.
- `PBX-175` Golden import scenario maps users/fields/media explicitly and rejects protected/unresolved mutations.
- `PBX-176` Golden adversarial AI/MCP scenario cannot approve registration, elevate role, reveal secrets, edit another user or bypass reauth/Policy.

## Execution gate

This document specifies evidence only. **PBX executed remains 0/176.** No registration, user/profile mutation, role/Membership assignment, provider security flow, Woo mutation, test, benchmark or AI/MCP execution is authorized by this protocol.