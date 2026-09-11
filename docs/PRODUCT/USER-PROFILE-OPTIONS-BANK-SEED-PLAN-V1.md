# User Profile — Options Bank Seed Plan V1

Surface: **14 / User Profile**
Issue: **#497**
Current Bank: **UNSEEDED / 0 records**
Atomic inventory: **ATOMIC_INVENTORY_COMPLETE**
Runtime implementation: **not authorized**

## Candidate families

- native and custom profile fields;
- avatar/cover/biography/social/contact data;
- field privacy and editability classifications;
- public profile slug, visibility, template and canonical URL;
- registration, verification and approval configuration;
- login/logout/reset surfaces that delegate to WordPress authority;
- directory query, filters, search, sort and pagination;
- role inclusion/exclusion and privacy-aware directory output;
- profile import/export and dependency mapping.

## Evidence pass

Audit current WordPress user/profile/registration/reset APIs and privacy/export behavior. Benchmark representative profile/directory/account products using official documentation. Separate authored profile settings from Fields, Roles, Membership and Listings ownership.

## Ownership boundaries

Fields owns reusable field definitions. Roles & Capabilities owns grants. Membership owns membership state. Listings/Query own reusable directory query/render engines. User Profile owns profile-specific schema composition and profile experience semantics.

## Promotion path

Seed records → native audit → market audit → ownership/semantic review → zero unresolved → `BANK_REVIEWED` → schema-valid option contract → reviewed UX/gap matrix. No runtime or certification claim is made by this plan.
