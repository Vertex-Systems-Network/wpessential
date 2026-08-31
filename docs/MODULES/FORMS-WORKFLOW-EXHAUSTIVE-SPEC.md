# WPEssential — Forms & Workflow Builder Exhaustive Option Specification

Status: **Phase 0 exhaustive product specification / no implementation authorized**  
Applies shared Field Schema, Query, Condition, Ability, Event, Job, Policy, Secrets and Integration contracts.

## 1. Module screens

Primary navigation:
- Forms
- Entries
- Workflows
- Runs
- Settings
- Integrations/Connections shortcut
- Diagnostics

The Forms and Workflows concepts share actions/runtime but remain separate definition types.

---

# 2. Forms list screen

## Columns
- checkbox
- Form name
- stable key/slug
- status: draft/published/archived/disabled
- form type: standard/multi-step/application/CRUD/profile/etc. label only
- entry storage: on/off + retained count summary
- submissions last 24h/period optional statistic
- workflow count
- embed/use count
- updated at
- updated by
- version/revision
- health badge
- actions menu

## Search
- name
- key
- description/tag

## Filters
- status
- creator/owner where policy allows
- has workflow
- entry storage enabled/disabled
- embed context
- updated date
- needs attention

## Sort
- name
- updated
- created
- submission count where efficient

## Row actions
- Edit
- Preview
- Duplicate
- Publish/Unpublish
- Enable/Disable submissions
- View Entries
- View Workflows
- Usage / Used by
- Revisions
- Export
- Archive
- Delete

Delete requires dependency preview and entry-retention decision if definition owns entries.

## Bulk actions
- publish
- unpublish/disable
- archive
- export
- tag/category if taxonomy-like organization exists
- delete only with dependency/entry impact flow

No bulk destructive action bypasses per-form permission.

---

# 3. Create Form wizard

Steps:
1. Identity
2. Structure
3. Data/Entry behavior
4. Submission actions
5. Access/security
6. Confirmation
7. Review/Publish

## Identity fields
- Name — required, human label
- Key — auto-generated stable slug; advanced editable before first publish, later rename requires dependency impact
- Description — optional internal
- Tags/category — optional organization
- Status — Draft default
- Template — Blank / Contact / Registration / Post CRUD / Custom table CRUD / Multi-step / Membership enrollment interest / imported template

Template is a starting definition, not runtime dependency unless explicitly linked.

---

# 4. Form canvas/layout

## Layout units
- section
- row
- column
- field
- group
- step/page
- repeater container
- content/message block
- divider/spacer only when semantic spacing cannot be achieved by layout settings

## Canvas controls
- drag/drop reorder
- keyboard move up/down/into/out of group
- duplicate
- delete
- disable field
- collapse/expand group
- copy/paste field definition where safe
- multi-select optional later; not required v1
- outline/tree view for complex forms
- search fields palette

## Row/column options
- columns count/ratio
- responsive stacking behavior
- gap token
- alignment
- label layout inherited/override
- custom class only if approved safe developer option; never raw CSS execution

---

# 5. Shared field options

Every input-capable field supports applicable subset:

## Identity
- field label
- field key/name
- description/instructions
- placeholder
- admin label

## Value
- default static value
- dynamic default source
- prefill from query/current user/current entity/request allowlist
- read-only
- hidden
- disabled UI state; disabled input is not security boundary

## Requirement
- required toggle
- nullable/empty behavior
- conditional required rule

## Validation
- type-specific validation
- min/max
- min/max length
- regex/pattern where safe
- allowed/disallowed values
- custom registered validator
- cross-field validation through Condition/validator registry
- validation message

## Formatting
- input mask adapter only if accessibility-safe
- prefix/suffix
- help text position
- width/layout

## Conditional visibility
- show/hide when condition true
- AND/OR groups
- conditions based on previous safe form values, user/context/query data
- server-side reevaluation mandatory on submit

## Privacy
- data classification P0-P4
- include/exclude from stored entry
- include/exclude from notifications/export
- redact/mask in admin list/logs
- retention override where allowed

## Access
- visible to guest/authenticated/roles/capabilities/policy
- editable by same rule
- server-side authorization mandatory

