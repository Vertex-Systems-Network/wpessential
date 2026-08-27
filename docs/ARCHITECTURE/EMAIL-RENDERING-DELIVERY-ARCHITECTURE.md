# WPEssential — Email Rendering & Delivery Architecture

Status: **Phase 0 paper architecture / no implementation authorized**  
Related: Emails Builder exhaustive spec, Notification architecture, Connections, Job Service, ADR-0014.

## 1. Core separation

WPEssential email is split into five domains:

1. **Template Definition** — editable product definition stored in Definition Repository.
2. **Compiled Email Representation** — validated immutable rendering descriptor for a published template revision.
3. **Render Context** — authorized recipient/event/entity data available for one render.
4. **Rendered Message** — HTML + plaintext + headers/attachment references produced deterministically.
5. **Delivery Attempt** — transport/provider operation owned by Notification/Delivery infrastructure.

A template being valid does not mean a message was sent. A transport accepting a message does not mean it reached an inbox.

## 2. No webpage-builder runtime in email

Email Builder does not execute Elementor/WPBakery/Visual Composer/browser component trees in recipients' mail clients.

The canonical email model is a restricted **Email Component AST** using approved blocks only:
- section/container;
- columns;
- text;
- heading;
- button;
- image;
- divider/spacer;
- list;
- bounded data table/repeater;
- logo/brand;
- social links;
- legal/preferences;
- typed token;
- conditional block;
- registered SDK email component.

No script, iframe, form, arbitrary JS, browser CSS runtime or arbitrary PHP template execution.

## 3. Publish-time compile

Publishing a Template revision runs static validation and produces a compiled descriptor containing:
- template UUID + immutable revision UUID;
- layout UUID/revision policy;
- renderer schema/profile version;
- block tree with normalized styles;
- token dependencies and types;
- data-provider/query dependencies;
- locale variants;
- required policy claims;
- attachment slot definitions;
- plaintext generation policy;
- sanitizer/render mode;
- diagnostics/warnings;
- content fingerprint.

Invalid or unknown block/token versions cannot silently publish.

Draft edits never mutate the compiled descriptor pinned by an already queued notification.

## 4. Render-time pipeline

Candidate pipeline:

1. load compiled descriptor;
2. verify template/layout revision compatibility;
3. construct authorized render context;
4. resolve typed tokens and bounded collection data;
5. evaluate conditions;
6. build semantic Email IR;
7. render email-safe HTML;
8. apply renderer-controlled CSS compatibility/inlining rules;
9. sanitize/validate final markup and URLs;
10. generate or validate plaintext alternative;
11. resolve authorized attachments/secure links;
12. enforce final message/attachment limits;
13. return Rendered Message + safe diagnostics;
14. hand delivery intent to Notification/transport.

Rendering must not mutate business state.

## 5. Email IR and renderer profile

The renderer consumes normalized Email IR rather than raw editor JSON.

Renderer profile version controls:
- supported blocks;
- supported style properties;
- table/layout strategy;
- mobile/responsive transforms;
- CSS inline/embedded handling;
- dark-mode hints where supported;
- accessibility markup;
- plaintext transform rules.

This profile is independently versioned so future renderer changes do not silently alter old compliance-sensitive messages.

## 6. HTML compatibility philosophy

WPE does not promise pixel-identical rendering across all mail clients.

The default profile favors conservative email-compatible markup and layout. Browser-only CSS features are not accepted merely because the admin preview supports them.

Compatibility-sensitive properties may be:
- supported;
- supported with warning;
- renderer-transformed;
- unsupported.

Unknown style input fails validation or is removed according to explicit schema rules; it is never passed through as arbitrary CSS.

## 7. Dynamic data security

Every token/data provider declares:
- value type;
- privacy class;
- HTML/text escaping mode;
- authorization resolver;
- allowed formatters;
- nullable/fallback behavior;
- collection maximum where relevant.

Explicitly excluded from generic token exposure:
- password/password hash;
- reset/activation internals;
- session tokens;
- Application Password hashes/secrets;
- OAuth/API secrets;
- Vault plaintext;
- raw protected user meta;
- arbitrary object dumps.

Entity data is authorized for the intended recipient/render context, not simply because the email job runs as an administrator process.

## 8. URL and action links

Links are typed as:
- static trusted URL;
- validated local URL;
- authorized context URL;
- registered single-purpose signed-link provider.

Generic tokens cannot expose reusable session/authentication secrets.

Password reset, email verification, preference/unsubscribe and protected-download links use their owning security flow/provider rather than a free-form token concatenation mechanism.

## 9. Attachments and private files

Attachment resolution occurs at send time and is policy checked.

