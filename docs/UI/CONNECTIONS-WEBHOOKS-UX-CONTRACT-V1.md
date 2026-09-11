# Connections/Webhooks — Provisional UX Contract V1

Surface: **23 / Connections/Webhooks**  
Planning issue: **#589**  
Supervisor wave: **#583**  
Exact-main claim anchor: `6e0c29aeec9c7553ebb2d22decbc5f9437edecc1`

Lifecycle status: **PROVISIONAL PLANNING ONLY**. Surface 23 remains `UNSEEDED / 0` in the Master Options Bank. This document does not promote `UX_CONTRACT_COMPLETE` or authorize network/credential side effects.

## Product model

The UI separates:

- **Connection Definition** — provider/base host/non-secret configuration;
- **Vault Secret** — secret value outside ordinary definition payloads;
- **Outbound Request Profile** — reusable method/path/schema/retry policy;
- **Inbound Webhook Endpoint** — verified receiving contract;
- **Delivery / Receipt** — operational attempt/evidence.

Consumers select allowed connections/profiles without reading credentials.

## Information architecture

1. Connections
2. Create/Edit Connection
3. OAuth Connections
4. Outbound Request Profiles
5. Inbound Webhooks
6. Outbound Deliveries
7. Inbound Receipts
8. Health / Diagnostics
9. Settings

## Progressive disclosure

### Essential

- name/key/provider/type/status/environment;
- base host safe summary;
- auth type and secret presence state;
- timeouts/response size within safe defaults;
- Test Connection;
- dependency/usage summary;
- Validate / Save.

Secret values are entered only through dedicated replace/add controls and never preloaded back into the browser.

### Advanced

- allowed path prefix;
- method/header/query defaults;
- redirect policy;
- request profile mappings/schema;
- retry/idempotency;
- OAuth scopes/status;
- webhook signature/timestamp/replay settings;
- inbound schema/event mapping;
- log retention/redaction;
- rate/body/depth controls.

### Expert

- certified provider/auth adapters;
- private-network exception flow behind dedicated high-risk capability and exact allowlist;
- circuit/degraded behavior;
- secret-rotation overlap where provider requires;
- raw-receipt short retention policy;
- health-check cadence;
- provider-version/compatibility diagnostics.

No Expert control exposes arbitrary PHP, secrets, disabled TLS verification or unrestricted private-network access.

### System / Diagnostics

Read-only system view shows:

- provider/adapter version and health;
- DNS/TLS/auth/scope/latency test stages;
- safe resolved host summary;
- SSRF/redirect decision;
- credential presence/expiry/last-changed metadata;
- dependency usage count;
- outbound failure/rate/degraded state;
- webhook signature/replay/schema health;
- Vault availability;
- current site/network scope.

## Connections list

Columns: Name, Key, Provider/Type, Auth type, safe base host, health/status, scopes summary, used-by count, last test/use, credential expiry indicator, updated, actions.

Actions: Edit, safe Test, Reconnect/Reauthorize, Rotate credential, Usage, Duplicate without secrets, Export definition without secrets, Disable, Archive/Delete.

## Secret UX

Secret controls use explicit actions:

- Add / Replace;
- Clear;
- Rotate;
- Test after replace.

Blank edit fields mean keep existing unless Clear is explicitly selected. UI displays only presence/masked state and metadata. Secrets never appear in diff/export/log/AI context.

## OAuth UX

OAuth screen shows:

- provider;
- requested scopes before redirect;
- connected account safe label;
- current scopes;
- expiry/refresh state;
- last refresh;
- Reauthorize / Disconnect.

Adding scopes marks reauthorization required and shows impacted dependents. Old connection remains until new authorization succeeds where safe.

## Safe HTTP / SSRF UX

Base URL validation shows why a destination is allowed/blocked. By default block loopback, link-local, RFC1918/private ranges, cloud metadata, reserved ranges and unsupported schemes.

Redirect mode is explicit: none, same-host, allowlisted hosts, bounded count. Every redirect is revalidated and secret/auth headers are not forwarded cross-host unless a certified provider adapter requires it.

No normal “ignore SSL errors” toggle exists.

## Outbound Request Profile UX

Fields:

- connection;
- relative path;
- method;
- typed headers/query/body mapping;
- accepted statuses;
- response mode/schema/max bytes;
- timeout;
- retry profile;
- idempotency mapping;
- log redaction.

Dynamic host is absent by default. Component-aware encoding is used for path/query values. Host/Content-Length/adapter-owned Authorization/proxy headers are controlled.

## Inbound webhook UX

Endpoint editor shows:

- name/key/status/provider;
- generated route;
- signature/auth mode;
- Vault secret reference state;
- timestamp/replay window;
- body schema limits;
- provider event-ID/type mapping;
- normalized WPE Event/Workflow consumer;
- rate/body/concurrency controls;
- expected acknowledgement behavior.

Endpoint obscurity is explicitly labelled as not authentication.

## Signature/replay diagnostics

Diagnostics explain ordered verification: method/size → raw body capture → provider identification → timestamp/replay → constant-time signature verify → parse/schema → event ID/idempotency → receipt → async consumer.

Duplicate provider events are acknowledged according to adapter semantics but do not rerun domain mutation.

## Logs / receipts

Operational evidence defaults to metadata and redacted fields. Never show Authorization, cookies, secrets or unbounded payloads.

Raw inbound body retention is off/short-bounded candidate and clearly marked sensitive.

## Accessibility

- keyboard-operable forms/tables/test/rotation dialogs;
- linked validation/SSRF errors;
- focus after OAuth return/test/rotate/save;
- no color-only health/signature/replay status;
- accessible masked-secret state labels;
- responsive delivery/receipt tables;
- restrained live status during tests;
- axe coverage for healthy/degraded/auth-expired/signature-failed states once implemented.

## Multisite

Future normalized contract must define site/network Connection scope, credential/Vault scope, OAuth callbacks, inbound route ownership, logs/receipts visibility, dependency use and export/import behavior. Cross-site access must be Policy-authorized, never inferred from Super Admin presentation.

## Portability

Exports contain provider/type/non-secret settings and credential placeholders only. Imports enter `credential_required` degraded state until a new local Vault secret is bound. Generic package orchestration remains Surface 26.

## Lifecycle decision

This is provisional interaction guidance only. Exact-main Master Options Bank remains `UNSEEDED / 0`; re-review after Bank review and schema-valid Atomic Option Contracts is mandatory before runtime authorization.