---

# 6. Field palette

Uses Custom Fields shared types plus form-only fields.

## Text family
- text
- textarea
- email
- URL
- tel
- number
- range
- password input for workflows that genuinely need new password; never expose stored passwords

## Choice
- select
- multi-select
- radio
- checkbox group
- single checkbox/consent
- button group
- true/false

Choice source options:
- manual choices
- Query Builder result
- taxonomy terms
- posts/entities
- users only when policy allows
- relation choices
- registered provider

Choice mapping:
- stored value
- display label
- optional disabled choice
- order

## Date/time
- date
- time
- datetime
- timezone display policy
- min/max date
- relative min/max expressions from allowlisted date engine

## Media/files
- file upload
- image upload
- gallery/multiple file

Options:
- max files
- max each/total size
- MIME/extension allowlist
- image dimensions min/max
- storage destination adapter
- private/public storage policy
- virus/scanner adapter status if configured
- filename strategy
- overwrite behavior: never blindly overwrite by user filename

## Content/reference
- rich text
- hidden
- computed
- post/entity selector
- taxonomy/term
- user selector
- relation selector
- map/location adapter
- signature field only if a genuine non-legal-signature drawing use case; do not market as legal e-signature without separate compliance product

## Complex
- group
- repeater
- flexible layout

Options:
- min/max rows
- initial rows
- add/remove labels
- reorder
- nested complexity limit
- server-side max input count guard

## Form-only controls
- submit button
- next/previous buttons
- page/step break
- HTML-safe message/content
- CAPTCHA adapter
- honeypot hidden trap
- consent/privacy acknowledgement
- terms acknowledgement

---

# 7. Multi-step options

- Enable multi-step
- step title
- step key
- optional description
- progress UI: none / text / numbered / progress bar
- progress position
- allow previous
- validate current step before next — default yes
- persist step locally/session/server according to save-draft mode
- conditional step skip
- URL/history integration candidate only if accessibility/back-button behavior proven
- step analytics opt-in only if telemetry/privacy rules permit

Final submission is one server-authorized transaction/workflow trigger; reaching final step client-side is not proof earlier values are valid.

---

# 8. Form presentation

## Labels
- top/left/hidden visually with accessible name preserved
- required indicator text/symbol

## Validation presentation
- inline message
- summary at top
- focus first invalid field
- aria-describedby/error semantics

## Button options
- label
- icon optional
- alignment
- full width responsive
- loading label
- disabled state

Styling uses WPE component/theme tokens or builder adapter style controls; no required custom CSS.

---

# 9. Submission availability

## Status
- accept submissions toggle
- maintenance/closed reason

## Authentication
- guests + authenticated
- authenticated only
- guests only rare use; explicit
- selected roles/capabilities
- resource policy

## Schedule
- always open
- start datetime
- end datetime
- timezone
- outside-window behavior/message

## Limits
- total entry limit
- per-user limit
- per-email/value limit where privacy/security justified
- per-IP limit only with proxy/privacy rules
- rolling period vs lifetime
- limit reached behavior

Concurrency-safe enforcement required; UI counter alone is insufficient.

---

# 10. Anti-spam / abuse

Options:
- honeypot — default candidate on for public forms
- minimum fill-time heuristic
- maximum submission rate
- IP/user/session rate scope
- duplicate payload fingerprint window where privacy-safe
- CAPTCHA adapter: Turnstile/reCAPTCHA/hCaptcha/etc. via Connections after provider certification
- Akismet adapter where applicable
- custom registered spam classifier

Actions on suspected spam:
- reject
- mark spam + store
- accept but suppress selected workflows only if explicitly configured

Never execute expensive downstream actions before spam/authorization validation when avoidable.

---

# 11. CSRF / replay / idempotency

Authenticated state-changing forms require WordPress nonce/CSRF semantics appropriate to frontend context.

Public form submissions use:
- submission intent/session token as appropriate;
- idempotency key for actions vulnerable to duplicate retry;
- replay/duplicate controls;
- server-side validation independent from hidden fields/client JS.

Idempotency scope and retention documented per form/action.

---

# 12. Entry storage

