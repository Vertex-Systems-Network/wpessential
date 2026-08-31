# ADR-0061 — Backup Provider Family Identity & Capability Registry

Status: **Accepted backup architecture / provider runtime evidence pending**  
Date: 2026-08-27

## Context

ADR-0053 accepted the protocol-family adapter + provider capability-profile architecture for Backup destinations. Subsequent planning documents accidentally assigned the same numeric `PF-xx` labels to different families. For example, one document used `PF-02` for FTP/FTPS while another used `PF-02` for S3-compatible storage.

That ambiguity is harmless in prose but unsafe if the identifiers are ever serialized, imported, persisted or used to choose an adapter.

At the same time, static provider research now covers all 34 target destinations at varying maturity. Static documentation evidence must remain separate from executable C0–C4 certification.

## Decision

### 1. Semantic family keys are canonical

Backup protocol families use stable semantic keys:

- `bf.local-filesystem`
- `bf.browser-export`
- `bf.ftp`
- `bf.ftps`
- `bf.sftp`
- `bf.webdav`
- `bf.s3`
- `bf.gcs`
- `bf.azure-blob`
- `bf.google-drive`
- `bf.msgraph-drive`
- `bf.dropbox`
- `bf.swift`
- `bf.native`

These keys are the canonical family identifiers for future schemas, APIs, imports/exports and adapter selection.

### 2. Numeric `PF-xx` identifiers are legacy documentation aliases only

Earlier `PF-xx` labels are **not canonical machine identifiers**.

They MUST NOT be:
- persisted as the sole family identity;
- serialized in new API/config/export formats;
- used as adapter registry keys;
- interpreted without knowing the source namespace/document version.

A bare legacy value such as `PF-02` is ambiguous and must fail validation unless a migration/import routine knows which historical namespace produced it.

No silent guess is allowed.

### 3. Provider identity is separate from family identity

A provider capability profile is identified conceptually by:

`family_key + provider_key + provider_profile_version + adapter_version`

Examples:
- `bf.s3 + amazon-s3 + profile-date/version + adapter-version`
- `bf.webdav + nextcloud + server/profile-version + adapter-version`
- `bf.msgraph-drive + onedrive-business-sharepoint + graph/profile-version + adapter-version`

A family expresses reusable transfer semantics. A provider profile expresses the provider-specific deviations, limits, authentication, checksum, retention, deletion and restore behavior.

### 4. Family inheritance is conservative

A provider that advertises protocol compatibility does not automatically inherit every capability of the reference provider.

Examples:
- `S3 compatible` does not imply Amazon S3 part-count, checksum, lifecycle, Object Lock, versioning or addressing semantics;
- generic WebDAV does not imply resumable/chunk upload;
- Nextcloud chunking is a provider-specific WebDAV extension;
- OneDrive Personal and OneDrive Business/SharePoint share a Graph family but remain separate capability profiles;
- provider-native APIs inherit no capability from another family unless explicitly certified.

Unknown capability remains `unknown`, not optimistic `true`.

### 5. Static evidence and executable certification are separate

Static documentation research uses:
- **SE0** — insufficient current official evidence;
- **SE1** — official protocol/compatibility statement reviewed;
- **SE2** — upload/finalization/limits reviewed;
- **SE3** — upload + integrity/lifecycle/deviation semantics reviewed.

SE0–SE3 is planning evidence only.

It does **not** grant:
- C0 connection certification;
- C1 upload certification;
- C2 resumability/integrity certification;
- C3 restore support;
- C4 disaster restore certification.

Current provider certification count remains **0**.

### 6. Canonical registry

`docs/ARCHITECTURE/BACKUP-PROVIDER-FAMILY-CAPABILITY-REGISTRY.md` is the canonical planning registry for:
- stable family keys;
- legacy PF alias mappings;
- baseline family capability assumptions;
- provider keys;
- provider-specific static overrides;
- SE0–SE3 static evidence maturity;
- minimum future provider-profile schema.

The existing target matrix remains the catalog/priority view and must use or map to these semantic family keys.

### 7. ADR-0053 remains intact

This ADR does not supersede ADR-0053's protocol-family/provider-profile or C0–C4 certification architecture.

It resolves identifier ambiguity and makes capability inheritance stricter.

## Consequences

Positive:
- no future schema can confuse FTP with S3 because both were once called `PF-02`;
- provider profiles can evolve independently from reusable adapters;
- capability differences remain explicit;
- provider compatibility claims become reviewable/versionable;
- static research cannot accidentally become a public support claim;
- legacy planning documents remain understandable through explicit alias maps.

Cost:
- future migration/import logic must recognize historical namespace/version when legacy PF aliases are encountered;
- existing planning docs should be normalized to semantic family keys over time;
- each provider profile needs explicit version/evidence maintenance.

## Evidence still required

After explicit owner development/executable-spike consent:
- actual family/provider registry schema/API;
- legacy-import ambiguity fixtures;
- adapter selection tests;
- provider capability negotiation/probing where appropriate;
- C0–C4 fixtures under ADR-0053/P-013;
- provider/API-version downgrade/expiry behavior;
- restore certification and disaster restore evidence;
- capability-profile migration/compatibility tests.

No provider connection, SDK, API call, upload, delete, restore, migration or executable certification has been performed by this decision.