Rules:
- maximum count and total bytes;
- allowed MIME/extension;
- filesystem/provider existence check;
- no arbitrary server path supplied by template author;
- private/protected file requires explicit authorization;
- large/private resources should prefer controlled expiring download links where appropriate;
- rendered/logged metadata never stores secret storage URLs long-term.

## 10. Headers and sender safety

Template authors do not receive a free-form raw header editor.

Typed sender/message metadata includes:
- From profile;
- Reply-To;
- subject;
- recipient(s) resolved by Notification;
- category/purpose;
- optional provider metadata through registered adapters.

Header newline/control-character injection is rejected by normalization.

CC/BCC are delivery-policy inputs, not hidden template recipients.

## 11. WordPress transport boundary

WordPress `wp_mail()` may be one transport adapter/fallback, but WPE normalizes its result as **accepted/processed by transport**, not Delivered.

Provider adapters may expose richer states only when evidence exists, such as:
- queued;
- provider accepted;
- provider rejected;
- delivered (provider event confirms);
- bounced;
- complained;
- suppressed.

Unknown provider status never becomes Delivered by optimism.

## 12. WordPress email overrides

Core WordPress mail overrides use a certified event-adapter registry where stable hooks/filters provide semantic context.

Do not globally capture and rewrite every `wp_mail()` message by string matching.

Each adapter declares:
- supported WordPress/event version assumptions;
- recipient ownership;
- available typed context;
- subject/body override scope;
- fallback behavior to original WordPress flow;
- whether the event contains security-sensitive one-time links.

If a core/third-party email cannot be safely reconstructed, mark it Partial/Unsupported.

## 13. Plaintext

Every HTML transactional template has a readable plaintext path unless a documented adapter requires otherwise.

Auto-generation is deterministic from Email IR, not HTML tag stripping alone.

Rules include:
- headings → readable text;
- buttons → label + URL;
- images → meaningful alt/link when relevant;
- data tables → readable rows;
- decorative blocks removed;
- conditional content preserved according to rendered branch.

Custom plaintext override pins its own revision content.

## 14. Localization

Published Template can contain locale variants.

Resolution order candidate:
1. explicitly specified business/event locale;
2. recipient user locale if configured;
3. site default locale;
4. template default.

Missing required translation falls back according to template policy and is visible in diagnostics; it must not silently select a random locale.

## 15. Tracking/privacy

Open/click tracking is off by default at platform level unless the site deliberately enables it for an appropriate message category/provider.

WPE does not claim open tracking accuracy due to client privacy proxies/image blocking.

Tracking settings are part of delivery policy, not hidden renderer behavior.

## 16. Determinism and audit

For each delivery, retain safe references needed to explain rendering:
- Template revision;
- Layout revision/profile;
- renderer profile version;
- locale;
- Notification occurrence/delivery attempt;
- safe dependency versions;
- render result hash/diagnostic state where useful.

Do not retain full sensitive rendered bodies indefinitely by default.

## 17. Preview and test-send

Preview uses a declared sample context and clearly distinguishes:
- editor/browser preview;
- generated HTML source;
- plaintext;
- optional external mail-client rendering adapter later.

Test send:
- uses explicit test recipient;
- does not mutate production workflow state;
- logs as test;
- cannot bypass token/data authorization merely because caller has template-edit permission.

## 18. Failure/degraded behavior

Possible failures:
- missing/unsupported Template revision;
- missing layout/token/provider;
- denied token data;
- query budget exceeded;
- malformed URL;
- sanitizer/renderer error;
- attachment unavailable/unauthorized;
- transport unavailable;
- provider rate limit;
- Pro entitlement editing restrictions.

Rendering failure prevents send and produces structured safe error. It does not fall back to an unsafe unvalidated raw body.

## 19. Performance

- compiled published descriptors cached by immutable revision;
- bounded query/repeater results;
- batch recipient data where possible;
- no frontend builder runtime/assets;
- large delivery sets use Job Service;
- renderer cache must not mix recipient-specific output across principals.

## 20. Future executable evidence — NOT AUTHORIZED

After explicit consent:
- choose/evaluate renderer/CSS inliner dependency or local implementation;
- Gmail/Outlook/Apple/webmail fixture matrix;
- localization/RTL tests;
- XSS/URL/header injection tests;
- large HTML/attachment budgets;
- WordPress core-event adapter tests;
- transport/provider status truth tests;
- deterministic revision snapshots;
- bundle/dependency/license review.

## Paper recommendation

Accept the architecture principle:

**Template Definition → Compiled Email Descriptor → Authorized Render Context → Email IR → HTML + Plaintext Rendered Message → Notification Delivery Attempt**

The exact rendering/inliner library and provider adapters remain evidence-gated.