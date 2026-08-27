# WPEssential — Notification System Exhaustive Option Specification

Status: **Phase 0 exhaustive product specification / no implementation authorized**

## 1. Concepts

Separate:
- Notification Rule — event/recipient/channel policy definition
- Notification Instance — one logical user/recipient notification
- Delivery Attempt — one channel/provider attempt
- User Preference — recipient channel/category preference
- Digest — batched delivery container

Notifications consume Events/Workflow; they do not reimplement full Workflow branching.

---

# 2. Screens

- Rules
- Create/Edit Rule
- Notification Inbox Preview/Admin
- Deliveries
- Digests
- User Preferences Defaults
- Channels/Connections shortcut
- Settings
- Diagnostics

---

# 3. Rules list

Columns:
- Name
- Key
- Status
- Trigger/event
- Recipient summary
- Channels
- Priority
- batching/digest
- last fired
- last delivery result
- failure rate/health summary
- updated
- actions

Filters:
- enabled/disabled
- trigger family
- channel
- priority
- has failures
- category
- updated date

Actions:
- Edit
- Enable/Disable
- Test/Preview
- Duplicate
- Deliveries
- Usage
- Revisions
- Export
- Archive/Delete

---

# 4. Rule identity

Fields:
- Name required
- Key stable generated
- Internal description
- Category key
- Status Draft/Enabled/Disabled/Archived
- Tags optional

Category examples are user/site-defined or registered:
- system
- account
- content
- workflow
- membership
- commerce adapter
- security
- marketing/optional only when explicitly intended

Category is used for preference/digest policy; it is not automatically a legal classification.

---

# 5. Trigger

Options:
- WPE typed event
- workflow action invokes notification
- manual/admin send through registered Ability
- scheduled digest flush

Fields:
- event type/version
- source module
- optional object/definition scope
- trigger filters
- deduplication key mapping

Notification rule does not subscribe to arbitrary PHP hook by raw string unless a registered safe Event adapter exposes it.

---

# 6. Trigger condition

Optional shared Condition Engine:
- event fields
- current entity properties
- Query result/count
- user/recipient attributes allowed by policy
- Membership/role/resource conditions

Conditions run server-side.

No raw PHP/JS expression.

---

# 7. Recipients

Recipient source types:
- specific users
- event actor
- event subject/owner
- roles
- capabilities
- Query Builder users
- relation-derived users
- Membership Plan/entitlement members
- Team owner/managers/members
- custom registered recipient provider
- explicit external email/phone endpoint only for channels designed for non-user recipients and strict validation

## Recipient options
- include actor yes/no
- exclude selected users/roles
- deduplicate recipients
- max recipients/run guard
- resolve recipients at event time — default
- resolve at delivery time only for explicit use case

Large recipient sets become batched Job Service work, never one request loop.

---

# 8. Recipient eligibility

Per recipient evaluate:
- account/user exists where required
- channel address available
- channel verified requirement
- resource authorization for notification content/action
- user preference/category
- quiet hours
- mute/block/chat-specific states where relevant
- rate/frequency cap

A notification must not reveal a protected resource to a recipient who cannot access it at delivery/render time when resource state is security-sensitive.

---

# 9. Priority

Values:
- low
- normal — default
- high
- critical/system

Priority affects:
- sorting
- digest eligibility
- quiet-hour bypass only when explicitly allowed
- retry/alert policy

Do not let ordinary marketing notification select “critical bypass” without high-level policy.

---

# 10. Preference class

Rule class:
- Required system/security
- Transactional/service
- Optional subscription

Semantics must be documented per site use case. Optional notification honors unsubscribe/preference. Required class is reserved for notifications necessary for system/security/account operation and must not be used to bypass user preferences for promotional content.

---

# 11. Channels

Core candidates:
- admin/in-app
- frontend dashboard/in-app
- email via Email Builder
- webhook/Connection

Adapters later:
- browser push
- Slack
- Microsoft Teams
- SMS
- WhatsApp/business provider where compliant/certified
- other registered channel

Each selected channel has:
- enabled toggle
- template/content variant
- fallback policy
- delay/digest eligibility
- provider connection
- retry settings bounded by adapter

---

# 12. Channel fallback

Modes:
- independent channels — each attempted
- ordered fallback — next only if prior definitive failure and semantics support it
- preferred recipient channel then fallback

Do not treat “email accepted by SMTP/provider” as definitive end-user delivery failure/success unless provider supplies status.

Fallback from async channel must not create duplicates due to delayed delivery-status uncertainty.

---

# 13. In-app content

Fields:
- title
- short body
- optional icon/type
- action label
- action target
- secondary action optional
- image/media only when access-safe
- priority
- expires at/duration
- dismissible
- mark-read-on-open

Action target:
- WPE/admin route
- validated local frontend URL
- protected resource entity route
- trusted external URL allowlist option

