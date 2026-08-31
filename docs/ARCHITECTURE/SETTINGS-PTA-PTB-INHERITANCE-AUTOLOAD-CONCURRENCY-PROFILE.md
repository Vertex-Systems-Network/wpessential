# WPEssential — Settings PT-A/PT-B Inheritance, Autoload & Concurrency Profile

Status: **Phase 0 paper runtime profile / no option writes, REST runtime or benchmark execution authorized**  
Date: 2026-08-28  
Related: Settings Page Storage Scope Runtime, ADR-0036, ADR-0069, ADR-0071, Vault ADR-0085.

## Purpose

Narrow the physical runtime model for WPE-owned Settings Pages while preserving WordPress-native Options ownership, explicit network inheritance and safe concurrency.

Settings values are bounded configuration documents, not a general business-record store.

## Physical profiles

### ST1 — PT-A site Settings value document — first site baseline

For one site-scoped Settings Page:
- one namespaced WPE-owned option/value document per Settings Page identity + site scope;
- stable field keys/UUID mapping inside the document;
- document schema/value version;
- explicit non-autoload by default;
- Vault references only for secret fields.

ST1 is the default for bounded WPE-owned site configuration.

### ST2 — PT-B network Settings value document — first network baseline

For explicitly network-scoped Settings:
- one network option/value document per Settings Page identity + network scope;
- mutation requires network capability/Super Admin policy as applicable;
- never materialized into every subsite merely for read convenience.

### ST3 — Network default + site override

Inheritance stores two independent documents:
- ST2 network default;
- optional ST1 site override.

Resolution remains:
`explicit site override → network default → Definition default`.

Reset-to-inherited removes the site override value rather than copying the current network/default value.

### ST4 — per-field option rows — comparison/control only

Per-field physical options may be benchmarked for atomic hot-field updates or native compatibility, but are not the default builder model because they increase option proliferation and complicate page-level atomicity/migration.

Any ST4 use must remain schema-registered and bounded; arbitrary option-name editing is not accepted.

## Document size bounds

A Settings document has an explicit size/complexity budget.

If values become growing/queryable records, logs or large arrays, they must move to the owning runtime/custom-table domain rather than expanding an option indefinitely.

Files/media remain references.

Exact byte thresholds require evidence.

## Autoload policy

Default: **non-autoload**.

Autoload may be enabled only for tiny, proven request-critical site settings after evidence.

Rules:
- admin-only settings never autoload by default;
- network inheritance does not cause copying/autoloading into every site;
- diagnostics surface WPE option size and autoload state;
- a page cannot opt every field into separate autoloaded options as a generic performance shortcut.

## Concurrency model

A Settings Value Document exposes a monotonic value version/generation.

Candidate save semantics:
1. read current document + version;
2. client/editor submits expected version;
3. server reauthorizes and revalidates fields;
4. if version changed, return stale-edit conflict unless an explicit safe merge policy exists;
5. apply whole-document mutation atomically enough for selected backend semantics;
6. increment version;
7. invalidate resolved inheritance caches.

Last-write-wins without conflict visibility is not accepted for high-risk settings.

Exact Options API compare-and-set/locking implementation is evidence-gated.

## Partial update semantics

API/UI may submit a bounded patch, but the server resolves it against the current typed document and validates resulting full state.

Unknown fields are rejected. Hidden/conditional fields follow explicit preserve/clear policy.

A patch cannot bypass page/field authorization by omitting the rest of the document.

## High-risk fields

Security/access/retention/provider/billing/Vault-reference changes can require:
- recent authentication;
- stronger confirmation;
- impact preview;
- audit;
- pre-change snapshot/diff metadata where appropriate.

Secret plaintext never enters Settings history.

## Cache profile

Resolved Settings cache identity includes:
- Settings Page + field identity;
- site/network scope;
- site override generation;
- network default generation;
- Definition revision/default generation;
- locale only when semantics depend on locale.

Network default mutation invalidates or version-bypasses all affected site-resolved cache entries without requiring synchronous enumeration/update of every site row.

Secret plaintext is never cached in generic persistent Settings cache.

## REST/API profile

REST remains Off by default.

When enabled:
- explicit field allowlist;
- typed read/write schema;
- site/network scope resolved server-side;
- secret/internal fields excluded;
- mutation uses same expected-version concurrency semantics;
- resolved vs raw/inherited representation is explicit in endpoint contract.

## Native/external setting adapter

Existing WordPress/plugin settings may be exposed only through a versioned adapter declaring:
- exact setting identity/scope;
- read/write capability;
- validation/sanitize behavior;
- compatibility range;
- ownership.

Unknown external settings default inspect/read-only. WPE does not become a generic arbitrary `wp_options` editor.

## Multisite lifecycle

Site deletion:
- removes/retains site ST1 override according to lifecycle/retention policy;
- never deletes ST2 network default;
- site export does not automatically include network secrets/settings.

Clone:
- site-scoped ordinary values may copy according to clone policy;
- environment/provider/Vault refs may require rebind/deactivation;
- inherited network values are resolved on target rather than blindly materialized from source.

## Future evidence — NOT AUTHORIZED

Compare:
- ST1 grouped option vs ST4 per-field rows;
- read/write/query count and object-cache behavior;
- concurrent stale editor saves;
- large document bounds;
- autoload behavior across supported WordPress versions;
- ST2/ST3 inheritance invalidation at 100/1k/10k sites;
- site delete/clone/export;
- REST read/write scope attacks;
- Vault-degraded behavior.

Correctness gates:
- site admin cannot mutate network value;
- stale high-risk write cannot silently overwrite newer state;
- network default change cannot leave indefinitely stale inherited values under accepted cache model;
- explicit empty/null cannot be confused with inheritance;
- secret plaintext cannot appear in option/history/REST/cache.

Executed Settings fixtures: **0**.

## Paper recommendation

Use **ST1/PT-A grouped site document** and **ST2/PT-B grouped network document**, with **ST3 explicit inheritance**. Non-autoload is default. ST4 per-field rows remain a bounded comparison/adapter case, not the universal model.