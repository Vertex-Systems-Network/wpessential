# Fields Runtime Binding and Reference Workflow V1

Status: implementation candidate  
Tracker: #86 / #66 Gate A  
Base: `main @ c47a79dd217b37154951775390bd5e436c44ec2b`

## Objective

Close the remaining Surface 3 runtime integration gap without changing the Free/Pro ownership boundary: an externally admitted `FieldsModule` must automatically bind canonical Published Field Groups to native WordPress post meta during the normal WordPress lifecycle, after compiled custom post types are available.

This slice also adds one composed real-WordPress reference workflow so Gate A does not rely only on individually certified components.

## Runtime ownership

The Free bootstrap continues to instantiate only Free modules. It contains no concrete `FieldsModule` reference.

A separately supplied add-on may, before WPEssential boot:

1. supply an explicit `ModuleActivationPolicyInterface` implementation;
2. contribute a concrete `FieldsModule` through `Plugin::registerModule()`.

The default activation policy still denies Pro modules. Therefore a denied `FieldsModule` never reaches `register()` or `boot()` and cannot install the Field runtime hook.

When explicitly admitted, `FieldsModule::register()` constructs the certified Field services and `FieldsModule::boot()` creates the Fields-owned `FieldGroupRuntimeRegistrar` service and registers its WordPress hook.

## Lifecycle ordering

Compiled Custom Post Type runtime registration is scheduled on WordPress `init` priority 20.

Fields post-meta runtime registration is scheduled on WordPress `init` priority 30.

This ordering is intentional: Field registration validates actual target subtype support (`custom-fields`, revisions where requested), so the target post type must already exist before the Fields runtime plan is evaluated.

The Fields runtime registrar is idempotent for one request. A repeated callback does not rebind the plan.

## Canonical runtime selection

The runtime coordinator reads `field_group` definitions from the shared `DefinitionRepositoryInterface`, then admits only definitions that are both:

- owned by canonical Surface 3 (`ownerSurfaceId = 3` through `FieldGroupDefinitionNormalizer::OWNER_SURFACE_ID`); and
- in `Published` lifecycle status.

Draft, Disabled, Archived, and foreign-owner definitions are not passed to native Field registration.

Accepted definitions are deterministically ordered by `(slug, id)` before planning. Deterministic ordering is diagnostic/reproducibility behavior only; it does not resolve collisions by winner selection.

## All-groups atomic preflight

`FieldGroupPostMetaBinder::bind()` remains available for focused single-definition callers.

The new `bindAll()` path compiles all supplied canonical Published groups into one combined registration list and submits that list once to `WordPressPostMetaRegistrar::registerBatch()`.

This matters for cross-group ownership safety. Duplicate `(post_type, meta_key)` tuples across two groups are discovered by registrar batch validation before any native `register_post_meta()` mutation. The system does not silently choose one group, remap a key, or partially register earlier groups.

The registrar's previously certified live per-tuple ownership revalidation remains authoritative immediately before possible native mutation.

## Runtime failure model

The coordinator catches runtime planning/registration failures so one invalid Field configuration does not fatally terminate WordPress initialization.

Failure is not silently treated as success:

- `processed()` records that the runtime pass ran;
- `bound()` remains empty when the combined plan fails;
- `errors()['runtime']` exposes the failure reason for diagnostics;
- batch fail-before-mutation semantics prevent partial Field registration for plan-level collisions.

No retry, key remap, provider fallback, or direct SQL bypass is introduced.

## Composed real-WordPress reference workflow

`tests/Integration/wordpress-fields-runtime-reference.php` is the seed/orchestration process. It uses real WordPress and the persistent shared repository. It:

1. installs/loads the WordPress fixture and administrator;
2. authorizes and contributes a concrete `FieldsModule` through the public pre-boot activation seam;
3. boots WPEssential;
4. creates and publishes a custom post type through the shared CPT Ability boundary;
5. creates and publishes a two-Field canonical Field Group through the shared Fields Ability boundary;
6. seeds a Draft group and a foreign-owner Published group as negative controls;
7. installs a temporary must-use contributor that uses the same public activation seam before plugin boot;
8. launches a fresh WordPress child request.

The fresh child (`wordpress-fields-runtime-reference-core.php`) allows the production lifecycle to run normally:

`mu-plugin contribution -> plugins_loaded -> Plugin::boot -> module lifecycle -> init:20 CPT -> init:30 Fields`.

In success mode it proves:

- the concrete Pro Fields module is admitted and reaches `Booted`;
- compiled CPT registration exists before Fields binding;
- the Fields runtime hook executes automatically;
- only the canonical Published Surface 3 group binds;
- both Field metadata definitions appear in native WordPress registered-meta state;
- REST-visible metadata shape is present where requested;
- group revision policy reaches native meta registration;
- native WordPress Field write/read Abilities operate against the automatically bound target;
- a second Field is writable through the shared internal Ability path;
- a non-target post fails closed and is not mutated.

The orchestration process then persists two Published canonical groups that intentionally reuse the same storage key on the same post type and launches a second fresh child request. In collision mode it proves:

- the runtime coordinator reports an inspectable collision error;
- the colliding key is not registered;
- Fields from the otherwise-valid earlier group are also absent, proving combined-plan fail-before-first-mutation behavior.

The composed reference never calls the production binder manually to establish automatic runtime behavior.

## Compatibility evidence target

The Platform Compatibility Matrix executes the composed reference on:

- WordPress 6.9 / PHP 8.2, 8.3, 8.4, 8.5 / MySQL 8.4;
- WordPress 7.1 / PHP 8.2, 8.3, 8.4, 8.5 / MySQL 8.4;
- WordPress 6.9 / PHP 8.4 / MariaDB 10.11;
- WordPress 7.1 / PHP 8.4 / MariaDB 10.11.

Exact-head Architecture Guards, PHP Quality Toolchain, Platform Compatibility Matrix, and Distributable Package remain authoritative before promotion.

## Non-goals

- no concrete `FieldsModule` reference in the Free bootstrap;
- no billing, licensing, entitlement provider, or ADR-0010/P-006 compatibility implementation;
- no provider-owned or Relations storage;
- no Query, Columns, Listings, or Status implementation;
- no production deployment/release;
- no arbitrary Field/group count limits;
- no direct SQL metadata writes;
- no cross-request Field registration cache.

## Gate A implication

Promotion of this slice does not by itself close #66. After merge, `CHECKPOINT.md` must be synchronized with all merged Surface 3 slices and Gate A must receive an explicit exit audit against its original acceptance criteria. Relations remains blocked until that audit closes Gate A.