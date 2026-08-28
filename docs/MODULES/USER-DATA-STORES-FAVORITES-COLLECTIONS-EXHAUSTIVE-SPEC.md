# WPEssential — User Data Stores, Favorites & Collections

Status: **Phase 0 exhaustive planning / no development authorization**  
Edition: **Pro**  
Surface: **54**

## 1. Purpose

Provide reusable per-user/guest collections such as favorites, wishlists, bookmarks, compare lists, recently viewed, save-for-later and arbitrary typed collections. This closes the JetEngine Data Stores class of capability without coupling it to one listing builder.

## 2. Core concepts

- Store Definition;
- Store Entry;
- Subject/Owner;
- Target Entity;
- Entry Metadata;
- Ordering;
- Retention/Expiry;
- Guest Identity;
- Merge/Reconciliation Policy.

A store is not authorization and not a substitute for Membership, cart/order or ledger truth.

## 3. Screens

- Stores
- Store Editor
- Entries Explorer
- Guest Merge / Diagnostics
- Limits & Retention
- Integrations
- Import / Export
- Privacy
- Revisions
- Settings

## 4. Store types/presets

- Favorites;
- Wishlist;
- Bookmarks;
- Compare;
- Save for later;
- Recently viewed;
- Reading/listening queue;
- Custom collection.

Presets only preconfigure options; all resolve to one canonical store contract.

## 5. Target entity types

- posts/CPTs;
- products through Woo adapter;
- terms;
- users only where privacy/policy permits;
- CCT/custom-table records;
- relation-backed entities;
- remote entities only through stable provider identity adapter.

## 6. Ownership modes

- authenticated user;
- guest browser/session pseudonymous store;
- team/shared collection through explicit Policy;
- site-level shared curated collection;
- network shared collection only where explicitly authorized.

Guest storage may use signed cookie/local storage plus server persistence according profile; no secret/private data may be stored client-side.

## 7. Entry operations

- add;
- remove;
- toggle;
- clear;
- move/reorder;
- copy/move between stores;
- annotate/note;
- set quantity-like metadata only for collection semantics, not commerce inventory;
- set custom typed entry metadata;
- bulk import/export.

Idempotent add/remove semantics are required.

## 8. Limits

- max entries;
- max entries per target type;
- per-user/team limits;
- guest limits;
- duplicate policy;
- oldest/newest eviction;
- explicit fail when full;
- retention/expiry;
- recently-viewed rolling window;
- rate limits.

## 9. Guest → user merge

On authoritative login/registration:
- keep user store;
- merge unique;
- guest wins;
- user wins;
- prompt/plan where UI supports;
- max-limit conflict strategy;
- duplicate metadata merge rules;
- guest artifact invalidation after successful merge.

Never merge stores across users solely because they share a device/browser.

## 10. Query / listing integration

Expose typed source/filter:
- items in current user's store;
- item membership boolean/count where allowed;
- store entry order;
- entry metadata;
- stores containing current item under allowed scope.

Query Builder gets a dedicated Store Query provider rather than raw SQL.

Dynamic Listings support:
- Add/Remove/Toggle action;
- membership state;
- collection count;
- store list selector;
- empty state;
- compare/favorites views.

## 11. REST / Abilities

- list available stores;
- get own store entries;
- add/remove/toggle;
- reorder;
- clear;
- import/export plan;
- admin inspect only with Policy.

Public endpoints require authentication/nonce/token strategy appropriate to context and rate limiting.

## 12. Privacy

- store contents may reveal interests/health/religion/etc. depending target data, so definitions carry sensitivity classification;
- export/erase integration;
- guest identifier rotation/expiry;
- no cross-user cache leakage;
- analytics event collection separately consented;
- admin browsing of user stores separately permissioned/audited.

## 13. Commerce boundary

Wishlist/Save-for-later can reference Woo products but:
- Store Entry ≠ Cart Line;
- Store Entry ≠ Reservation/Hold;
- Store Entry ≠ Inventory allocation;
- Store Entry ≠ Order.

Transfer to cart is an explicit Woo adapter action with its own validation.

## 14. Shared/team collections

Optional profiles:
- owner;
- editors/viewers;
- invite/share policy;
- revision/audit;
- concurrent update handling;
- public share link only for stores explicitly configured public and sanitized.

## 15. Import / migration

Detect/import recognized favorites/wishlist/data-store plugins through adapters. Workflow:
`detect → map store → map entity identities → preview orphan entries → dry run → import → verify`.

## 16. AI

AI may create store definitions, suggest limits and summarize aggregate collection trends only when privacy policy allows. It must not inspect protected personal collections outside actor scope or infer sensitive traits as facts.

## 17. Multisite

- user identity may be network-global but store ownership is site-scoped by default;
- network store requires explicit definition;
- site deletion follows retention policy;
- site clone does not copy live personal store entries by default;
- cross-site product/content identity requires adapter mapping.

## 18. MUST NOT

- no use of favorites as authorization;
- no cross-user cache leakage;
- no silent guest-store merge to wrong account;
- no cart/order/inventory semantics inside generic store;
- no raw unbounded client-side store for protected data;
- no remote entity without stable identity mapping.

## 19. Evidence

Reserved namespace: **UDS-001…UDS-176**, executed **0/176**.

Evidence groups cover store schema, add/remove idempotency, ordering, limits/expiry, guest identity, merge, privacy/export/erase, query/listing, Woo boundaries, shared stores, migration, Multisite, concurrency, scale and golden favorites/wishlist/compare scenarios.