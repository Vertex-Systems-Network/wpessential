# WPEssential — Access/Admin/Media/Code Multisite & AI Prompt Addendum

Status: **Phase 0 planning / no development authorization**  
Date: 2026-08-29

## 1. Scope

Covers newly accepted/expanded behaviors for:
- Membership Surface 15;
- Media Surface 28;
- Role & Capability Surface 30;
- proposed Surface 49 Admin Theme, Branding & Experience;
- proposed Surface 50 Safe Script, Tag & Code Injection.

This supplements existing 48-surface Multisite and AI Prompt matrices.

## 2. Membership additions

### Multisite
- Registration Flow is site-owned by default.
- Network may publish templates, but account identity can still be network-global while Membership remains site-scoped.
- Private-Site/Site Access profile can target one site and MUST NOT lock Network Admin or unrelated sites.
- role-sync affects target site role only unless a separately authorized network action exists.
- email verification/approval artifacts are site-bound.
- migration mapping resolves source site before creating Plans/Rules.
- site clone defaults to no live Enrollment, verification token, approval queue or member PII copy.

### AI Prompt
Allowed by default where policy permits:
- draft registration flow;
- draft restriction defaults;
- explain access;
- plan migration mapping;
- generate login/register/profile UI definitions.

High-risk/default-disabled:
- approve/reject registration;
- create/enroll users;
- activate site lockdown;
- role-sync mutation;
- migration apply;
- Entitlement grant/revoke.

## 3. Role additions

### Multisite
- Role Administration Policy is site-scoped unless explicitly network-defined.
- network role templates can dry-run/sync selected sites.
- local Administrator target-role policy cannot mutate Super Admin.
- rescue flow distinguishes site Administrator recovery from Super Admin recovery.
- role visibility/assignability is evaluated in target site's role registry.
- network sync reports missing/custom capability drift per site.

### AI Prompt
Allowed:
- draft role/cap diffs;
- compare roles;
- explain effective access;
- detect orphan capabilities;
- plan network sync.

High-risk/default-disabled:
- publish role mutation;
- target-role hierarchy change;
- rescue execution;
- Super Admin change;
- individual capability escalation;
- impersonation.

## 4. Media additions

### Multisite
- field metrics site-scoped;
- route/template fingerprints include site identity;
- network aggregate is explicit and privacy-minimized;
- shared CDN/offload connection is delegated without secret reveal;
- WPE derivatives remain linked to the owning site's attachment;
- network delivery templates can be instantiated/enforced only within certified controls;
- site deletion cleans site-owned metrics/derivatives without deleting shared external originals not owned by the site.

### AI Prompt
Allowed:
- analyze LCP/media diagnostics;
- draft delivery policy;
- recommend modern format profile;
- identify stale derivatives;
- explain Core/WPE ownership.

High-risk/default-disabled:
- enable field collection;
- publish site-wide loading rewrite;
- mass regeneration;
- cache/CDN purge;
- original-source replacement.

## 5. Surface 49 — Admin Theme / Branding

### Multisite
Default ownership: Site theme definition.

Network modes:
- template;
- default;
- enforced visual baseline;
- Network Admin theme separate from site admin theme.

Options:
- target sites;
- site override allowed/blocked;
- selected token groups locked;
- user preference allowed inside approved variants;
- new-site default;
- environment identity always re-resolved locally rather than copied blindly;
- network delete unlinks/reverts child assignments safely.

### AI Prompt
Allowed:
- draft palette/tokens;
- create variants;
- contrast audit;
- assignment impact preview;
- environment-cue proposal.

High-risk/default-disabled:
- network force/lock;
- mass user reassignment;
- login branding publish;
- compatibility override using raw CSS.

## 6. Surface 50 — Safe Script / Tag Manager

### Multisite
Default ownership: Site snippet.

Network modes:
- shared template;
- explicit rollout;
- network security floor;
- network-enforced snippet only under high-risk Network Admin policy.

Controls:
- target sites;
- environment rebind on clone;
- network allowed-origin floor;
- inline-JS allowed/forbidden policy;
- consent/CSP floor;
- site-specific parameter bindings;
- shared Vault reference without secret disclosure;
- emergency network pause;
- no cross-site client data sharing by implication.

### AI Prompt
Allowed:
- draft HTML/CSS/JS/JSON-LD/external-tag definition;
- convert raw vendor instructions into typed Draft;
- explain conditions;
- CSP/consent conflict audit;
- migration draft from simple header/footer scripts.

High-risk/default-disabled:
- publish browser code;
- admin/login scope injection;
- network rollout;
- CSP/security-header change;
- consent-category weakening;
- emergency pause/resume;
- any PHP/server-code generation for runtime application.

## 7. Combined product mapping

After acceptance of Surfaces 49 and 50:
- current planned surfaces: **50**;
- logical Multisite product mapping: **50/50**;
- module-wide AI Prompt product mapping: **50/50**;
- runtime Multisite certifications: unchanged / zero where not explicitly executed;
- AI/MCP runtime certifications: unchanged / zero.

## 8. Critical invariant

Multisite scope and AI Prompt availability are product contracts only. They do not grant development authorization, runtime certification, cross-site authority or the right for AI/MCP to execute protected mutations.