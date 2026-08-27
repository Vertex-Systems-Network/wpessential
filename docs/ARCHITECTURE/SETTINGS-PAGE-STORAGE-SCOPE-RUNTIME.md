# WPEssential — Settings Page Storage, Scope & Runtime Model

Status: **Phase 0 paper architecture / no implementation authorized**  
Related: Settings Page exhaustive spec, Custom Fields, Policy, Vault, REST, multisite.

## 1. Separation

Settings Page Builder separates:

1. **Settings Page Definition** — fields/layout/permissions/storage policy in Definition Repository.
2. **Settings Value Document** — current site/network values for one published definition identity.
3. **Secret Values** — Vault-owned, referenced by opaque secret refs.
4. **External/Native Setting Mapping** — optional adapter to existing WordPress/plugin setting.
5. **Render Surface** — wp-admin/frontend/dashboard component.

The UI layout is not the storage contract.

## 2. Default value-storage model

For WPE-owned ordinary settings, current paper preference is one namespaced value document per Settings Page + scope rather than one unstructured global option or arbitrary option rows per field.

Logical record:
- Settings Page UUID;
- schema/version;
- scope type/id;
- values keyed by stable field key/UUID mapping;
- last update timestamp/version;
- optional migration marker.

Physical WordPress storage remains implementation evidence, but site scope naturally maps to Options API and network scope to Network/Site Options APIs.

## 3. Scope modes

### Site
Value belongs to one WordPress site/blog.

### Network
One network-level value document; only network-authorized principals can mutate.

### Network default + site override
Same Settings definition supplies:
- network default document;
- optional per-site override document.

Resolution:
`explicit site override → network default → definition default`.

UI must show whether displayed value is inherited or overridden.

### User-specific settings
Not normal Settings Page storage by default. User preferences/profile values belong to User Profile/Field storage unless a real settings use case requires a registered user-scoped adapter.

## 4. Value identity

Field label may change without changing value identity.

Canonical dependency/value identity uses stable field UUID/key mapping.

Field key rename after publish is migration-class and requires alias/mapping rather than silently creating a new unrelated value.

## 5. Typed validation

Definition specifies logical type and validator.

Every save pipeline:
1. authenticate;
2. page/action capability;
3. field-level Policy;
4. nonce/CSRF for browser mutation;
5. reject unknown/unmapped fields;
6. normalize type;
7. validate;
8. sanitize/escape according to type/context;
9. resolve secret writes separately;
10. persist atomically enough for page/document semantics;
11. audit security-sensitive changes;
12. invalidate relevant caches.

WordPress `register_setting()` may register compatible native settings/REST schema, but WPE does not rely on declared `type` alone to validate ordinary admin form submissions.

## 6. Definition default vs persisted value

Three states remain distinct:
- field has no explicit persisted value;
- field persisted explicit null/empty value where schema permits;
- field inherits definition/network default.

A default change does not overwrite an explicit stored value.

UI can offer **Reset to inherited/default** action which removes explicit override rather than writing copied default.

## 7. Option grouping and size

Value document remains bounded.

Do not use one gigantic option for unrelated pages/modules.

Large datasets/logs/table-like records do not belong in Settings value document; they use Custom Tables/runtime stores.

Large media/files are stored as references.

## 8. Autoload policy

WPE should not automatically autoload every Settings Page document.

Candidate policy:
- only tiny, proven request-critical site settings may opt into autoload;
- ordinary builder settings default non-autoload/lazy access where underlying WP API permits;
- admin-only settings never need frontend autoload merely because they are settings;
- diagnostics surface unexpectedly large/autoloaded WPE options.

Exact Options API behavior/version must be tested after consent.

## 9. Secret field

A secret/credential field stores only Vault reference in Settings value document.

UI states:
- not configured;
- configured;
- replace;
- revoke/remove;
- connection/test status through owning adapter.

Never return existing plaintext into input value, REST response, import/export or frontend bootstrap.

## 10. External/native setting adapter

Settings Page may display/edit an existing registered WordPress/plugin setting only through explicit adapter descriptor:
- option/setting identity;
- supported scope;
- value schema;
- sanitize/validation contract;
- read/write capability;
- ownership/source plugin;
- REST exposure behavior;
- compatibility/version range.