Tokens rendered through shared Renderer with escaping appropriate to destination.

---

# 14. Email channel mapping

Options:
- Email Template definition
- subject override
- preheader override
- sender profile/Connection
- reply-to policy
- recipient address source

Email Builder owns email-safe markup. Notification Rule does not paste arbitrary Elementor/browser HTML into email.

---

# 15. Webhook channel

Uses Connections Manager.

Options:
- connection
- endpoint/path
- method supported by adapter
- payload mapping/schema
- headers mapping without secret exposure
- signing/auth from Connection
- idempotency event ID
- timeout/retry

SSRF/security inherited from Connections.

---

# 16. Template tokens

Sources:
- event payload safe fields
- recipient user/profile safe fields
- entity fields under policy
- Query values
- Membership Plan/Enrollment safe display fields
- site name/URL
- date/time
- registered token provider

Sensitive fields are denylisted by schema; generic `user.meta.*` or raw object dump is not allowed.

Preview uses sample/synthetic or explicitly selected authorized entity/user.

---

# 17. Conditional content

Template can include bounded conditional blocks through Renderer/Condition Engine.

Examples:
- show renewal CTA if grace
- different message by role/language
- hide field if empty

No raw executable template code.

---

# 18. Localization

Options:
- site default language template
- per-locale variants
- fallback locale
- recipient locale source

Missing locale falls back predictably; does not fail delivery silently.

RTL is handled by renderer/channel where applicable.

---

# 19. Scheduling/delay

Options:
- immediate
- delay duration
- send at date/time
- recipient-local time candidate where reliable timezone exists

Delayed notification snapshots event identity and resolves mutable content according to explicit policy:
- render at delivery time — default for current state;
- snapshot selected values at trigger time when audit/business use requires.

Job Service executes delay; PHP request does not sleep.

---

# 20. Quiet hours

Global default + user preference where allowed.

Fields:
- enabled
- start local time
- end local time
- timezone source: user profile/site
- days
- priority bypass policy

If timezone unknown, use documented fallback site timezone.

Quiet-hour behavior:
- defer to end of quiet hours — default
- add to digest
- skip only if rule explicitly allows expiration

Critical bypass is policy-controlled.

---

# 21. Digest/batching

Modes:
- none
- hourly
- daily
- weekly
- custom approved interval

Fields:
- recipient-local delivery time
- max items per digest
- overflow behavior/link
- grouping key
- sort order
- duplicate collapse
- empty digest suppress — default yes

Digest stores notification references/safe snapshots, not unbounded full object dumps.

---

# 22. Deduplication

Options:
- none
- event ID
- custom key mapping
- recipient + rule + entity within time window

Fields:
- dedupe window
- on duplicate: suppress / update existing in-app / increment counter where channel supports

Security/access revocation notification should not be incorrectly suppressed by broad dedupe key.

---

# 23. Frequency caps

Optional per category/rule:
- max N per hour/day/week per recipient
- in-app only/email only/channel-specific

On cap:
- suppress
- digest
- defer

Required security/system notifications may use separate policy rather than silently dropped by optional marketing cap.

---

# 24. In-app instance state

States:
- unread
- read
- archived/dismissed
- expired
- revoked/withdrawn where notification content must no longer be shown

Fields:
- created
- first seen
- read at
- dismissed at
- expires at

Read state is per recipient.

---

# 25. Recipient inbox UX

Components:
- unread badge/count
- list
- filters All/Unread/Category
- mark read/unread
- mark all read
- dismiss/archive
- open action
- pagination/load more

Bulk “mark all read” uses bounded server operation, not N browser requests.

Protected action is re-authorized when clicked; notification possession is not authorization.

---

# 26. Delivery statuses

Normalize:
- queued
- attempting
- accepted_by_provider
- delivered_confirmed only when provider reports reliable delivery
- failed_retrying
- failed
- suppressed_preference
- suppressed_dedupe
- suppressed_frequency
- deferred_quiet_hours
- expired
- cancelled

Do not label `accepted_by_provider` as `delivered` by default.

---

# 27. Retry

Adapter defines retryable categories.

Rule options bounded by adapter/global max:
- max attempts
- backoff
- respect Retry-After
- timeout

Idempotency is required for webhook/provider calls where duplicate sends matter.

Permanent invalid destination does not retry indefinitely.

---

# 28. Delivery logs

Columns:
- delivery ID
- notification/rule
- recipient safe summary
- channel
- provider/connection
- status
- attempt
- queued/sent/confirmed timestamps
- error category
- correlation ID

Detail redacts:
- credentials
- full sensitive message bodies by default
- private webhook payload fields
- phone/email beyond policy-safe display masking where appropriate

---

# 29. User preferences

