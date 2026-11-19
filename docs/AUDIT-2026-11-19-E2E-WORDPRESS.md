# End-to-end audit on real WordPress — 2026-11-19

Status: **VERIFIED IN A BROWSER**. Every Free and Pro module was exercised in a real WordPress
6.9 / PHP 8.3 site (wp-env) with Elementor 4.4, WooCommerce 11.0 and Contact Form 7 6.1.5, Free +
Pro active, Pro on the local trial entitlement. The run is repeatable: `.github/workflows/wp-env-audit.yml`
(Free) and the Pro repository's workflow of the same name run the same specs in CI.

Compared with [AUDIT-2026-11-16-FREE-PRO-MARKET.md](AUDIT-2026-11-16-FREE-PRO-MARKET.md): what that
audit calls foundation, skeleton or not started is reported here as **NOT BUILT**, not FAIL.

## Result

| | |
|---|---|
| PASS | 176 |
| FAIL | 0 (after the fixes below) |
| NOT BUILT | 87 (61 Custom Fields types marked "unavailable in V1", 23 modules with an "Under development" / "Coming soon" stage, Query execution, Custom Tables create, Fields edit-screen meta box) |
| debug.log | 0 fatals, 0 warnings from WPEssential; 1 notice (below) |

## Bugs found and fixed

| Where | Bug | Fix |
|---|---|---|
| Pro bootstrap | Fields, Relations, Status, Admin Columns and Listings were never registered, so **Open builder** answered "Sorry, you are not allowed to access this page". | Registered in `wpessential-pro.php`. |
| Pro builders | Query, Custom Tables, Fields and Admin Columns loaded their scripts from the Free plugin's folder, so the builder pages were empty. | Pro modules read Pro's own built assets. |
| Dashboard Widgets | Switching **Query** off took the whole site down (fatal: `module.query.read-consumer` not registered). | Declares its `query` dependency; it now waits instead of failing. |
| Custom Fields | Two published groups using the same storage key made **every** field group on the site disappear. | The first group keeps the key; the later one is skipped with a reason. |
| Admin UI | Icons used HTML entities and glyphs (`&rarr;`, `« ‹ › »`). | Shared SVG icon set. |

## Not WPEssential (environment)

- WordPress auto-updated core to 7.1.5 in the middle of a run and showed "Briefly unavailable for scheduled maintenance". The wp-env configs now set `AUTOMATIC_UPDATER_DISABLED`.
- Elementor bundles an old MCP Adapter copy that logs a deprecation notice; WooCommerce loads its translations early. Both filtered from the log check.

## Open notice

- `_load_textdomain_just_in_time` for the `wpessential` domain is logged while a taxonomy is saved: a string is translated before `init`. Harmless, but should be traced and moved after `init`.

## Options Bank seeding (AI-Native plan)

29 of 56 surfaces are seeded and reviewed, each with a native and/or market audit file that cites
providers and URLs (`config/product/options-bank-audits/`). Two modules that ship a working runtime
have **no Bank seed**: Site Kit (surface 49 `admin-theme`) and AI Connect (surface 43 `ai`). Surfaces
11–21 hold only 15–18 records each, far fewer than their market references expose. The other 25
surfaces are `UNSEEDED`.

## Every checked option

Screenshots are in the CI artifact `wp-env-audit-<run id>` under `test-results/audit/`.

PASS 176 · FAIL 0 · NOT BUILT 87 · log notes 1