Default external setting mode is inspect/read-only until write semantics are known.

WPE never presents arbitrary `option_name` free-form editing as safe generic Settings Page behavior.

## 11. WordPress registered settings

WPE can inspect `get_registered_settings()` and map known registered settings.

When WPE owns/registers a compatible setting, it can use WordPress metadata such as:
- type;
- label;
- description;
- sanitize callback/adapter;
- default;
- `show_in_rest` schema.

WPE's own schema remains authoritative for WPE UI, permissions and imports.

## 12. REST exposure

Off by default.

When enabled:
- explicit field allowlist;
- read/write Policy;
- typed schema;
- secret/internal fields always excluded;
- network/site scope checked server-side;
- write errors are structured;
- values resolved after inheritance/override according to endpoint contract.

Settings Page Builder does not expose every option simply because a page exists.

## 13. Frontend usage

Settings values can be consumed by:
- Component Blueprint bindings;
- Dynamic Listings;
- Forms defaults;
- Email templates;
- Workflow conditions;
- registered tokens/Abilities.

Consumers ask Settings service for typed value and scope; they do not call arbitrary WordPress option keys directly when using WPE definitions.

## 14. Revisions

Settings **definitions** are revisioned through Definition Repository.

Runtime **values** need an audit/history strategy separate from definition revisions.

Candidate per-page value-history modes:
- none beyond audit — default for ordinary low-risk settings;
- change history metadata/diff;
- snapshot before high-risk setting changes.

Secrets never copied into plaintext history.

Do not store every Settings save as a Definition revision.

## 15. High-risk setting changes

Fields can be classified high-risk, e.g.:
- integration credential reference;
- access/security policy;
- destructive retention behavior;
- remote endpoint/domain;
- billing/provider mapping.

Can require:
- recent auth;
- Level 2/3 confirmation;
- impact preview;
- audit;
- rollback/snapshot strategy.

## 16. Conditional fields

Conditional visibility uses shared Conditions Engine.

Hidden field save behavior is explicit:
- preserve existing — default;
- clear when hidden — opt-in with data-loss warning;
- ignore submitted hidden field from tampered request unless field policy permits.

Server validation never trusts client hidden state.

## 17. Import/export

Definition export includes field/storage/scope configuration.

Configuration value export is separate and explicit:
- values may be included per page/scope;
- secrets excluded/replaced by connection placeholders;
- inherited/default values can remain semantic rather than materialized;
- environment/site-specific URLs/IDs classified and remapped;
- network/site scope conflicts surfaced.

## 18. Multisite

Network settings require proper network capability and network context.

Rules:
- site admin cannot mutate network value;
- network default vs site override shown clearly;
- deleting a site removes/orphans its site-scope WPE settings according to multisite lifecycle policy;
- exporting one subsite does not silently export all network secrets/settings;
- cross-network assumptions avoided.

## 19. Cache model

Settings service may cache resolved value by:
- Settings Page/field identity;
- site/network scope;
- value version/generation;
- locale only if value resolution depends on locale.

On mutation, invalidate site/network inheritance chain as required.

Secret resolution is never copied into generic persistent cache in plaintext.

## 20. Failure/degraded behavior

- definition missing → consumer gets typed missing-definition error/default policy;
- corrupted value document → do not coerce arbitrary payload; diagnostics/recovery;
- invalid stored legacy value → safe fallback + health warning, not silent destructive rewrite;
- Vault unavailable → non-secret settings work; secret-dependent feature degraded;
- network default unavailable → explicit local/default fallback contract;
- Pro expiry → existing safe values remain readable for deployed runtime under ADR-0007; editing restricted.

## 21. Future executable evidence — NOT AUTHORIZED

After explicit consent:
- grouped option vs per-setting performance/atomicity comparison;
- autoload behavior across supported WP versions;
- site/network storage/override tests;
- REST schema/save tests;
- concurrent writes/stale edit conflict;
- protected secret fields;
- import/migration/field rename;
- Settings API compatibility;
- multisite site-delete/network cases.

## Paper recommendation

Accept **definition separate from scoped runtime value document**, with explicit site/network inheritance, Vault-backed secrets, bounded non-autoload-by-default storage and typed server validation.