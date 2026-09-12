# Emails — Native WordPress Audit V1

Surface: **20 / Emails**  
Issue: **#696**  
Parent: **#686**  
Exact-main audit anchor: `3839142cc8a225f443fcf9f6e0c26eb9641742e7`  
Seed: `config/product/options-bank/emails.json` — **16 records / BANK_SURFACE_SEEDED**

## Decision

**NATIVE AUDIT EVIDENCE COMPLETE; WORDPRESS PROVIDES MAIL TRANSPORT/FILTERS AND CORE EVENT-SPECIFIC MAIL FLOWS, NOT AN EMAIL TEMPLATE-BUILDER PRODUCT.** No mail is sent and no sender/provider configuration is mutated here.

## Current native sources

- `wp_mail()` — https://developer.wordpress.org/reference/functions/wp_mail/
- `phpmailer_init` — https://developer.wordpress.org/reference/hooks/phpmailer_init/
- `wp_mail` filter — https://developer.wordpress.org/reference/hooks/wp_mail/
- `wp_mail_from` / `wp_mail_from_name` — https://developer.wordpress.org/reference/hooks/wp_mail_from/
- `wp_mail_content_type` — https://developer.wordpress.org/reference/hooks/wp_mail_content_type/
- `wp_mail_failed` / `wp_mail_succeeded` — https://developer.wordpress.org/reference/hooks/wp_mail_failed/ and https://developer.wordpress.org/reference/hooks/wp_mail_succeeded/
- `wp_new_user_notification()` — https://developer.wordpress.org/reference/functions/wp_new_user_notification/
- password-reset/account mail flows are native event-specific flows, not a generic template catalog
- accepted readiness evidence: `docs/PRODUCT/EMAILS-BANK-ENTRY-READINESS-V1.md`

`wp_mail()` accepts recipients/subject/message/headers/attachments and initializes PHPMailer, but Surface 20 must not treat global mail interception as a stable semantic-event catalog. Recipient routing remains Notifications-owned and credentials/provider transport stay outside template ownership.

## Seed-record disposition

| Seed record | Native disposition | Integration action |
|---|---|---|
| `emails.template.identity` | **WPE PRODUCT SEMANTIC** | no native template-definition lifecycle claim |
| `emails.metadata.message` | **NATIVE-PARTIAL** — subject/message/content type exist; preheader/locale/template mode are WPE | attach `wp_mail()`/content-type provenance without calling it a template engine |
| `emails.layout.shell` | **WPE/MARKET-ONLY** | no native reusable email-shell system |
| `emails.brand.tokens` | **WPE/MARKET-ONLY** | sender/site metadata may be inputs; no native branding system |
| `emails.block.structure` | **WPE EMAIL-SAFE RENDERER** | no native block-email renderer claim |
| `emails.block.actions-media` | **WPE EMAIL-SAFE RENDERER** | native mail accepts HTML/plain message but not a typed email block model |
| `emails.block.dynamic` | **WPE TOKEN/CONDITION SYSTEM** | no executable template language; bounded providers only |
| `emails.token.schema` | **WPE HARD SAFETY** | no native typed token schema |
| `emails.locale.plaintext` | **NATIVE-ADJACENT + WPE RENDERER** | core has locale-aware flows; deterministic fallback/plaintext generation remains WPE |
| `emails.override.wordpress` | **NATIVE-CONFIRMED COMPATIBILITY BOUNDARY** | map only reviewed stable core event adapters/filters; reject generic string interception |
| `emails.override.third-party` | **MARKET/PROVIDER COMPATIBILITY** | certified adapter only |
| `emails.sender.profile` | **NATIVE-PARTIAL / PROVIDER BOUNDARY** — from/name filters exist; credentials/verification do not belong in templates | keep profile reference external and secret-free |
| `emails.attachment.policy` | **NATIVE-CONFIRMED SUBSTRATE + WPE POLICY** | `wp_mail()` supports attachment paths; authorization/size/MIME/private-file policy remains WPE/resource-owner concern |
| `emails.preview.diagnostics` | **WPE/MARKET-ONLY** | no native template preview/client preflight product |
| `emails.portability.performance` | **WPE/IMPORT-EXPORT/CACHE** | no native email-definition portability/compile-cache contract |
| `emails.safety.raw-executable` | **REJECTED-UNSAFE CONFIRMED** | keep `REJECT`; raw PHP/script/untrusted HTML execution remains forbidden |

## Required Supervisor Bank integration

1. Populate native `wp_mail()`/PHPMailer/filter/core-event sources.
2. Enrich `metadata.message`, `override.wordpress`, `sender.profile` and `attachment.policy` with native provenance.
3. Add an explicit note to `override.wordpress`: only a reviewed adapter catalog of stable core events is supported; global arbitrary mail interception is not an authored option.
4. Preserve template/layout/blocks/tokens/preview/portability as Surface 20 WPE/market semantics.
5. Preserve recipient/event routing as Surface 19 responsibility and transport credentials/providers as external connection/adapter ownership.
6. Keep `safety.raw-executable` rejected.

## Native completeness / unresolved items

No new V1 family is required: native mail metadata, sender hooks, attachment support, success/failure observation and core event adapters map into existing records. The audit intentionally does not convert transport primitives into template-builder parity.

Actual sends, test sends, provider mutation, attachments of protected files, override runtime and deliverability evidence remain later gates.

## Gate boundary

Worker conclusion: **native evidence complete; Bank provenance/boundary integration pending**.  
Not promoted here: `NATIVE_AUDITED`, market review, `BANK_REVIEWED`, send/provider runtime, Atomic Option Contract, UX or product parity.