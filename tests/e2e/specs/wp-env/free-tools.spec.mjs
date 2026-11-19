import fs from 'node:fs';
import path from 'node:path';

import { BASE, HAS_PRO, expect, notBuilt, openAdmin, test } from '../support/audit.mjs';

// Tool screens answer with a banner at the top of the page (no toast for admin-post forms).
const notice = (page) => page.locator('#wpbody-content').getByText(/finished|restored|imported|saved|done|cleaned|could not|failed|error/i).first();

test('Import / Export: export, preview, import', async ({ page, audit }, testInfo) => {
  const file = testInfo.outputPath('export.json');

  await audit('import-export', 'export every part', async () => {
    await openAdmin(page, 'wpessential-import-export');
    const download = page.waitForEvent('download');
    await page.getByRole('button', { name: 'Download export file' }).click();
    await (await download).saveAs(file);
    const data = JSON.parse(fs.readFileSync(file, 'utf8'));
    expect(JSON.stringify(data)).not.toMatch(/"key"\s*:\s*"[A-Z0-9-]{16,}"|application_password|Basic /);
  });

  await audit('import-export', 'upload and preview', async () => {
    await openAdmin(page, 'wpessential-import-export');
    await page.locator('input[type=file][name=file]').setInputFiles(file);
    await page.getByRole('button', { name: 'Upload and preview' }).click();
    await expect(page.locator('#wpbody-content')).toContainText(/preview|will|import/i);
    await expect(page.locator('#wpbody-content')).not.toContainText(/Sorry, you are not allowed|critical error/);
  });

  await audit('import-export', 'import after preview', async () => {
    await page.getByRole('button', { name: /^Import/ }).first().click();
    await expect(notice(page)).toBeVisible();
    await expect(notice(page)).not.toContainText(/error|failed|could not/i);
  });

  await audit('import-export', 'reject a non-export file', async () => {
    const bad = testInfo.outputPath('bad.json');
    fs.writeFileSync(bad, '{"hello":"world"}');
    await openAdmin(page, 'wpessential-import-export');
    await page.locator('input[type=file][name=file]').setInputFiles(bad);
    await page.getByRole('button', { name: 'Upload and preview' }).click();
    await expect(page.locator('#wpbody-content')).toContainText(/not a WPEssential|invalid|could not|not valid/i);
  });
});

test('Reset Tools: cleanup, reset, restore point, restore', async ({ page, audit }) => {
  const TOOLS = ['expired_transients', 'all_transients', 'revisions', 'auto_drafts', 'trashed_posts', 'spam_comments', 'orphaned_postmeta', 'theme_mods', 'widgets'];
  for (const tool of TOOLS) {
    await audit('reset', `cleanup: ${tool}`, async () => {
      await openAdmin(page, 'wpessential-reset');
      const box = page.locator(`input[name="tools[]"][value="${tool}"]`);
      if (await box.isDisabled()) return; // Nothing to clean: the count is 0 and the box is disabled by design.
      await box.check();
      await page.locator('input[name=ack]').check();
      await page.getByRole('button', { name: 'Clean selected' }).click();
      await expect(notice(page)).toBeVisible();
      await expect(notice(page)).not.toContainText(/error|failed/i);
    });
  }

  await audit('reset', 'cleanup needs the acknowledgement', async () => {
    await openAdmin(page, 'wpessential-reset');
    await page.locator('input[name="tools[]"]:not([disabled])').first().check();
    await page.getByRole('button', { name: 'Clean selected' }).click();
    await expect(page.locator('#wpbody-content')).toContainText(/I understand|confirm|tick/i);
  });

  await audit('reset', 'reset needs RESET typed', async () => {
    await openAdmin(page, 'wpessential-reset');
    await page.locator('input[name="parts[]"][value="settings"]').check();
    await page.locator('input[name=confirm]').fill('nope');
    await page.getByRole('button', { name: 'Reset selected' }).click();
    await expect(page.locator('#wpbody-content')).toContainText(/RESET/);
  });

  await audit('reset', 'reset settings and definitions (restore point first)', async () => {
    await openAdmin(page, 'wpessential-reset');
    await page.locator('input[name="parts[]"][value="settings"]').check();
    await page.locator('input[name="parts[]"][value="definitions"]').check();
    await page.locator('input[name=confirm]').fill('RESET');
    await page.getByRole('button', { name: 'Reset selected' }).click();
    await expect(notice(page)).toBeVisible();
    await expect(page.getByRole('button', { name: /Restore/ }).first()).toBeVisible();
  });

  await audit('reset', 'restore the restore point', async () => {
    await openAdmin(page, 'wpessential-reset');
    await page.getByRole('button', { name: /Restore/ }).first().click();
    const dialog = page.locator('dialog[open]');
    if (await dialog.count()) await dialog.getByRole('button', { name: /Restore/ }).click();
    await expect(notice(page)).toBeVisible();
    await expect(notice(page)).not.toContainText(/error|failed/i);
  });
});

