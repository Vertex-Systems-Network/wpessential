# WPEssential — Role & Capability Anti-Lockout / Recovery Contract

Status: **Phase 0 security architecture / no implementation authorized**  
Related: Role & Capability exhaustive spec, Policy, User/Profile security, multisite, ADR-0014.

## 1. Goal

Role & Capability Manager can change the authorization fabric of WordPress. A valid save operation can still be catastrophic if it removes all practical administrative recovery paths.

Therefore authorization mutation requires **impact analysis + anti-lockout rules + recoverable break-glass strategy**.

## 2. Capability truth

WPE treats capabilities as authoritative, not role names.

A role called `administrator` is not automatically equivalent to administrative power if capabilities were changed, and a custom role may become administrator-equivalent.

Object/meta capabilities are evaluated through WordPress mapping semantics rather than being treated as simple booleans.

## 3. Administrative-equivalent classifier

WPE maintains a risk classifier, not a universal claim that one capability defines admin equivalence.

Candidate high-risk capability families include:
- role/user promotion and management;
- plugin/theme installation/update/deletion;
- site/network settings;
- WPE role/capability management;
- WPE recovery/security settings;
- WPE reset/restore/destructive operations;
- Super Admin/network authority where applicable.

Classifier reports **risk/administrative reach**, not a fictional single hierarchy score.

## 4. Mutation classes

### Low
- add/remove ordinary content capability with no recovery impact.

### Medium
- capability change affecting editors/managers or broad content access.

### High
- role deletion;
- change to current operator's effective capabilities;
- change to role used by administrators;
- user role replacement;
- individual capability deny/allow on privileged user.

### Critical / anti-lockout
- mutation could remove every viable user able to manage WPE roles/capabilities/site administration;
- remove current operator's own recovery permissions;
- alter network/Super Admin-sensitive controls;
- delete role that is the only privileged role in use.

## 5. Impact preview

Before high/critical publish/apply, compute:
- role capability diff;
- users directly assigned role;
- users with multiple roles and resulting effective caps;
- individual user-cap overrides;
- current operator impact;
- count/list of remaining recovery-capable principals;
- multisite/network effect;
- WPE module/policy dependencies;
- dashboard/menu/access changes where inferable.

Preview has a fingerprint/version; execution rechecks if underlying role/user state changed.

## 6. Minimum recovery invariant

Normal UI must not knowingly commit a change leaving **zero viable administrative recovery principals** for the relevant site/network scope.

Candidate invariant checks:
- at least one authenticated WordPress principal remains able to manage core/WPE authorization according to accepted policy;
- current change does not unintentionally remove every user with required primitive capabilities;
- multisite honors Super Admin semantics rather than inventing site-role authority over network operations.

If the intended use case is deliberately removing all administrators, that is outside ordinary WPE Role Manager behavior and requires an explicit external recovery procedure, not a bypass checkbox.

## 7. Self-lockout protection

If actor's own effective access would be removed:
- show explicit before/after diff;
- require high-risk confirmation/recent authentication;
- verify at least one other recovery principal;
- optionally require second authorized principal for future enterprise mode, not v1 default;
- apply only after impact fingerprint recheck.

No silent save that immediately strands the operator.

## 8. Role deletion

Delete flow:
1. inspect assigned users;
2. identify default/new-user role implications;
3. inspect WPE policies/profile/dashboard references;
4. choose reassignment/removal behavior;
5. simulate effective capabilities after reassignment;
6. enforce recovery invariant;
7. confirm;
8. apply;
9. verify affected users/role no longer have stale assignments;
10. audit.

Core/built-in role deletion receives stricter advanced treatment and may be unsupported in initial release if recovery semantics are not proven.

## 9. Multi-role semantics

`add role`, `remove role`, and `replace roles` are distinct actions.

Replace-all is high-risk because it may discard unrelated integration roles.

Impact preview must use resulting effective capability set, including individual overrides, not only selected role label.

## 10. Explicit user capability overrides

User-specific allow/deny is advanced and stored separately from role assignments.

Rules:
- show source of capability (role vs user override);
- explicit deny distinguished from absence;
- high-risk override on privileged user requires confirmation;
- removing override restores inherited behavior, not necessarily deny;
- audit every override mutation.

## 11. Simulation

Effective capability simulator can accept:
- user;
- capability;
- optional object/context arguments;
- proposed role/cap diff.

