# Emails — Market Audit V1

Status: `RESEARCH_COMPLETE / SUPERVISOR_INTEGRATION_PENDING`

Surface: 20 — Emails  
Snapshot: 2026-09-12  
Parent: #713  
Worker issue: #723  
Base: `54e4e70095f74a6ebff15da83cc2bbf3d447a49c`

## 1. Scope and boundary

This evidence-only audit reconciles all 16 current Emails records. Email owns reusable message/template definitions and rendering policy. WordPress `wp_mail()`/PHPMailer remain transport substrate; sender/provider credentials remain external/Vault-owned. Raw PHP/script/untrusted executable HTML remains rejected.

## 2. Current market evidence

### E1 — WooCommerce Email Settings / Block Email Editor
Official evidence:
- https://woocommerce.com/document/configuring-woocommerce-settings/emails/
- https://developer.woocommerce.com/2026/06/woocommerce-10-9-beta-block-email-editor-updates/

Verified: built-in transactional email types, sender identity, template/global styling, per-email settings, preview/test flows, and current block-editor template update/diff behavior.

### E2 — WooCommerce Email Customizer
Official evidence:
- https://woocommerce.com/document/email-customizer-for-woocommerce/

Verified: visual drag/drop email composition with text, image, button, social, divider, columns and commerce-oriented elements plus desktop/mobile preview.

### E3 — YayMail
Official evidence:
- https://yaycommerce.com/yaymail-woocommerce-email-customizer/
- https://yaycommerce.com/yaymail-4-0/

Verified current capabilities: drag/drop templates, reusable blocks/patterns, global header/footer, branding, dynamic shortcodes/data, conditional elements, preview/test, template history/restore, transfer/backup and broad third-party integrations.

## 3. Record reconciliation

| Record | Disposition |
|---|---|
| `emails.template.identity` | MARKET_EVIDENCED — reusable/event-specific email templates and template history are mainstream. |
| `emails.metadata.message` | MARKET_EVIDENCED — subject/content/type/template settings exist; WPE retains explicit preheader/locale/plaintext semantics. |
| `emails.layout.shell` | MARKET_EVIDENCED — E3 global header/footer and reusable layout prove shell family. |
| `emails.brand.tokens` | MARKET_EVIDENCED — E1–E3 expose sender/branding/global styles. |
| `emails.block.structure` | MARKET_EVIDENCED — sections/columns/text/headings are visual-editor parity. |
| `emails.block.actions-media` | MARKET_EVIDENCED — buttons/images/dividers/media are established blocks. |
| `emails.block.dynamic` | MARKET_EVIDENCED_WITH_SAFE_PROVIDER_BOUNDARY — dynamic/conditional/reusable commerce elements exist; only bounded registered providers/tokens are accepted. |
| `emails.token.schema` | MARKET_EVIDENCED / WPE_HARDENED — shortcodes/dynamic data are common; WPE normalizes typed tokens/null fallback. |
| `emails.locale.plaintext` | KEEP_WPE_HARD — providers vary; deterministic locale fallback/plaintext generation remains explicit WPE policy. |
| `emails.override.wordpress` | MARKET_EVIDENCED_COMPATIBILITY — Woo/WP event email customization proves reviewed event adapters. |
| `emails.override.third-party` | MARKET_EVIDENCED_COMPATIBILITY — E3 broad integrations prove certified adapter family. |
| `emails.sender.profile` | MARKET_EVIDENCED_WITH_PROVIDER_BOUNDARY — sender metadata is common; verification/credentials remain transport/Vault-owned. |
| `emails.attachment.policy` | KEEP_WPE_HARD — transport can attach files, but protected-file authorization/count/size/MIME policy stays explicit. |
| `emails.preview.diagnostics` | MARKET_EVIDENCED — E1–E3 expose preview/test flows; WPE may add deterministic preflight diagnostics. |
| `emails.portability.performance` | MARKET_EVIDENCED / WPE_HARDENED — E3 transfer/backup/history and Woo template diff support portability/revision family; secrets remain excluded and compile caching is WPE-owned. |
| `emails.safety.raw-executable` | KEEP `REJECTED_UNSAFE` — market HTML/code blocks do not justify arbitrary PHP/script or untrusted executable markup. |

Coverage: **16 / 16**. Unresolved research dispositions: **0**. Worker Bank/progress mutations: **0**.

## 4. WPE-exceed / safety

- Template definitions are secret-free; provider credentials/verifications are external references.
- Dynamic data uses typed allowlisted token/provider schemas with context escaping and predictable null fallback.
- Test/preview rendering must not become uncontrolled external delivery; sending remains a separately authorized operation.
- Protected attachments require resource authorization at delivery time; a filesystem path alone is insufficient authority.
- Raw PHP/script/untrusted executable HTML remains rejected regardless of competitor extensibility.

## 5. Supervisor integration requirements

A later Supervisor may add E1–E3 as market sources and classify template/layout/branding/block/dynamic/preview/compatibility families while retaining locale/plaintext, attachment, secret, provider and executable-content safety boundaries. `MARKET_AUDITED` requires exact-head Bank reconciliation and CI.

No email sending/provider mutation, `BANK_REVIEWED`, runtime/product certification, deployment or release is authorized.