## Store entry
- Yes — default candidate for general forms unless privacy template says no
- No — process transiently only

## Entry status
- received
- processing
- completed
- failed_action
- spam
- archived
- deleted/erased according to retention semantics

## Retention
- indefinite
- N days/months/years
- delete/anonymize after terminal time
- separate file retention
- spam retention

## Metadata toggles
- user ID when authenticated
- created timestamp always
- IP address off by default unless abuse/security purpose
- user agent off by default
- referrer/landing page opt-in
- source route/form embed ID
- correlation ID

Privacy class displayed for each.

---

# 13. Save draft / resume

Options:
- off
- authenticated account draft
- guest magic-link/token draft

Fields:
- draft expiry
- autosave interval candidate; server cost warning
- save button label
- resume email action
- whether files are retained with draft
- delete abandoned drafts after retention

Guest resume token:
- high entropy
- hash at rest
- single-purpose
- expiry
- rate-limit lookup
- never guessable sequential ID

---

# 14. Data binding / CRUD mode

Form target mode:
- create entity
- update entity
- create-or-update by explicit match key
- delete entity only as separate high-risk action/form
- no data persistence + workflow-only

Supported target adapters:
- posts/CPT
- users
- terms
- custom tables
- settings/options only with explicit capability/policy
- Membership operations through registered Membership Abilities, not direct table writes

## Field mapping row
- form field
- target field/property/meta
- transformation from allowlisted mapper
- required target type
- empty-value behavior: ignore / clear / set null where supported
- create-only/update-only/both

## Entity selection for update
- current entity context
- URL/request ID only after policy validation
- Query result
- relation context
- explicit server-bound hidden signed context

Never trust user-modifiable hidden ID as authorization.

---

# 15. User registration/update

Options:
- create user action
- username source/generation
- email source
- display name fields
- initial role — allowlisted low-privilege roles only
- password: user-supplied or secure generated/reset-link flow
- send WordPress notification
- require email verification adapter/workflow if configured
- duplicate email/user handling
- existing-user update behavior

Creating administrator-equivalent user/role from public form is blocked by default and needs exceptional high-risk policy if ever allowed.

---

# 16. Delete actions

Delete is never just a normal CRUD checkbox.

Options:
- trash vs permanent delete where source supports
- confirmation input
- current-user ownership requirement
- capability/policy
- dependency impact
- relation cascade/detach policy
- file cleanup policy
- workflow after deletion

Permanent delete may require re-auth for sensitive admin forms.

---

# 17. Confirmation behavior

After successful primary submission:
- inline success message
- render WPE template/listing
- redirect to validated local URL
- redirect to trusted allowlisted external URL only if explicitly enabled
- show created/updated entity link when permitted
- custom success page

Failure confirmation separate from validation errors.

Do not expose sensitive entity IDs/data to guest without policy.

---

# 18. Submission action pipeline

Form can invoke registered actions after primary validation.

Action order is explicit.

Each action defines:
- action type/Ability
- condition
- synchronous/async
- required/optional
- timeout
- retry policy
- idempotency
- failure path
- compensation option where supported

Primary data commit semantics must be clear: e.g. “email failure does not undo created post” unless workflow transaction explicitly owns both and compensation exists.

---

# 19. Workflow list screen

Columns:
- checkbox
- name
- key
- status
- trigger
- linked form/entity/event
- active runs
- last run
- last result
- updated
- health
- actions

Filters:
- enabled/disabled
- trigger family
- has failures
- linked module
- updated date

Actions:
- edit
- enable/disable
- run/test where manual trigger allowed
- duplicate
- revisions
- runs
- export
- archive/delete

---

# 20. Workflow editor canvas

Node types:
- Trigger
- Condition/branch
- Action/Ability
- Delay/wait
- Manual approval
- Parallel/fan-out — only after semantics/cost limits
- Join — only if parallel supported
- End/success/failure
- Sub-workflow call

Canvas features:
- zoom/pan
- keyboard accessible outline/list editor alternative
- drag connection
- node duplicate/delete
- connection validation
- unreachable-node warning
- cycle detection
- node search/palette
- minimap optional
- validation/errors panel

