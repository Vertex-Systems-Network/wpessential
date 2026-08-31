# WPEssential — Automation & Communication Detailed Specifications

Status: **Phase 0 — specified with job-engine/provider blockers**

Applies `COMMON-OPTION-CONTRACTS.md`. Resolves Forms & Workflow Builder, Cron Job Builder, Notification System, Emails Builder and Message & Chat System.

---

# 17. Forms & Workflow Builder — Pro

## Form definition
- name/key/status;
- description;
- access audience;
- schedule/capacity;
- layout/steps;
- field schema references;
- submission storage;
- workflow bindings.

Default status Draft. Publish requires at least one submit action/outcome and valid field schema.

## Access
Default public/guest is **false until user explicitly chooses**. Modes:
- public guests + authenticated;
- guests only;
- authenticated only;
- policy/role/membership/entitlement condition.

Server validates access on form schema load and submit.

## Field behavior
Reuses Custom Fields schema plus form-only controls. Form does not fork validation rules; it may add presentation and submission-specific constraints.

## Layout
Rows/columns responsive. Drag/drop also supports keyboard move controls.

Each field placement references field instance ID and width tokens. Duplicate placement of same single-value field blocked unless explicitly supported alias/display clone.

## Multi-step
- step key/title;
- optional description;
- progress mode none/steps/bar;
- validate current step before advance;
- previous retains values;
- server still validates entire final submission.

## Conditional visibility
Shared Conditions Engine. Hidden field submission policy defaults **ignore/unset hidden value** unless field explicitly preserves hidden value. UI labels this because stale hidden data can be surprising.

## Calculations
Allowlisted typed expression engine. No eval. Numeric rounding/precision explicit.

## Dynamic defaults/options
Query/token/URL parameter only through declared allowlist and validation. Server recomputes privileged defaults where security matters.

## Save draft / resume
Disabled by default.
When enabled:
- guest resumes via high-entropy token with expiry;
- authenticated resumes owned drafts;
- sensitive fields may be excluded;
- file draft retention defined;
- token hashed/stored safely;
- rate limits.

## Schedule/capacity
- open date/time optional;
- close date/time optional;
- global entry max;
- per-user max;
- per-IP only if justified/privacy-aware;
- capacity checks concurrency-safe.

## Spam
- honeypot default optional/on for public forms after compatibility review;
- rate limit;
- CAPTCHA adapter;
- spam status/quarantine instead of silent delete if operationally useful;
- no fingerprinting by default.

## File uploads
- allowed MIME + extension mapping;
- max size cannot exceed server;
- max count;
- public/private storage policy;
- randomized filenames/WordPress media where appropriate;
- access controlled when private;
- executable/script MIME blocked;
- SVG only through sanitization policy.

## Submission storage
Modes:
- WPE entry record default for ordinary forms;
- no persistent entry only if actions can complete reliably and audit/privacy requirements permit;
- custom Data Source target through action.

Entry retention configurable by form/category. Sensitive field values can be redacted/encrypted where storage model supports.

## Success outcome
- inline success message default;
- redirect internal page;
- validated external redirect advanced;
- Dashboard route;
- return created entity link if authorized.

## CRUD actions
Each action selects Data Source + operation + field mapping.
- create;
- update selected/current entity;
- delete only dangerous action with Level 2 and strict ownership/policy;
- upsert only with explicit unique match key.

Mass assignment impossible because only mapped target fields are sent.

## User create/update
- role assignment requires separate capability;
- cannot create administrator-equivalent account through generic public form;
- password uses secure WordPress flow;
- existing-email/username behavior explicit.

## Workflow definition
Directed graph of registered triggers/nodes. Cycles allowed only through explicit looping construct with bounds; accidental graph cycles rejected.

## Node common options
- name;
- action type;
- input mapping;
- timeout;
- retry max/backoff;
- idempotency key strategy;
- on-success next;
- on-failure next;
- compensation reference where supported.

## Trigger
One workflow can have one or more explicit triggers if semantics are clear. Trigger defines event schema and filtering conditions.

## Delay/wait
- fixed duration;
- until timestamp;
- wait for event with timeout advanced;
- persists job state, not PHP request sleep.

## Manual approval
- approver policy/query;
- due/expiry;
- approve/reject branches;
- comment/reason;
- notifications;
- immutable audit.

## Run logs
- run ID;
- trigger;
- status;
- current node;
- started/finished;
- safe input/output summaries;
- retries/errors;
- replay/retry eligibility.

Secrets redacted.

## Retry
Retry only failed idempotent/safely retryable node. UI warns if action cannot guarantee retry safety.