Per user/category/channel controls where policy allows:
- enabled/disabled
- preferred channel
- quiet hours
- digest mode/time
- language
- browser push device subscriptions later

Required/system categories display locked/explanatory preference where user cannot disable for legitimate product operation; site owner must not misuse classification.

Preference change is audited only at appropriate privacy-safe level.

---

# 30. Default preference policy

Admin settings define defaults for new users:
- channel defaults per category
- optional categories opt-in/opt-out policy according to site business/legal requirements
- digest defaults
- quiet hours default

WPE does not make jurisdiction-specific legal compliance claims; site owner remains responsible for lawful messaging configuration.

---

# 31. Escalation

A Notification Rule may delegate escalation to Workflow rather than implement its own complex state machine.

Simple escalation option:
- if delivery failed on required channel → emit failure event
- if unread after N hours → emit `notification.unread_threshold` event

Workflow can then branch/notify manager/etc.

---

# 32. Test send/preview

Test modes:
- render preview with sample context
- send to current admin/test recipient
- dry-run recipient resolution count

Test UI shows:
- resolved tokens
- missing tokens
- channel output
- expected provider connection
- privacy/sensitive fields

Test send clearly marked and does not alter real event state.

---

# 33. Rule versioning

Triggered Notification Instance records Rule revision/snapshot identity.

Delayed delivery policy defines whether message renders using:
- trigger revision snapshot; or
- current template at delivery.

Default candidate:
- event/routing/recipient logic pinned to triggered Rule revision;
- content template can be pinned by default for deterministic audit, with explicit “latest template at send” option if needed.

---

# 34. Rule disable/delete

Disable:
- no new triggers
- queued notifications behavior selected: keep queued / cancel queued; default keep already-created transactional instances unless explicit cancellation

Delete:
- archive preferred when history exists
- dependency/queued instance impact
- delivery history retained per policy

---

# 35. Settings

- default delivery log retention
- in-app notification retention
- read notification retention
- expired cleanup
- max recipient batch
- default retry cap
- default quiet hours optional
- default digest policies
- global rate ceiling
- notification payload size limit
- email/webhook provider timeout defaults
- privacy/log verbosity

Per-adapter safety limits override unsafe global values.

---

# 36. Permissions

Separate:
- manage rules
- publish/enable rules
- send/test
- view delivery logs
- view sensitive recipient details
- manage default preferences
- manage provider connections via Connections capability
- view/admin in-app inbox on behalf of another user — high privacy capability

Users can manage own preferences/read state through self-service policy.

---

# 37. Abilities

Candidate:
- rule list/get/create/update/validate/publish/enable/disable
- preview recipients/content
- notification send registered action
- inbox list/read/mark-read/dismiss for current subject
- delivery list/get/retry when safe
- preference get/update self

AI default:
- rule read/explain/preview
- draft creation opt-in
- real send/bulk recipient mutation disabled by default.

---

# 38. Events

- notification instance created
- delivery queued/attempted/accepted/confirmed/failed/suppressed
- notification read/dismissed/expired
- preference changed
- digest created/sent/failed
- channel unhealthy/recovered deduplicated

Avoid high-volume access/event feedback loops where notification events retrigger same rule recursively; recursion/cycle guards required.

---

# 39. Error/degraded states

- missing provider connection
- recipient missing address
- invalid template/token
- provider outage
- rate limited
- preference suppression
- Job Service unhealthy
- digest backlog
- disabled channel adapter
- Pro expiry → rule editing read-only; existing necessary security/runtime behavior follows ADR-0007

---

# 40. Performance

- batch recipient resolution
- paginate notification/delivery history
- no N+1 profile/query rendering
- prefetch/batch template context
- bounded fan-out per job
- queue large sends
- indexes for recipient unread state
- cleanup old deliveries asynchronously
- no global wp-admin polling when notification UI absent

---

# 41. Assets/accessibility

Assets:
- admin rule/editor assets only notification screens
- frontend inbox assets only when component rendered
- provider SDK only when selected channel needs it

Accessibility:
- live unread count announcements should not be noisy
- keyboard inbox actions
- semantic status labels
- focus after mark/dismiss
- no color-only priority
- toast does not disappear before accessible reading/action opportunity

---

# 42. Future tests

After consent:
- recipient resolution/dedupe
- protected resource reauthorization
- preference/quiet-hours/digest
- same event duplicate delivery
- provider retry/idempotency
- provider accepted vs delivered status
- invalid destination
- large recipient batching
- role/membership target changes between trigger/delivery
- template XSS/escaping
- privacy log redaction
- unread counts concurrency
- user cannot read another user's notification
- required vs optional preference classification
- recursion prevention
- Pro/dependency degraded behavior

## Maturity

Notification System is now **Exhaustive option spec** at Phase 0 product level. Provider adapters, persistence/index design and executable delivery tests remain technical/consent-gated.