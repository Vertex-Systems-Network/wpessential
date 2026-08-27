# WPEssential — Forms Entry Runtime Storage Candidate

Status: **Phase 0 paper architecture / no tables or form runtime authorized**  
Related: Forms & Workflow Exhaustive Spec, Field Storage ADR-0022, Workflow Runtime candidate, Privacy contract.

## Purpose

Separate **Form Definition configuration** from **submission/runtime data** and define a storage model that can handle validation, files, save/resume, retries, privacy retention and workflow linkage without turning form entries into arbitrary serialized blobs that cannot be queried or erased safely.

## Ownership boundary

### Definition Repository owns
- Form schema/layout;
- field placements;
- access rules;
- submission settings;
- success outcome;
- workflow bindings;
- spam/retention policy references.

### Forms Runtime owns
- submission/entry identity;
- canonical submitted values;
- validation/spam state;
- actor/context metadata allowed by policy;
- file references;
- save/resume drafts;
- workflow/run links;
- state/history needed for entry operations.

Workflow Runtime owns workflow execution state, not Forms.

---

# Entry model

Candidate `Form Entry` logical fields:
- internal ID + stable UUID;
- Form UUID + published revision UUID pinned at submission start/finalization;
- state;
- actor user ID nullable;
- owner/subject user ID where distinct;
- created/updated/submitted timestamps UTC;
- source context type/ref;
- request correlation/idempotency key;
- validation version;
- spam status/score/provider reference safe subset;
- storage/privacy classification;
- retention policy snapshot/reference;
- workflow run UUID(s) references;
- entry version for optimistic concurrency;
- anonymized/deleted timestamps if applicable.

Do not store raw password fields, security tokens, or arbitrary request headers by default.

## Entry states
Candidate:
- `draft`
- `pending_validation`
- `submitted`
- `processing`
- `completed`
- `failed_action`
- `spam`
- `rejected`
- `expired_draft`
- `anonymized`
- `deleted_tombstone` where audit requires minimal trace.

State meanings are separate from Workflow run status.

---

# Value storage alternatives

## Alternative A — normalized value rows
Logical child rows:
- entry ID/UUID;
- field UUID;
- occurrence/item identity;
- typed scalar/reference value columns or canonical encoded value;
- ordering/path metadata for repeaters;
- privacy class.

Benefits:
- field-level export/erase;
- selective querying;
- easier schema evolution across Form revisions.

Costs:
- many rows;
- type/query complexity;
- potential EAV-like performance if abused for reporting.

## Alternative B — one canonical structured document per Entry
Benefits:
- atomic read/write;
- compact row model;
- preserves nested/repeater structure.

Costs:
- weak field-level DB querying/indexing;
- privacy erase transforms entire document;
- DB JSON semantics vary by compatibility profile.

## Alternative C — hybrid candidate
Current paper preference:
- Entry core row normalized;
- canonical field-value document for complete submission truth;
- optional **explicit projections/index rows only for fields administrators mark queryable/reportable**;
- files stored as protected media/object references;
- relation/entity actions use their destination stores, not duplicated into Entry as source of truth after action.

Why:
- Forms entries are usually read as one submission;
- unlimited EAV projections would recreate metadata scaling problems;
- selected searchable/indexed fields can still support admin filtering/reporting.

This preference requires benchmark evidence before acceptance.

---

# Canonical value document

Requirements:
- schema version;
- field UUID keys, not only mutable labels/slugs;
- typed canonical values;
- stable repeater item IDs/order where needed;
- explicit null/empty/missing distinctions;
- no rendered HTML as canonical data unless field type itself is sanitized rich content;
- external/entity references store stable IDs/UUIDs;
- file values store protected file/media references, not temporary upload URLs;
- secret/password-equivalent fields omitted or converted to action result/reference only.

Display labels are resolved from pinned Form revision for historical rendering.

---

# Query/index projection

Per field option:
- not indexable (default for complex/sensitive);
- equality projection;
- typed range/sort projection;
- full-text/search projection later;
- external reporting projection adapter.

Projection includes:
- entry ID;
- field UUID;
- typed normalized searchable value;
- generation/version.

Projection is derived and rebuildable from canonical entry value when policy allows.

Sensitive/secret fields default non-indexed.

---

# Revision pinning

Each draft/final submission pins Form revision semantics.

Rules:
- editing Form after a user begins save/resume does not silently reinterpret existing values;
- draft may offer explicit migration to newer revision only through compatible mapping;
- submitted Entry always renders/validates history against pinned revision snapshot/reference;
- deleting old Definition revision is blocked while retained Entries require it unless sufficient immutable display/schema snapshot exists.

---

# Validation persistence

Store normalized validation outcome metadata, not full internal stack traces.

Potential:
- validation status;
- validator schema version;
- field error codes/safe messages for active draft only;
- final rejection reason category;
- spam classification.

Transient field validation errors do not require indefinite retention after successful submit.

---

# Save / resume drafts

Draft fields:
- draft Entry UUID;
- resume token hash/reference;
- expiry;
- owner user ID or guest session subject;
- pinned Form revision;
- safe stored values;
- file draft refs;
- last activity.