Arbitrary infinite loops are rejected.

---

# 21. Trigger options

Common triggers:
- form submitted
- form entry status changed
- entity created/updated/deleted
- status transition
- relation attached/detached
- Membership Enrollment event
- scheduled/cron
- inbound webhook
- user login/register/profile change
- WooCommerce adapter event
- manual run
- Ability/event registered by SDK

Trigger fields:
- source
- event type/version
- object/form/plan scope
- filters/conditions
- debounce/dedupe window
- actor/context capture allowlist
- replay behavior

---

# 22. Condition/branch node

Options:
- condition group ANY/ALL/NONE
- left operand source
- operator
- right operand static/dynamic
- null/missing behavior
- case sensitivity where applicable
- date/time timezone
- branch labels
- default/fallback branch

Condition data is typed. No raw PHP/JS expression.

---

# 23. Action node

Action chooser uses Ability/action registry.

Common options:
- input mapping
- condition
- execution mode sync/async
- timeout budget
- retry strategy
- max attempts
- backoff
- idempotency key source
- on failure: stop / branch / continue warning / compensate
- store selected output fields for later nodes
- redact sensitive output

Dangerous abilities show destructive/high-risk badge and may be disallowed from unattended workflows.

---

# 24. HTTP/Webhook action

Uses Connections Manager.

Options:
- connection
- method allowlist
- path/template relative to connection base where possible
- headers mapping excluding raw secret exposure
- query/body mapping
- content type
- timeout
- accepted status codes
- response schema/size limit
- retry policy
- idempotency header
- log request/response metadata redaction

SSRF/private-network rules owned by Connections security layer.

---

# 25. Delay/wait

Modes:
- duration
- until date/time
- until event/callback with timeout

Options:
- timezone for human configured time
- maximum wait
- expiry/failure branch
- whether schedule is recalculated on workflow definition update — default no for already-running instance unless migration chosen

Durable Job Service handles waits; PHP request does not sleep.

---

# 26. Manual approval

Options:
- approver users/roles/capability/query/relation target
- ANY one / ALL selected approvals
- expiry
- reminder schedule
- approve/reject labels
- rejection reason required
- delegate policy
- self-approval allowed off by default when requester can also approve
- action after approve/reject/timeout

Approval links require authenticated authorization or signed single-purpose link when intentionally allowed.

---

# 27. Retry/backoff

Strategies:
- none
- fixed
- exponential
- provider `Retry-After` aware

Fields:
- max attempts
- initial delay
- maximum delay
- retryable error categories/statuses
- jitter candidate

Never retry non-idempotent external charge/create action blindly.

---

# 28. Workflow versioning while runs exist

Published workflow revision is immutable for a started run by default.

Running instance records revision UUID.

New published revision applies to new runs.

Admin migration of in-flight runs to new revision is not baseline and would require explicit mapping/compatibility design.

---

# 29. Workflow concurrency

Per workflow options:
- unlimited within system budget
- max concurrent runs
- per-subject/resource concurrency key
- skip/defer/queue when duplicate active
- singleton mode

Business critical concurrency lock comes from Job/Workflow runtime, not browser UI.

---

# 30. Run history

Run list columns:
- run ID
- workflow/revision
- trigger
- subject/resource
- status
- started/completed
- duration
- current step
- attempts
- correlation
- initiated by/source

Statuses:
- queued
- running
- waiting
- awaiting_approval
- succeeded
- succeeded_with_warnings
- failed
- cancelled
- expired/timed_out

Actions:
- inspect
- retry failed step/run when safe
- cancel where safe
- resume after manual repair
- export diagnostic summary

Do not expose sensitive action payloads by default.

---

# 31. Entry screen

Columns configurable but default:
- entry ID
- form
- status
- submitted at
- user/guest summary
- selected indexed fields only
- workflow status
- spam state

Filters/search based on indexed/configured fields; do not generate unbounded meta scans.

Entry detail:
- field values with privacy masking
- uploaded files with authorization
- action/workflow history
- notes
- audit
- edit entry only if form allows + capability
- rerun selected actions only with idempotency warning
- export
- anonymize/delete according retention

---