Output:
- assigned roles;
- role entries;
- individual overrides;
- mapped primitive requirements where meaningful;
- multisite/Super Admin effect;
- final simulated result.

Simulation does not impersonate user or create a session.

## 12. Break-glass recovery layers

Recovery must not depend on a secret public URL.

### Layer A — normal UI recovery
Another authorized administrator/Super Admin can repair role/user capabilities.

### Layer B — native WordPress/WP-CLI recovery
Documented operator path using WordPress/WP-CLI APIs to restore a role/capability assignment when wp-admin authorization is damaged.

This is the strongest practical recovery when filesystem/CLI access exists and avoids building a weaker parallel authentication system.

### Layer C — WPE recovery mode constant
Candidate config constant such as `WPESSENTIAL_RECOVERY_MODE` may:
- disable WPE custom menu/route/profile restrictions;
- disable WPE Role Manager writes except explicit recovery screen;
- surface diagnostics;
- avoid loading broken WPE authorization overlays.

It **must not**:
- bypass WordPress login;
- mint Administrator/Super Admin authority;
- expose public recovery endpoint;
- disable core capability checks globally.

Recovery mode is for escaping WPE-induced UI/policy lockout, not repairing a fully destroyed WordPress role database without a privileged principal.

## 13. Emergency repair operation

If WPE later provides an explicit repair ability, it requires:
- WordPress-level privileged actor or authenticated CLI context;
- narrow operation (restore known WPE management caps/role assignment);
- impact preview;
- audit;
- no arbitrary role dump replacement;
- optional one-time recovery snapshot.

No anonymous recovery token endpoint.

## 14. Pre-change snapshot

Before critical role/capability mutation, store a compact authorization snapshot/reference:
- affected roles/caps;
- affected user assignments/overrides;
- timestamp/actor;
- schema/version;
- fingerprint.

Snapshot is not a substitute for full backup but can support targeted revert when semantics are proven.

Sensitive user data is minimized.

## 15. Rollback semantics

A rollback/revert operation must itself simulate current state because users/plugins may have changed capabilities since snapshot.

Modes candidate:
- restore exact WPE-managed changed entries if safe;
- generate reverse diff for review;
- abort on conflicting newer changes.

Never blindly overwrite all current role data with stale snapshot.

## 16. Multisite

Rules:
- distinguish network user, site role, and Super Admin;
- site admin cannot use WPE to create network/Super Admin authority;
- network role/capability changes require network scope and appropriate core authority;
- recovery invariant evaluated per affected site/network;
- current WordPress Super Admin semantics remain outer boundary.

## 17. Third-party role ownership

Unknown/plugin roles/capabilities are preserved.

WPE does not remove a capability because its source plugin is inactive or descriptor missing.

When editing third-party role:
- ownership warning;
- dependency impact;
- source plugin may re-register/change role on activation/update;
- optional compare/repair diagnostics.

## 18. UI confirmation levels

Candidate:
- Level 1: ordinary role/cap change;
- Level 2: high-impact role/user override;
- Level 3: role deletion, self-lockout risk, administrator-equivalent loss, bulk privileged mutation.

Level 3 includes typed confirmation phrase + recent auth + impact summary.

## 19. Audit

Record:
- actor;
- affected role/user;
- operation;
- before/after diff or fingerprint;
- affected-user count;
- risk level;
- recovery-invariant result;
- result/error;
- correlation ID.

Do not record credentials/session tokens.

## 20. Failure behavior

- impact scan fails → block high-risk mutation;
- stale fingerprint → require refresh/review;
- last recovery principal would be removed → block ordinary UI action;
- multisite authority insufficient → deny;
- post-apply verification mismatch → mark degraded and show recovery actions;
- WPE policy/menu broken → config recovery mode can bypass WPE overlay but not WordPress authorization.

## 21. Future executable evidence — NOT AUTHORIZED

After explicit consent:
- core roles/caps mutation fixtures;
- meta-cap/object checks;
- multi-role/individual deny cases;
- self-lockout and last-admin-equivalent scenarios;
- role deletion/reassignment;
- plugin-owned role changes;
- multisite/Super Admin;
- WP-CLI recovery documentation/fixtures;
- recovery-mode behavior;
- snapshot/reverse-diff conflict handling.

## Paper recommendation

Accept the security principle:

**WPE never treats a syntactically valid role/capability diff as safe until it proves the affected scope retains a viable recovery path. Primary break-glass recovery uses WordPress-authenticated/CLI authority, not a parallel anonymous backdoor.**