| Module | Option | Status | Error | Screenshot |
|---|---|---|---|---|
| custom-post-types | create with every option set | PASS |  | `custom-post-types--create-with-every-option-set.png` |
| custom-post-types | every value survives a reload | PASS |  | `custom-post-types--every-value-survives-a-reload.png` |
| custom-post-types | edit and save again | PASS |  | `custom-post-types--edit-and-save-again.png` |
| custom-post-types | publish | PASS |  | `custom-post-types--publish.png` |
| custom-post-types | registered in WordPress after publish (REST) | PASS |  | `custom-post-types--registered-in-wordpress-after-publish-rest.png` |
| custom-post-types | disable, trash and delete permanently | PASS |  | `custom-post-types--disable-trash-and-delete-permanently.png` |
| taxonomies | create with every option set | PASS |  | `taxonomies--create-with-every-option-set.png` |
| taxonomies | every value survives a reload | PASS |  | `taxonomies--every-value-survives-a-reload.png` |
| taxonomies | edit and save again | PASS |  | `taxonomies--edit-and-save-again.png` |
| taxonomies | publish | PASS |  | `taxonomies--publish.png` |
| taxonomies | registered in WordPress after publish (REST) | PASS |  | `taxonomies--registered-in-wordpress-after-publish-rest.png` |
| taxonomies | disable, trash and delete permanently | PASS |  | `taxonomies--disable-trash-and-delete-permanently.png` |
| debug.log | taxonomies: create, reload, edit, front end, delete | NOTE | PHP Notice: Function _load_textdomain_just_in_time was called <strong>incorrectly</strong>. Translation loading for the <code>wpessential</code> domain was triggered too early. This is usually an indicator for some code in the plugin or theme running too early. Translations should be loaded at the  |  |
| import-export | export every part | PASS |  | `import-export--export-every-part.png` |
| import-export | upload and preview | PASS |  | `import-export--upload-and-preview.png` |
| import-export | import after preview | PASS |  | `import-export--import-after-preview.png` |
| import-export | reject a non-export file | PASS |  | `import-export--reject-a-non-export-file.png` |
| reset | cleanup: expired_transients | PASS |  | `reset--cleanup-expired-transients.png` |
| reset | cleanup: all_transients | PASS |  | `reset--cleanup-all-transients.png` |
| reset | cleanup: revisions | PASS |  | `reset--cleanup-revisions.png` |
| reset | cleanup: auto_drafts | PASS |  | `reset--cleanup-auto-drafts.png` |
| reset | cleanup: trashed_posts | PASS |  | `reset--cleanup-trashed-posts.png` |
| reset | cleanup: spam_comments | PASS |  | `reset--cleanup-spam-comments.png` |
| reset | cleanup: orphaned_postmeta | PASS |  | `reset--cleanup-orphaned-postmeta.png` |
| reset | cleanup: theme_mods | PASS |  | `reset--cleanup-theme-mods.png` |
| reset | cleanup: widgets | PASS |  | `reset--cleanup-widgets.png` |
| reset | cleanup needs the acknowledgement | PASS |  | `reset--cleanup-needs-the-acknowledgement.png` |
| reset | reset needs RESET typed | PASS |  | `reset--reset-needs-reset-typed.png` |
| reset | reset settings and definitions (restore point first) | PASS |  | `reset--reset-settings-and-definitions-restore-point-first.png` |
| reset | restore the restore point | PASS |  | `reset--restore-the-restore-point.png` |
| rest-api | defaults: posts public, users visible | PASS |  | `rest-api--defaults-posts-public-users-visible.png` |
| rest-api | hide the user list → 401 | PASS |  | `rest-api--hide-the-user-list-401.png` |
| rest-api | signed-in users only → 401 | PASS |  | `rest-api--signed-in-users-only-401.png` |
| rest-api | plugin exception stays public (Contact Form 7) | PASS |  | `rest-api--plugin-exception-stays-public-contact-form-7.png` |
| rest-api | custom route exception | PASS |  | `rest-api--custom-route-exception.png` |
| rest-api | switch off a namespace → 404 | PASS |  | `rest-api--switch-off-a-namespace-404.png` |
| rest-api | rate limit → 429 | PASS |  | `rest-api--rate-limit-429.png` |
| rest-api | rate-limit exempt IP | PASS |  | `rest-api--rate-limit-exempt-ip.png` |
| rest-api | hide REST discovery links | PASS |  | `rest-api--hide-rest-discovery-links.png` |
| rest-api | JSONP off | PASS |  | `rest-api--jsonp-off.png` |
| rest-api | XML-RPC off | PASS |  | `rest-api--xml-rpc-off.png` |
| rest-api | restricted role treated like a visitor → 403 | PASS |  | `rest-api--restricted-role-treated-like-a-visitor-403.png` |
| rest-api | blocked-request log | PASS |  | `rest-api--blocked-request-log.png` |
| ai-connect | switch on | PASS |  | `ai-connect--switch-on.png` |
| ai-connect | allow AI to change settings | PASS |  | `ai-connect--allow-ai-to-change-settings.png` |
| ai-connect | create a session (Other MCP client) | PASS |  | `ai-connect--create-a-session-other-mcp-client.png` |
| ai-connect | connection instructions: claude-ai | PASS |  | `ai-connect--connection-instructions-claude-ai.png` |
| ai-connect | connection instructions: chatgpt | PASS |  | `ai-connect--connection-instructions-chatgpt.png` |
| ai-connect | connection instructions: claude-code | PASS |  | `ai-connect--connection-instructions-claude-code.png` |
| ai-connect | connection instructions: claude-desktop | PASS |  | `ai-connect--connection-instructions-claude-desktop.png` |
| ai-connect | connection instructions: cursor | PASS |  | `ai-connect--connection-instructions-cursor.png` |
| ai-connect | connection instructions: vscode | PASS |  | `ai-connect--connection-instructions-vscode.png` |
| ai-connect | connection instructions: windsurf | PASS |  | `ai-connect--connection-instructions-windsurf.png` |
| ai-connect | connection instructions: gemini | PASS |  | `ai-connect--connection-instructions-gemini.png` |
| ai-connect | OAuth discovery URLs | PASS |  | `ai-connect--oauth-discovery-urls.png` |
| ai-connect | MCP without credentials → 401 | PASS |  | `ai-connect--mcp-without-credentials-401.png` |
| ai-connect | MCP initialize | PASS |  | `ai-connect--mcp-initialize.png` |
| ai-connect | MCP tools/list | PASS |  | `ai-connect--mcp-tools-list.png` |
| ai-connect | MCP tools/call is logged | PASS |  | `ai-connect--mcp-tools-call-is-logged.png` |
| ai-connect | activity log CSV download | PASS |  | `ai-connect--activity-log-csv-download.png` |
| ai-connect | disconnect sessions | PASS |  | `ai-connect--disconnect-sessions.png` |
| ai-connect | switch off | PASS |  | `ai-connect--switch-off.png` |
| Modules | list shows every module | PASS |  | `modules--list-shows-every-module.png` |
| Modules | edition and state filters | PASS |  | `modules--edition-and-state-filters.png` |
| custom-post-types | module page opens | PASS |  | `custom-post-types--module-page-opens.png` |
| custom-post-types | switch off then on | PASS |  | `custom-post-types--switch-off-then-on.png` |
| custom-post-types | Open builder | PASS |  | `custom-post-types--open-builder.png` |
| taxonomies | module page opens | PASS |  | `taxonomies--module-page-opens.png` |
| taxonomies | switch off then on | PASS |  | `taxonomies--switch-off-then-on.png` |
| taxonomies | Open builder | PASS |  | `taxonomies--open-builder.png` |
| custom-fields | product runtime | NOT BUILT | Stage tag on the module page: Under development |  |
| custom-fields | module page opens | PASS |  | `custom-fields--module-page-opens.png` |
| custom-fields | switch off then on | PASS |  | `custom-fields--switch-off-then-on.png` |
| custom-fields | Open builder | PASS |  | `custom-fields--open-builder.png` |
| relations | product runtime | NOT BUILT | Stage tag on the module page: Under development |  |
| relations | module page opens | PASS |  | `relations--module-page-opens.png` |
| relations | switch off then on | PASS |  | `relations--switch-off-then-on.png` |
| relations | Open builder | PASS |  | `relations--open-builder.png` |
| custom-tables | product runtime | NOT BUILT | Stage tag on the module page: Under development |  |
| custom-tables | module page opens | PASS |  | `custom-tables--module-page-opens.png` |
| custom-tables | switch off then on | PASS |  | `custom-tables--switch-off-then-on.png` |
| custom-tables | Open builder | PASS |  | `custom-tables--open-builder.png` |
| status | product runtime | NOT BUILT | Stage tag on the module page: Under development |  |
| status | module page opens | PASS |  | `status--module-page-opens.png` |
| status | switch off then on | PASS |  | `status--switch-off-then-on.png` |
| query | product runtime | NOT BUILT | Stage tag on the module page: Under development |  |
| query | module page opens | PASS |  | `query--module-page-opens.png` |
| query | switch off then on | PASS |  | `query--switch-off-then-on.png` |
| query | Open builder | PASS |  | `query--open-builder.png` |
| admin-columns | module page opens | PASS |  | `admin-columns--module-page-opens.png` |
| admin-columns | switch off then on | PASS |  | `admin-columns--switch-off-then-on.png` |
| admin-columns | Open builder | PASS |  | `admin-columns--open-builder.png` |
| admin-menu | product runtime | NOT BUILT | Stage tag on the module page: Under development |  |
| admin-menu | module page opens | PASS |  | `admin-menu--module-page-opens.png` |
| admin-menu | switch off then on | PASS |  | `admin-menu--switch-off-then-on.png` |
| dashboard-widgets | product runtime | NOT BUILT | Stage tag on the module page: Under development |  |
| dashboard-widgets | module page opens | PASS |  | `dashboard-widgets--module-page-opens.png` |
| dashboard-widgets | switch off then on | PASS |  | `dashboard-widgets--switch-off-then-on.png` |
| settings | product runtime | NOT BUILT | Stage tag on the module page: Under development |  |
| settings | module page opens | PASS |  | `settings--module-page-opens.png` |
| settings | switch off then on | PASS |  | `settings--switch-off-then-on.png` |
| roles | product runtime | NOT BUILT | Stage tag on the module page: Under development |  |
| roles | module page opens | PASS |  | `roles--module-page-opens.png` |
| roles | switch off then on | PASS |  | `roles--switch-off-then-on.png` |
| profiles | product runtime | NOT BUILT | Stage tag on the module page: Under development |  |
| profiles | module page opens | PASS |  | `profiles--module-page-opens.png` |
| profiles | switch off then on | PASS |  | `profiles--switch-off-then-on.png` |
| membership | product runtime | NOT BUILT | Stage tag on the module page: Under development |  |
| membership | module page opens | PASS |  | `membership--module-page-opens.png` |
| membership | switch off then on | PASS |  | `membership--switch-off-then-on.png` |
| dashboard | product runtime | NOT BUILT | Stage tag on the module page: Under development |  |
| dashboard | module page opens | PASS |  | `dashboard--module-page-opens.png` |
| dashboard | switch off then on | PASS |  | `dashboard--switch-off-then-on.png` |
| listings | product runtime | NOT BUILT | Stage tag on the module page: Under development |  |
| listings | module page opens | PASS |  | `listings--module-page-opens.png` |
| listings | switch off then on | PASS |  | `listings--switch-off-then-on.png` |
| site-kit | module page opens | PASS |  | `site-kit--module-page-opens.png` |
| site-kit | switch off then on | PASS |  | `site-kit--switch-off-then-on.png` |
| site-kit | Open builder | PASS |  | `site-kit--open-builder.png` |
| builder-widgets | product runtime | NOT BUILT | Stage tag on the module page: Under development |  |
| builder-widgets | module page opens | PASS |  | `builder-widgets--module-page-opens.png` |
| builder-widgets | switch off then on | PASS |  | `builder-widgets--switch-off-then-on.png` |
| pro-elements | module page opens | PASS |  | `pro-elements--module-page-opens.png` |
| pro-elements | switch off then on | PASS |  | `pro-elements--switch-off-then-on.png` |
| forms-workflows | product runtime | NOT BUILT | Stage tag on the module page: Under development |  |
| forms-workflows | module page opens | PASS |  | `forms-workflows--module-page-opens.png` |
| forms-workflows | switch off then on | PASS |  | `forms-workflows--switch-off-then-on.png` |
| notifications | product runtime | NOT BUILT | Stage tag on the module page: Under development |  |
| notifications | module page opens | PASS |  | `notifications--module-page-opens.png` |
| notifications | switch off then on | PASS |  | `notifications--switch-off-then-on.png` |
| emails | product runtime | NOT BUILT | Stage tag on the module page: Under development |  |
| emails | module page opens | PASS |  | `emails--module-page-opens.png` |
| emails | switch off then on | PASS |  | `emails--switch-off-then-on.png` |
| chat | product runtime | NOT BUILT | Stage tag on the module page: Under development |  |
| chat | module page opens | PASS |  | `chat--module-page-opens.png` |
| chat | switch off then on | PASS |  | `chat--switch-off-then-on.png` |
| cron | product runtime | NOT BUILT | Stage tag on the module page: Under development |  |
| cron | module page opens | PASS |  | `cron--module-page-opens.png` |
| cron | switch off then on | PASS |  | `cron--switch-off-then-on.png` |
| import-export | module page opens | PASS |  | `import-export--module-page-opens.png` |
| import-export | switch off then on | PASS |  | `import-export--switch-off-then-on.png` |
| import-export | Open builder | PASS |  | `import-export--open-builder.png` |
| reset | module page opens | PASS |  | `reset--module-page-opens.png` |
| reset | switch off then on | PASS |  | `reset--switch-off-then-on.png` |
| reset | Open builder | PASS |  | `reset--open-builder.png` |
| rest-api | module page opens | PASS |  | `rest-api--module-page-opens.png` |
| rest-api | switch off then on | PASS |  | `rest-api--switch-off-then-on.png` |
| rest-api | Open builder | PASS |  | `rest-api--open-builder.png` |
| backup | product runtime | NOT BUILT | Stage tag on the module page: Coming soon |  |
| backup | module page opens | PASS |  | `backup--module-page-opens.png` |
| backup | switch off then on | PASS |  | `backup--switch-off-then-on.png` |
| media | product runtime | NOT BUILT | Stage tag on the module page: Coming soon |  |
| media | module page opens | PASS |  | `media--module-page-opens.png` |
| media | switch off then on | PASS |  | `media--switch-off-then-on.png` |
| protector | product runtime | NOT BUILT | Stage tag on the module page: Coming soon |  |
| protector | module page opens | PASS |  | `protector--module-page-opens.png` |
| protector | switch off then on | PASS |  | `protector--switch-off-then-on.png` |
| connections | product runtime | NOT BUILT | Stage tag on the module page: Coming soon |  |
| connections | module page opens | PASS |  | `connections--module-page-opens.png` |
| connections | switch off then on | PASS |  | `connections--switch-off-then-on.png` |
| ai-connect | module page opens | PASS |  | `ai-connect--module-page-opens.png` |
| ai-connect | switch off then on | PASS |  | `ai-connect--switch-off-then-on.png` |
| ai-connect | Open builder | PASS |  | `ai-connect--open-builder.png` |
| custom-fields | field type: email | NOT BUILT | Email — unavailable in V1 |  |
| custom-fields | field type: url | NOT BUILT | URL — unavailable in V1 |  |
| custom-fields | field type: password | NOT BUILT | Password / Secret Reference — unavailable in V1 |  |
| custom-fields | field type: hidden | NOT BUILT | Hidden — unavailable in V1 |  |
| custom-fields | field type: range | NOT BUILT | Range / Slider — unavailable in V1 |  |
| custom-fields | field type: true_false | NOT BUILT | True / False — unavailable in V1 |  |
| custom-fields | field type: switcher | NOT BUILT | Switcher — unavailable in V1 |  |
| custom-fields | field type: checkbox_list | NOT BUILT | Checkbox List — unavailable in V1 |  |
| custom-fields | field type: radio | NOT BUILT | Radio — unavailable in V1 |  |
| custom-fields | field type: button_group | NOT BUILT | Button Set — unavailable in V1 |  |
| custom-fields | field type: select | NOT BUILT | Select — unavailable in V1 |  |
| custom-fields | field type: select_advanced | NOT BUILT | Advanced Select — unavailable in V1 |  |
| custom-fields | field type: autocomplete | NOT BUILT | Autocomplete — unavailable in V1 |  |
| custom-fields | field type: combobox | NOT BUILT | Combobox — unavailable in V1 |  |
| custom-fields | field type: text_list | NOT BUILT | Text List — unavailable in V1 |  |
| custom-fields | field type: key_value | NOT BUILT | Key / Value — unavailable in V1 |  |
| custom-fields | field type: advanced_date | NOT BUILT | Advanced Date / Range — unavailable in V1 |  |
| custom-fields | field type: wysiwyg | NOT BUILT | WYSIWYG — unavailable in V1 |  |
| custom-fields | field type: block_editor | NOT BUILT | Gutenberg / Block Content — unavailable in V1 |  |
| custom-fields | field type: code_editor | NOT BUILT | Code Editor — unavailable in V1 |  |
| custom-fields | field type: markdown | NOT BUILT | Markdown — unavailable in V1 |  |
| custom-fields | field type: oembed | NOT BUILT | oEmbed — unavailable in V1 |  |
| custom-fields | field type: link | NOT BUILT | Link — unavailable in V1 |  |
| custom-fields | field type: page_link | NOT BUILT | Page Link — unavailable in V1 |  |
| custom-fields | field type: image | NOT BUILT | Image — unavailable in V1 |  |
| custom-fields | field type: gallery | NOT BUILT | Gallery — unavailable in V1 |  |
| custom-fields | field type: file | NOT BUILT | File — unavailable in V1 |  |
| custom-fields | field type: file_advanced | NOT BUILT | Files — unavailable in V1 |  |
| custom-fields | field type: media | NOT BUILT | Media — unavailable in V1 |  |
| custom-fields | field type: video | NOT BUILT | Video — unavailable in V1 |  |
| custom-fields | field type: post_object | NOT BUILT | Post Object — unavailable in V1 |  |
| custom-fields | field type: posts | NOT BUILT | Posts — unavailable in V1 |  |
| custom-fields | field type: relationship | NOT BUILT | Relationship — unavailable in V1 |  |
| custom-fields | field type: taxonomy | NOT BUILT | Taxonomy — unavailable in V1 |  |
| custom-fields | field type: user | NOT BUILT | User — unavailable in V1 |  |
| custom-fields | field type: nav_menu | NOT BUILT | Navigation Menu — unavailable in V1 |  |
| custom-fields | field type: sidebar | NOT BUILT | Sidebar — unavailable in V1 |  |
| custom-fields | field type: group | NOT BUILT | Group — unavailable in V1 |  |
| custom-fields | field type: repeater | NOT BUILT | Repeater — unavailable in V1 |  |
| custom-fields | field type: flexible_content | NOT BUILT | Flexible Content — unavailable in V1 |  |
| custom-fields | field type: clone | NOT BUILT | Clone — unavailable in V1 |  |
| custom-fields | field type: heading | NOT BUILT | Heading — unavailable in V1 |  |
| custom-fields | field type: separator | NOT BUILT | Divider / Separator — unavailable in V1 |  |
| custom-fields | field type: tab | NOT BUILT | Tab — unavailable in V1 |  |
| custom-fields | field type: accordion | NOT BUILT | Accordion — unavailable in V1 |  |
| custom-fields | field type: message | NOT BUILT | Message — unavailable in V1 |  |
| custom-fields | field type: color | NOT BUILT | Color — unavailable in V1 |  |
| custom-fields | field type: gradient | NOT BUILT | Gradient — unavailable in V1 |  |
| custom-fields | field type: icon | NOT BUILT | Icon — unavailable in V1 |  |
| custom-fields | field type: image_select | NOT BUILT | Image Select — unavailable in V1 |  |
| custom-fields | field type: background | NOT BUILT | Background — unavailable in V1 |  |
| custom-fields | field type: dimensions | NOT BUILT | Dimensions — unavailable in V1 |  |
| custom-fields | field type: spacing | NOT BUILT | Spacing — unavailable in V1 |  |
| custom-fields | field type: border | NOT BUILT | Border — unavailable in V1 |  |
| custom-fields | field type: box_shadow | NOT BUILT | Box Shadow — unavailable in V1 |  |
| custom-fields | field type: typography | NOT BUILT | Typography — unavailable in V1 |  |
| custom-fields | field type: palette | NOT BUILT | Palette — unavailable in V1 |  |
| custom-fields | field type: phone | NOT BUILT | Phone — unavailable in V1 |  |
| custom-fields | field type: currency | NOT BUILT | Currency — unavailable in V1 |  |
| custom-fields | field type: unit | NOT BUILT | Unit Value — unavailable in V1 |  |
| custom-fields | field type: angle | NOT BUILT | Angle — unavailable in V1 |  |
| custom-fields | field type: text (every setting, save, reload) | PASS |  | `custom-fields--field-type-text-every-setting-save-reload.png` |
| custom-fields | field type: textarea (every setting, save, reload) | PASS |  | `custom-fields--field-type-textarea-every-setting-save-reload.png` |
| custom-fields | field type: number (every setting, save, reload) | PASS |  | `custom-fields--field-type-number-every-setting-save-reload.png` |
| custom-fields | field type: checkbox (every setting, save, reload) | PASS |  | `custom-fields--field-type-checkbox-every-setting-save-reload.png` |
| custom-fields | field type: date (every setting, save, reload) | PASS |  | `custom-fields--field-type-date-every-setting-save-reload.png` |
| custom-fields | field type: time (every setting, save, reload) | PASS |  | `custom-fields--field-type-time-every-setting-save-reload.png` |
| custom-fields | field type: datetime (every setting, save, reload) | PASS |  | `custom-fields--field-type-datetime-every-setting-save-reload.png` |
| custom-fields | publish a group placed on posts | PASS |  | `custom-fields--publish-a-group-placed-on-posts.png` |
| custom-fields | meta box on the post edit screen | NOT BUILT | docs/AUDIT-2026-11-16-FREE-PRO-MARKET.md: post meta is registered, no meta box on the edit screen yet. |  |
| custom-fields | meta registered; value saved through REST | PASS |  | `custom-fields--meta-registered-value-saved-through-rest.png` |
| relations | create with every option set | PASS |  | `relations--create-with-every-option-set.png` |
| relations | every value survives a reload | PASS |  | `relations--every-value-survives-a-reload.png` |
| relations | publish and manage a connection | PASS |  | `relations--publish-and-manage-a-connection.png` |
| admin-columns | create with every option set | PASS |  | `admin-columns--create-with-every-option-set.png` |
| admin-columns | reopen the saved Column Set | PASS |  | `admin-columns--reopen-the-saved-column-set.png` |
| admin-columns | columns appear on the Posts list | PASS |  | `admin-columns--columns-appear-on-the-posts-list.png` |
| query | author a query definition | PASS |  | `query--author-a-query-definition.png` |
| query | save / preview execution | NOT BUILT | Screen states execution preview is unavailable in this tranche; no save action exists. |  |
| custom-tables | screen opens | PASS |  | `custom-tables--screen-opens.png` |
| custom-tables | create table definition | NOT BUILT | Screen lists definitions only ("No Custom Tables definitions exist yet"); no create form. |  |
| site-kit | tab: Global colours19 (save, reload) | PASS |  | `site-kit--tab-global-colours19-save-reload.png` |
| site-kit | tab: Dark mode21 (save, reload) | PASS |  | `site-kit--tab-dark-mode21-save-reload.png` |
| site-kit | tab: Typography26 (save, reload) | PASS |  | `site-kit--tab-typography26-save-reload.png` |
| site-kit | tab: Global typography (save, reload) | PASS |  | `site-kit--tab-global-typography-save-reload.png` |
| site-kit | tab: Site background2 (save, reload) | PASS |  | `site-kit--tab-site-background2-save-reload.png` |
| site-kit | tab: Buttons11 (save, reload) | PASS |  | `site-kit--tab-buttons11-save-reload.png` |
| site-kit | tab: Form fields6 (save, reload) | PASS |  | `site-kit--tab-form-fields6-save-reload.png` |
| site-kit | tab: Corners & images6 (save, reload) | PASS |  | `site-kit--tab-corners-images6-save-reload.png` |
| site-kit | tab: Layout6 (save, reload) | PASS |  | `site-kit--tab-layout6-save-reload.png` |
| site-kit | tab: Gradients5 (save, reload) | PASS |  | `site-kit--tab-gradients5-save-reload.png` |
| site-kit | tab: Links6 (save, reload) | PASS |  | `site-kit--tab-links6-save-reload.png` |
| site-kit | tab: Shadows2 (save, reload) | PASS |  | `site-kit--tab-shadows2-save-reload.png` |
| site-kit | tab: Header & footer12 (save, reload) | PASS |  | `site-kit--tab-header-footer12-save-reload.png` |
| site-kit | tab: Accessibility4 (save, reload) | PASS |  | `site-kit--tab-accessibility4-save-reload.png` |
| site-kit | tab: Hover & motion4 (save, reload) | PASS |  | `site-kit--tab-hover-motion4-save-reload.png` |
| site-kit | tab: SEO basics1 (save, reload) | PASS |  | `site-kit--tab-seo-basics1-save-reload.png` |
| site-kit | tab: Custom code5 (save, reload) | PASS |  | `site-kit--tab-custom-code5-save-reload.png` |
| site-kit | front end renders the saved styles | PASS |  | `site-kit--front-end-renders-the-saved-styles.png` |
| pro-elements | Gutenberg blocks registered | PASS |  | `pro-elements--gutenberg-blocks-registered.png` |
| pro-elements | Elementor widgets registered | PASS |  | `pro-elements--elementor-widgets-registered.png` |