# 32. Form notifications shortcut

Form can create/link Notification rules but Notification System owns templates/delivery.

Quick-create fields:
- event: success/failure/spam/etc.
- recipients
- channel/template

Creates real Notification definition, not hidden private form email engine.

---

# 33. Module settings

Candidate settings:
- default entry retention
- default spam policy
- default file limits
- upload private/public default
- max form fields
- max repeater nesting/rows
- max request body guidance from server
- public submission rate defaults
- workflow default retry cap
- workflow run-log retention
- failed-run retention
- default timezone = site timezone for UI, UTC storage
- cleanup schedule
- diagnostics verbosity safe level

Per-form/workflow overrides explicit.

---

# 34. Permissions

Capabilities include at minimum separate:
- forms read/create/update/delete/publish/import/export
- entries read/update/delete/export
- workflows read/create/update/delete/publish/run/retry/cancel
- sensitive entry view/export
- file download
- high-risk action execution

Resource Policies can restrict per definition/entry.

---

# 35. REST / Ability surface

Safe candidate abilities:
- form list/get/create/update/validate/publish/archive/export/import
- entry list/get/export/delete/anonymize under policy
- form submission execution through dedicated controlled endpoint/Ability semantics
- workflow list/get/create/update/validate/publish/run/cancel/retry
- run get/list

AI exposure default:
- list/get/explain/validate/preview only;
- form/workflow creation draft may be opt-in;
- publishing/destructive/run actions not AI-exposed by default.

---

# 36. Events

Emit typed events for:
- form published/updated
- submission received/validated/rejected/spam/completed
- entry archived/deleted/anonymized
- workflow run queued/started/waiting/completed/failed/cancelled
- workflow step completed/failed/retried
- approval requested/approved/rejected/expired

At-least-once consumers must be idempotent.

---

# 37. Empty/loading/error/degraded states

Forms:
- no forms → templates/create CTA
- form schema invalid → cannot publish, show exact validation
- dependency missing → degraded, existing definition preserved
- Pro expired → read-only definition; deployed safe rendering/processing behavior follows license ADR

Entries:
- no entries
- retention removed
- storage disabled explanatory state

Workflow:
- job runner unhealthy
- connection unavailable
- action plugin missing
- invalid revision
- paused by license/dependency

Never show a generic blank canvas when a dependency is the problem.

---

# 38. Performance guardrails

- maximum field/nesting limits configurable within safe bounds
- server input variable/post size diagnostics
- no N+1 dynamic option queries
- cache Query-backed choices with explicit invalidation/freshness
- async long actions
- chunk large exports/cleanup
- paginate entries/runs
- bounded action response storage
- prevent workflow explosion from unbounded fan-out

---

# 39. Assets

Admin Form Builder assets only on Forms editor/list/entry screens as needed.
Frontend form runtime assets only when WPE form is rendered.
Workflow editor assets never frontend.
CAPTCHA/provider scripts load only for forms using provider.
Rich editor/media dependencies load only when relevant field exists.

---

# 40. Accessibility

- labels and instructions associated correctly
- keyboard canvas alternative/outline
- field reordering keyboard support
- focus management after add/delete/move
- validation summary + inline errors
- multi-step focus/announcements
- CAPTCHA accessible fallback/provider requirement
- no color-only state
- progress semantics
- disabled/read-only distinctions

---

# 41. Security acceptance tests later

Required after consent:
- hidden field ID tampering cannot update another user's entity
- CSRF/auth failures
- mass assignment blocked
- role escalation blocked
- malicious upload/MIME polyglot policy
- XSS through labels/entries/templates
- SQL/query option injection
- replay duplicate submission
- concurrency entry limit
- save/resume token theft/expiry
- spam/rate behavior
- webhook SSRF inherited controls
- workflow duplicate/out-of-order event
- non-idempotent retry protection
- approval authorization
- sensitive entry export denial
- retention cleanup
- dependency/license degraded behavior

## Maturity

This module is now **Exhaustive option spec** at Phase 0 product level. Physical storage, renderer/runtime, Job/Workflow engine, provider integrations and executable tests remain blocked by technical ADRs and explicit owner development consent.