## Tests
- hidden field tampering;
- duplicate submits/idempotency;
- guest upload abuse;
- concurrent capacity;
- workflow retries;
- approval authorization;
- deleted action dependency;
- user role escalation.

---

# 18. Cron Job Builder — Pro

## Separation
WP-Cron event inspector and WPEssential durable Job Service are related but not the same. Cron UI must never imply traffic-triggered WP-Cron guarantees exact execution time.

## Event inventory
Read fields:
- hook;
- next scheduled UTC/site time;
- recurrence;
- arguments safely summarized;
- owner/source hint;
- overdue duration;
- WPE-owned flag.

Third-party event modifications disabled by default; advanced user can run/delete only after ownership warning and capability.

## WPE schedule identity
- name/key/status;
- action reference;
- schedule type;
- timing;
- arguments;
- overlap/missed-run/retry policy.

## Schedule type
- one-time;
- interval;
- daily/weekly/monthly preset;
- custom recurrence interval;
- cron-expression-like UI only if compiler can represent semantics reliably in selected runner.

## Timezone
User config displayed in site timezone by default; stored canonical time includes timezone/UTC conversion. DST behavior preview required for recurring local-clock schedules.

## Missed-run policy
Options:
- run once ASAP — default;
- skip missed occurrence;
- catch up bounded N occurrences advanced.

Never unbounded catch-up.

## Overlap policy
- prevent overlap default;
- allow overlap only if action declares safe;
- replace/cancel previous not default and requires action semantics.

Lock has timeout/recovery to avoid permanent stuck state.

## Action selection
- Workflow;
- registered Ability/action;
- safe WordPress hook adapter with typed args;
- HTTP/Webhook via Connections.

No arbitrary PHP/JS/HTML execution.

## Run now
Creates audited run through same Job Service, not direct bypass. Shows queued/running result.

## Pause/resume
Pause prevents future WPE schedule dispatch; does not kill already-running job unless action supports cancellation.

## Retry
Schedule-level retry defines max attempts/backoff for dispatch/action according to Job engine. Failed run history retained.

## Runner health
- WP-Cron disabled constant;
- loopback test;
- last dispatch/tick;
- queue backlog;
- overdue jobs;
- external system cron command guidance;
- WP-CLI runner guidance.

No automatic server crontab modification in v1.

## Tests
- DST recurrence;
- overlap lock;
- missed run;
- disabled WP-Cron;
- retry/idempotency;
- third-party delete warning.

---

# 19. Notification System — Pro

## Architecture
Notification = event-derived communication intent + recipients + channel deliveries. Branching business logic belongs in Workflow.

## Rule
- name/key/status;
- event;
- condition;
- recipients;
- channels;
- content/template;
- priority;
- schedule/delay;
- dedupe;
- user preference classification.

## Recipient resolution
All recipients resolved at send time unless rule says snapshot at trigger.
Sources:
- event actor/subject/owner;
- explicit user(s);
- role;
- capability;
- membership/entitlement;
- relation;
- Query;
- external address only if channel policy permits.

Large recipient Query becomes batched job.

## Priority
Informational / Normal / High / Urgent presentation/routing metadata. Priority does not bypass user/legal opt-out unless classification says mandatory transactional/security.

## Classification
- transactional/service;
- security;
- marketing/promotional;
- operational admin.

Default new custom rule = transactional only if event truly qualifies; UI requires explicit classification because preference/unsubscribe laws vary.

## In-app record
- title;
- body;
- icon;
- target URL/action;
- created;
- expiry optional;
- read timestamp;
- dismissed timestamp optional.

Read state per user.

## Digest
Disabled by default. Options:
- hourly minimum scheduling granularity only if Job Service supports;
- daily;
- weekly;
- user preferred time/timezone;
- max items;
- overflow summary link.

Urgent/security notifications bypass digest only when rule explicitly classifies them.

## Quiet hours
User preference optional. Stores local-time window + timezone. Critical notification policy can override with visible explanation.

## Dedupe
- event ID default when event is unique;
- custom key expression;
- window;
- same recipient/channel scope.

## Delivery statuses
Queued / Sent-to-provider / Delivered only if provider confirms / Failed / Suppressed / Cancelled.

Do not label PHP `mail()` handoff as delivered.

## Tests
- user preference;
- membership recipient revoked before send;
- huge role Query batching;
- duplicate event;
- provider failure;
- sensitive token leak.

---

# 20. Emails Builder — Pro

## Rendering principle
Dedicated email-safe renderer. Elementor/WPBakery/browser HTML is not canonical email markup.

## Template definition
- name/key/status;
- event binding optional;
- subject required before Publish;
- preheader optional;
- HTML component tree;
- plaintext template/generated preview;
- sender policy;
- sample context.