Rules:
- raw resume token never stored after issuance;
- guest token high entropy;
- sensitive fields can opt out of draft persistence;
- expiry cleanup chunked;
- capacity/limit final checks rerun at submission time;
- claiming a guest draft into authenticated account requires proof of token + policy.

---

# File storage

Entry stores file references, not arbitrary filesystem paths.

Modes:
- WordPress Media when public/normal media semantics accepted;
- WPE private/protected asset storage for private form uploads;
- registered external object storage adapter.

File lifecycle must know:
- draft vs submitted;
- owner Entry;
- retention;
- access policy;
- malware/scan status if provider supports;
- orphan cleanup.

Deleting/anonymizing Entry does not blindly delete shared Media attachment referenced elsewhere.

---

# Submission transaction boundary

Candidate final submission:
1. authenticate/access policy;
2. load pinned/current allowed Form revision;
3. verify idempotency/submission token;
4. normalize/validate fields server-side;
5. capacity/rate/spam checks;
6. persist Entry + canonical values/files metadata atomically where practical;
7. mark submitted;
8. commit;
9. enqueue/trigger Workflow actions;
10. return accepted result.

Business side effects such as CRM/email are not performed before durable Entry acceptance unless Form is configured no-storage and action semantics explicitly support it.

## No-storage Forms

If user selects “do not persist entries”:
- temporary durable envelope may still be required for idempotent workflow processing;
- retention can be extremely short after successful action;
- audit records store safe metadata only;
- UI must explain reduced recovery/retry/history capability.

“No storage” must not mean secrets remain in queue logs forever.

---

# Duplicate submit/idempotency

Entry acceptance uses client/session submission identity where practical.

Duplicate POST retry should return/reuse existing accepted Entry rather than create duplicates when idempotency contract matches.

Idempotency scope includes Form revision + actor/session + key and bounded expiration.

---

# CRUD Forms

When Form updates/deletes external entity:
- Entry is audit/submission record only;
- destination Data Source remains canonical entity data;
- mapped target fields are explicit;
- hidden/tampered target IDs reauthorized server-side;
- update precondition/version checks prevent stale overwrite where source supports;
- delete action requires higher confirmation/policy.

Do not copy destination entire record into Entry unless configured snapshot is needed and privacy impact accepted.

---

# User registration/password flows

Password values are never stored in Entry canonical document.

User registration action hands password directly through secure action boundary and stores only safe action outcome/reference.

Reset/recovery token similarly never stored as generic Form Entry field.

---

# Retention

Per Form:
- retain indefinitely;
- retain duration;
- anonymize after duration;
- delete after successful processing duration;
- no long-term storage.

Separate:
- Entry metadata;
- values;
- files;
- failed/spam entries;
- drafts.

Candidate defaults require separate product decision; public Forms should not silently keep IP/user-agent unless enabled for purpose.

---

# Privacy exporter/eraser

Exporter can locate by:
- authenticated user ID;
- verified email field mapping where Form declares personal identity key;
- module-specific ownership relation.

Eraser behavior per Form/field:
- delete;
- anonymize;
- retain with reason;
- remove file;
- preserve aggregate projection without identity.

Formula/CSV export escaping remains Import/Export responsibility.

---

# Entry admin list performance

Hot filters:
- Form UUID/revision;
- state;
- submitted date;
- user/owner;
- spam;
- explicitly projected field values.

Never generate SQL joins over every arbitrary field merely because admin added a filter. Unsupported non-projected fields require bounded scan/job/export workflow or prompt to create projection.

---

# Observability

Entry events:
- draft.created/resumed/expired;
- entry.submitted/validated/rejected/spam;
- processing.completed/failed;
- anonymized/deleted.

Audit safe metadata, not whole submission payload.

---

# Failure states

## DB save fails
No workflow side effects. User receives retry-safe error/idempotency preserved where possible.

## Workflow enqueue fails after Entry commit
Entry remains authoritative `submitted/processing_pending`; diagnostics/retry can enqueue later.

## File finalize fails
Submission either blocks before commit or stores explicit incomplete state according to field requirement; never claim complete if required file missing.

## Form revision missing/corrupt
Draft/Entry enters degraded read-only/recovery; never reinterpret against unrelated latest schema.

---

# Paper recommendation

Prefer hybrid:
- normalized Entry core;
- versioned canonical values document;
- selected typed query projections;
- first-class protected file refs;
- revision pinning;
- Workflow runtime separate.

Reject:
- one WP post per Entry as universal default;
- unlimited EAV rows for every nested value as sole canonical model;
- raw serialized PHP object payload;
- storing passwords/tokens in Entries.

## Future benchmark — NOT AUTHORIZED

Test after explicit consent:
- 10k/100k/1M Entries;
- small/large/repeater payloads;
- projected vs non-projected filtering;
- save/resume concurrency;
- duplicate submit;
- workflow enqueue failure;
- private file lifecycle;
- privacy export/erase batching;
- Form revision evolution;
- spam retention cleanup;
- object cache/no cache.

No table/schema/runtime code has been created or executed.