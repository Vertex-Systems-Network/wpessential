# User Profile — UX Contract V1

Surface: **14 / User Profile**  
Issue: **#497**  
Lifecycle: planning-only; no runtime promotion.

## Essential

- **Profile fields** — compose native and registered custom fields with clear required/optional states.
- **Public slug** — stable profile slug with collision feedback, preview and safe reset behavior.

## Advanced

- **Field privacy/editability** — explicit who-can-view / who-can-edit policy per field with effective-state feedback.
- **Public visibility** — public, authenticated or restricted visibility policy without client-only enforcement.
- **Registration policy** — verification, approval and post-registration state shown as server-enforced workflow policy.
- **Authentication surface** — presentation of registered login/logout/reset flows only; credentials remain with the canonical auth owner.
- **Directory query** — bounded filters, sorting, pagination and privacy-aware empty/forbidden states.

## Expert

- **Portability** — versioned profile-definition import/export with conflict preview, field-owner remapping and redaction diagnostics.

## Interaction contract

- Search/focus reaches every configurable setting.
- Forms expose required, invalid, saved, pending-verification, forbidden and recovery states accessibly.
- Privacy/effective visibility is shown separately from authored configuration.
- Keyboard operation, semantic labels, error summaries and visible focus are required.

## Security / privacy / multisite / compatibility / performance

- Server-side resource authorization and CSRF protection are authoritative for profile mutations.
- Passwords, reset tokens, sessions and secrets never enter portable profile definitions.
- Site/network/user scope is explicit and cross-site profile disclosure is forbidden by default.
- Missing custom-field/auth providers fail safely without corrupting stored profile data.
- Directory results are paginated/bounded and avoid N+1 metadata reads.
- Optional profile assets load only on profile surfaces.

This contract does not authorize runtime implementation, deployment or certification.