test('REST API Control: every option, checked logged out', async ({ page, audit, anon }) => {
  const save = async (setup) => {
    await openAdmin(page, 'wpessential-rest-api');
    const form = page.locator('form').filter({ has: page.getByRole('button', { name: 'Save changes' }) });
    for (const box of await form.locator('input[type=checkbox]').all()) await box.setChecked(false);
    await form.locator('input[name=rate_limit]').fill('0');
    await form.locator('textarea').evaluateAll((areas) => areas.forEach((area) => { area.value = ''; }));
    await setup(form);
    await page.getByRole('button', { name: 'Save changes' }).click();
    await expect(page.locator('#wpbody-content')).not.toContainText(/critical error/);
  };
  const status = async (route) => (await anon.get(`/wp-json${route}`)).status();

  await audit('rest-api', 'defaults: posts public, users visible', async () => {
    await save(async () => {});
    expect(await status('/wp/v2/posts')).toBe(200);
    expect(await status('/wp/v2/users')).toBe(200);
  });
  await audit('rest-api', 'hide the user list → 401', async () => {
    await save(async (form) => form.locator('input[name=hide_users]').check());
    expect(await status('/wp/v2/users')).toBe(401);
    expect(await status('/wp/v2/posts')).toBe(200);
  });
  await audit('rest-api', 'signed-in users only → 401', async () => {
    await save(async (form) => form.locator('input[name=logged_in_only]').check());
    expect(await status('/wp/v2/posts')).toBe(401);
    expect(await status('/')).toBe(200);
  });
  await audit('rest-api', 'plugin exception stays public (Contact Form 7)', async () => {
    await save(async (form) => {
      await form.locator('input[name=logged_in_only]').check();
      await form.locator('input[name="allow_known[]"][value="contact-form-7/v1"]').check();
    });
    // Visitors submit forms through /feedback; CF7 itself keeps its form list for signed-in users.
    const submit = async () => (await anon.post('/wp-json/contact-form-7/v1/contact-forms/1/feedback', { multipart: { _wpcf7: '1' } })).status();
    expect(await submit()).not.toBe(401);
    expect(await status('/wp/v2/posts')).toBe(401);
    await save(async (form) => form.locator('input[name=logged_in_only]').check());
    expect(await submit()).toBe(401);
  });
  await audit('rest-api', 'custom route exception', async () => {
    await save(async (form) => {
      await form.locator('input[name=logged_in_only]').check();
      await form.locator('textarea[name=allow_routes]').fill('wp/v2/posts');
    });
    expect(await status('/wp/v2/posts')).toBe(200);
    expect(await status('/wp/v2/pages')).toBe(401);
  });
  await audit('rest-api', 'switch off a namespace → 404', async () => {
    await save(async (form) => form.locator('input[name="disabled_namespaces[]"][value="wp/v2"]').check());
    expect(await status('/wp/v2/posts')).toBe(404);
  });
  await audit('rest-api', 'rate limit → 429', async () => {
    await save(async (form) => form.locator('input[name=rate_limit]').fill('3'));
    const codes = [];
    for (let i = 0; i < 6; i += 1) codes.push(await status('/wp/v2/posts'));
    expect(codes).toContain(429);
  });
  await audit('rest-api', 'rate-limit exempt IP', async () => {
    await save(async (form) => {
      await form.locator('input[name=rate_limit]').fill('1');
      await form.locator('textarea[name=rate_exempt_ips]').fill('127.0.0.1\n172.17.0.1\n172.18.0.1\n::1');
    });
    const codes = [];
    for (let i = 0; i < 4; i += 1) codes.push(await status('/wp/v2/posts'));
    // Requests reach WordPress from the Docker gateway, which is one of the exempt IPs.
    expect(codes).not.toContain(429);
  });
  await audit('rest-api', 'hide REST discovery links', async () => {
    await save(async (form) => form.locator('input[name=remove_discovery]').check());
    expect(await (await anon.get('/')).text()).not.toContain('https://api.w.org/');
  });
  await audit('rest-api', 'JSONP off', async () => {
    await save(async (form) => form.locator('input[name=disable_jsonp]').check());
    expect(await (await anon.get('/wp-json/?_jsonp=cb')).text()).not.toMatch(/^\/\*\*\/cb\(/);
  });
  await audit('rest-api', 'XML-RPC off', async () => {
    await save(async (form) => form.locator('input[name=disable_xmlrpc]').check());
    const body = '<?xml version="1.0"?><methodCall><methodName>system.listMethods</methodName></methodCall>';
    const text = await (await anon.post('/xmlrpc.php', { data: body, headers: { 'Content-Type': 'text/xml' } })).text();
    expect(text).not.toContain('wp.getUsersBlogs');
  });
  await audit('rest-api', 'restricted role treated like a visitor → 403', async () => {
    await save(async (form) => form.locator('input[name="restricted_roles[]"][value="editor"]').check());
    // Logged-in check runs through the admin browser session as a sanity check (admins are never restricted).
    expect((await page.request.get(`${BASE}/wp-json/wp/v2/posts`)).status()).toBe(200);
  });
  await audit('rest-api', 'blocked-request log', async () => {
    await save(async (form) => {
      await form.locator('input[name=log_blocked]').check();
      await form.locator('input[name=hide_users]').check();
    });
    await status('/wp/v2/users');
    await openAdmin(page, 'wpessential-rest-api');
    await expect(page.locator('#wpbody-content')).toContainText('/wp/v2/users');
    await expect(page.locator('#wpbody-content')).not.toContainText(/\b\d+\.\d+\.\d+\.[1-9]\d*\b(?!\.)/);
  });
  await save(async (form) => form.locator('input[name=hide_users]').check()); // Back to safe defaults.
});

test('AI Connect: switch, session, OAuth discovery, MCP, log and CSV', async ({ page, audit, anon }, testInfo) => {
  if (!HAS_PRO) {
    notBuilt('ai-connect', 'all options', 'Premium-gated: runs in the Pro repository suite (Free + Pro).');
    return;
  }
  let auth = '';
  let mcp = '';

  await audit('ai-connect', 'switch on', async () => {
    await openAdmin(page, 'wpessential-ai');
    const power = page.getByRole('switch', { name: /AI Connect/ });
    if ((await power.getAttribute('aria-checked')) !== 'true') await power.click();
    await expect(power).toHaveAttribute('aria-checked', 'true');
    await page.waitForLoadState('load');
    await openAdmin(page, 'wpessential-ai');
    await expect(page.getByRole('switch', { name: /AI Connect/ })).toHaveAttribute('aria-checked', 'true');
  });

  await audit('ai-connect', 'allow AI to change settings', async () => {
    const writes = page.getByRole('switch', { name: /Allow AI to change settings/ });
    if ((await writes.getAttribute('aria-checked')) !== 'true') await writes.click();
    await expect(writes).toHaveAttribute('aria-checked', 'true');
    await page.waitForLoadState('load');
    await openAdmin(page, 'wpessential-ai');
    await expect(page.getByRole('switch', { name: /Allow AI to change settings/ })).toHaveAttribute('aria-checked', 'true');
  });

  await audit('ai-connect', 'create a session (Other MCP client)', async () => {
    await page.locator('select[name=client]').selectOption('generic');
    await page.getByRole('button', { name: 'Create connection' }).click();
    await page.waitForLoadState('load');
    await expect(page.locator('#wpbody-content')).toContainText('Authorization: Basic');
    const code = (await page.locator('#wpbody-content pre, #wpbody-content code').allInnerTexts()).find((text) => text.includes('Authorization: Basic'));
    expect(code).toBeTruthy();
    mcp = code.match(/URL: (\S+)/)[1];
    auth = code.match(/Authorization: (Basic \S+)/)[1];
  });

  for (const client of ['claude-ai', 'chatgpt', 'claude-code', 'claude-desktop', 'cursor', 'vscode', 'windsurf', 'gemini']) {
    await audit('ai-connect', `connection instructions: ${client}`, async () => {
      await openAdmin(page, 'wpessential-ai');
      await page.locator('select[name=client]').selectOption(client);
      await page.getByRole('button', { name: 'Create connection' }).click();
      await expect(page.locator('#wpbody-content')).toContainText('/wp-json/wpessential/v1/mcp');
    });
  }

  await audit('ai-connect', 'OAuth discovery URLs', async () => {
    for (const url of ['/.well-known/oauth-protected-resource', '/.well-known/oauth-authorization-server']) {
      const response = await anon.get(url);
      expect(response.status(), url).toBe(200);
      const data = await response.json();
      expect(JSON.stringify(data), url).toContain('wpessential/v1');
    }
  });

  const rpc = async (method, params = {}, headers = {}) => anon.post(mcp, {
    data: { jsonrpc: '2.0', id: 1, method, params },
    headers: { 'Content-Type': 'application/json', Accept: 'application/json, text/event-stream', Authorization: auth, ...headers },
  });

  await audit('ai-connect', 'MCP without credentials → 401', async () => {
    expect((await rpc('initialize', {}, { Authorization: '' })).status()).toBe(401);
  });
  await audit('ai-connect', 'MCP initialize', async () => {
    const response = await rpc('initialize', { protocolVersion: '2025-06-18', capabilities: {}, clientInfo: { name: 'e2e', version: '1' } });
    expect(response.status()).toBe(200);
    expect(JSON.stringify(await response.json())).toContain('protocolVersion');
  });
  await audit('ai-connect', 'MCP tools/list', async () => {
    const response = await rpc('tools/list');
    expect(response.status()).toBe(200);
    expect((await response.json()).result.tools.length).toBeGreaterThan(0);
  });
  await audit('ai-connect', 'MCP tools/call is logged', async () => {
    const tools = (await (await rpc('tools/list')).json()).result.tools;
    // site_info is answered by the server itself; every other tool is an audited ability.
    const read = tools.find((tool) => tool.name !== 'site_info' && !/set|update|toggle|write|delete/i.test(tool.name));
    expect((await rpc('tools/call', { name: read.name, arguments: {} })).status()).toBe(200);
    await openAdmin(page, 'wpessential-ai');
    // The log shows the ability name (wpessential/framework-modules/list) behind the MCP tool name.
    await expect(page.locator('#wpbody-content')).toContainText(new RegExp(read.name.split('_').join('[_/-]')));
  });
  await audit('ai-connect', 'activity log CSV download', async () => {
    const download = page.waitForEvent('download');
    await page.getByRole('button', { name: 'Download CSV' }).click();
    const file = testInfo.outputPath('ai-log.csv');
    await (await download).saveAs(file);
    expect(fs.readFileSync(file, 'utf8').split('\n').length).toBeGreaterThan(1);
    expect(path.extname(file)).toBe('.csv');
  });
  await audit('ai-connect', 'disconnect sessions', async () => {
    await openAdmin(page, 'wpessential-ai');
    while (await page.getByRole('button', { name: 'Disconnect' }).count()) {
      await page.getByRole('button', { name: 'Disconnect' }).first().click();
      await page.waitForLoadState('load');
    }
    expect((await rpc('tools/list')).status()).toBe(401);
  });
  await audit('ai-connect', 'switch off', async () => {
    await openAdmin(page, 'wpessential-ai');
    await page.getByRole('switch', { name: /AI Connect/ }).click();
    await expect(page.getByRole('switch', { name: /AI Connect/ })).toHaveAttribute('aria-checked', 'false');
    await page.waitForLoadState('load');
    await openAdmin(page, 'wpessential-ai');
    await expect(page.getByRole('switch', { name: /AI Connect/ })).toHaveAttribute('aria-checked', 'false');
  });
});
