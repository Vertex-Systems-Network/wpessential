# ADR-0064 — Versioned Static Evidence Overlays for Backup Provider Profiles

Status: **Accepted planning/evidence-governance architecture / runtime certification pending**  
Date: 2026-08-27

## Context

ADR-0061 established stable `bf.*` family keys, provider keys, profile versions and a strict separation between static evidence (SE0–SE3) and runtime certification (C0–C4).

Provider documentation changes over time. Rewriting the original provider registry row in place every time official documentation improves makes it harder to audit when and why a capability assumption changed, and can accidentally turn static research into an implied certification claim.

The 2026-08-27 low-evidence refresh also demonstrates that evidence improves unevenly: Box and Hetzner now have strong documented large-transfer/finalization/lifecycle semantics, while MEGA still lacks a chosen WordPress/PHP adapter profile and Bunny Storage still lacks a proven ordinary-Storage crash-resumable session in the reviewed sources.

## Decision

WPEssential may publish **versioned static-evidence overlays** for provider profiles.

An overlay:
- names the provider key;
- records review date/source set;
- states prior and new SE level;
- records only documented static capability changes/deviations;
- explicitly supersedes the older static row for those fields;
- never changes C0–C4 certification;
- never changes the canonical `family_key`/`provider_key` identity unless a separate ADR does so;
- can later be consolidated into a new canonical registry revision without losing history.

The first overlay is:
`docs/ARCHITECTURE/BACKUP-PROVIDER-STATIC-EVIDENCE-REFRESH-2026-08-27.md`.

## Current refreshed profiles

Static maturity after the overlay:
- `box` → SE3;
- `minio` → SE3;
- `rackspace-swift` → SE2;
- `akamai-linode-object-storage` → SE2;
- `hetzner-object-storage` → SE3;
- `bunny-storage` → SE2 with non-resumable claim until stronger evidence;
- `mega` → SE1.

All other canonical rows retain their prior SE state unless another overlay explicitly supersedes them.

## Important provider-specific decisions

### Box
Official chunked upload session/parts/commit semantics are sufficient for SE3 paper maturity; asynchronous 202 commit handling is a provider-specific future runtime requirement.

### MinIO / AIStor
Current S3 compatibility docs establish full multipart operation set for the reviewed standard mode and document lifecycle/API deviations. Server mode/version must be part of the profile.

### Rackspace Cloud Files
Current docs establish segmented large objects and provider manifest behavior, but not enough current lifecycle/integrity evidence for SE3.

### Akamai/Linode Object Storage
Current docs expose a material AWS SDK compatibility risk with newer default S3 integrity protections. A provider adapter must solve this without pinning a stale/insecure dependency simply to make uploads work.

### Hetzner Object Storage
Current official docs establish multipart limits, lifecycle cleanup, versioning and Object Lock sufficiently for SE3 static maturity.

### Bunny Storage
Native HTTP/API upload and an official PHP client exist, but asynchronous client calls do not imply durable crash-resume. Bunny Stream/TUS behavior must not be inherited into Bunny Storage Backup.

### MEGA
An official maintained SDK/API exists, but choosing MEGAcmd/subprocess or another bridge for WordPress would be an implementation/dependency/security decision. No resumable/finalization claim is made yet.

## Certification invariant

Static overlays cannot produce these labels:
- Connected/Experimental (C0);
- Upload Certified (C1);
- Integrity/Resume Certified (C2);
- Supported Backup Destination (C3);
- Disaster Restore Certified (C4).

After ADR acceptance:
- target provider count = 34;
- runtime-certified provider count = **0**;
- normal Supported Backup Destination count = **0**.

## Consequences

Positive:
- research history is auditable;
- static provider changes do not silently rewrite runtime claims;
- future provider/API changes can be captured without losing prior reasoning;
- uncertainty remains explicit.

Cost:
- consumers of planning docs must apply the latest overlay for a provider;
- periodic consolidation into a new registry revision is desirable to avoid excessive overlay chains.

## Future evidence

Runtime C0–C4 certification remains governed by ADR-0053/P-013. No provider execution was performed to accept this ADR.