## Global brand
- logo;
- email content width default 600px planning default;
- page/background colors;
- safe font stack;
- default text/link/button styles;
- header/footer partials;
- company/legal footer fields where configured.

No remote font dependency assumed because email client support is inconsistent.

## Components
Every component has mobile-safe layout and email compatibility rating.

### Text/heading
Sanitized rich text subset; heading semantic level/style separate.

### Image
- Media/validated remote asset;
- alt required or decorative flag;
- width/max width;
- link optional;
- no base64 giant embeds by default.

### Button
- label required;
- URL/action token;
- alignment;
- padding/style tokens;
- safe URL schemes.

### Columns
Responsive stacking behavior explicit; max column count bounded (e.g. 4 planning cap) because email layout support.

### Data table
Bounded rows/columns; semantic fallbacks; no huge arbitrary Query output.

## Dynamic tokens
Token registry labels sensitivity. Missing token behavior:
- preview warning;
- runtime empty/default according to token;
- required token can fail send with actionable error.

Sensitive/internal tokens not available in template picker.

## Conditional section
Shared condition with email event context. No client-side condition.

## From/reply-to
Default inherits WordPress/site mail policy. Custom address validated. Spoofing/DMARC implications warned; provider adapter may restrict.

CC/BCC
Off by default; token-derived recipients require validation and explicit event policy. BCC list size bounded.

## Plaintext
Auto-generated initial plaintext but editable. Publish preview shows both.

## Test send
- recipient entered by authorized admin;
- sample context selected;
- clearly prefixed/test metadata where appropriate;
- does not trigger business workflow side effects;
- audited lightly.

## Logs
WPE records event/template/provider handoff outcome and message ID if available, not full email body indefinitely by default.

## Tests
- XSS/token escape;
- invalid URL;
- missing token;
- responsive email fixtures;
- plaintext;
- provider timeout/retry duplication.

---

# 21. Message & Chat System — Pro

## Storage
Purpose-built indexed tables:
- conversations;
- participants;
- messages;
- read cursor/state;
- attachment references;
- moderation/report records.

Exact schema ADR required before implementation.

## Conversation types
- direct 1:1;
- group.

Channel/public rooms are not v1 unless separately specified.

## Direct conversation uniqueness
Default: one active direct conversation per unordered participant pair within same context if context key matches. Option may allow separate topic/context conversations through context UUID.

## Initiation policy
Required before frontend messaging enabled.
Examples:
- anyone authenticated;
- same membership;
- specific role pair;
- relation exists;
- dashboard/context members;
- Query segment.

Server checks recipient eligibility on create.

## Group options
- title required;
- owner(s)/moderator(s);
- max participants planning default 100 until load benchmark;
- members may invite false by default;
- participant removal permissions;
- history visibility for newly added participant: from join by default for privacy, configurable if group use case requires full history.

## Message
- body plain/rich limited safe markup;
- reply-to message optional;
- attachments;
- created/edited/deleted timestamps;
- author.

## Edit/delete windows
Defaults product decision:
- edit allowed 15 minutes for author unless moderator policy;
- delete-for-self vs delete-for-everyone semantics must be separate;
- hard delete not default; tombstone/audit where needed.

These defaults require UX review before acceptance.

## Read state
Efficient last-read message/cursor per participant preferred over per-message rows when semantics allow. Read receipts off by default for privacy; unread count always available to user.

## Attachments
- max count/size;
- MIME allowlist;
- protected delivery requiring participant authorization;
- no direct public Media URL for private chat attachment;
- virus scan adapter future.

## Search
- participant-authorized only;
- indexed text strategy;
- result pagination;
- retention respect;
- no cross-conversation leak.

## Block/report
User block prevents new direct messages according to policy and may hide/send restrictions; existing group semantics clearly defined.

Report fields:
- reason category;
- comment;
- message/conversation ref;
- status;
- moderator notes.

## Retention
Default retain until site policy changes; optional auto-delete after N days/months with legal/product warning and background purge. User deletion/privacy request semantics must account for messages involving other participants rather than blindly deleting conversation history.

## Realtime
Core REST/AJAX polling fallback. Poll interval adaptive/bounded; stop when tab hidden where safe. WebSocket/Pusher-like services are adapters.

## Authorization
Every operation checks conversation participation/moderation policy. Numeric ID knowledge never grants read.

## Tests
- IDOR across conversations;
- blocked user;
- removed group member;
- protected attachment direct URL;
- pagination/search leak;
- polling rate;
- edit/delete authorization.

---

# Automation & Communication specification status

These modules are **Specified at Phase 0 behavioral level**. Exact Job engine, mail/provider transports, realtime adapters and chat storage indexes remain implementation-blocking research/ADR items where noted.
