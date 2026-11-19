// Shared fixtures for the end-to-end audit specs that run against a real WordPress (wp-env).
// Pro specs import `test`/`expect` from here so they resolve this package's Playwright.
import fs from 'node:fs';
import path from 'node:path';
import process from 'node:process';
import { execSync } from 'node:child_process';

import { expect, request, test as base } from '@playwright/test';

export { expect, request };

export const BASE = (process.env.WPE_E2E_BASE_URL ?? '').replace(/\/$/, '');
export const HAS_PRO = process.env.WPE_E2E_PRO === '1';
const OUT = path.resolve(process.cwd(), 'test-results', 'audit');
const ROWS = path.join(OUT, 'rows.jsonl');
const USER = process.env.WPE_E2E_USER ?? 'admin';
const PASS = process.env.WPE_E2E_PASSWORD ?? 'password';

// Log lines WPEssential does not cause: no outbound network in CI/sandbox, WooCommerce loading its
// own translations early, and the MCP adapter copy that Elementor bundles (deprecated notice).
const NOISE = /McpAdapter|wp_update_(plugins|themes)\(\)|wp_version_check\(\)|WordPress\.org|cURL error|woocommerce<\/code> domain|action-scheduler|Automattic\\/;

function row(data) {
  fs.mkdirSync(OUT, { recursive: true });
  fs.appendFileSync(ROWS, `${JSON.stringify(data)}\n`);
}

const slug = (text) => text.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '').slice(0, 80);

async function debugLog(api) {
  const response = await api.get(`${BASE}/wp-content/debug.log`);
  return response.ok() ? await response.text() : '';
}

export const test = base.extend({
  // One admin login per worker, reused by every test.
  adminState: [async ({ browser }, use) => {
    const file = path.join(OUT, 'admin-state.json');
    fs.mkdirSync(OUT, { recursive: true });
    const page = await browser.newPage();
    await page.goto(`${BASE}/wp-login.php`);
    await page.fill('#user_login', USER);
    await page.fill('#user_pass', PASS);
    await page.click('#wp-submit');
    await page.waitForURL(/wp-admin/);
    await page.context().storageState({ path: file });
    await page.close();
    await use(file);
  }, { scope: 'worker' }],

  storageState: async ({ adminState }, use) => use(adminState),

  context: async ({ context }, use) => {
    // Nothing outside the site: tests must not depend on (or wait for) third-party hosts.
    await context.route((url) => !url.href.startsWith(BASE), (route) => route.abort());
    await use(context);
  },

  // Logged-out HTTP client, the equivalent of plain curl.
  anon: async ({}, use) => {
    const api = await request.newContext({ baseURL: BASE, storageState: { cookies: [], origins: [] } });
    await use(api);
    await api.dispose();
  },

  /**
   * audit(module, option, fn): runs one checked step, screenshots it and writes a report row.
   * A failing step is a soft failure so the remaining options are still exercised.
   */
  audit: async ({ page, anon }, use, testInfo) => {
    const before = (await debugLog(anon)).length;
    await use(async (module, option, fn) => {
      let error = '';
      try {
        await fn();
      } catch (thrown) {
        error = String(thrown?.message ?? thrown).replace(/\u001b\[[0-9;]*m/g, '').replace(/\s+/g, ' ').slice(0, 1500);
      }
      const shot = `${slug(module)}--${slug(option)}.png`;
      await page.screenshot({ path: path.join(OUT, shot), fullPage: true }).catch(() => {});
      row({ module, option, status: error === '' ? 'PASS' : 'FAIL', error, screenshot: shot });
      expect.soft(error, `${module} › ${option}`).toBe('');
    });
    const lines = (await debugLog(anon)).slice(before).split('\n').filter((line) => /PHP (Fatal|Warning|Notice|Deprecated|Parse)/.test(line) && !NOISE.test(line));
    const unique = [...new Set(lines.map((line) => line.replace(/^\[[^\]]*\]\s*/, '').slice(0, 300)))];
    for (const line of unique) {
      row({ module: 'debug.log', option: testInfo.title, status: /Fatal|Parse/.test(line) ? 'FAIL' : 'NOTE', error: line, screenshot: '' });
    }
    expect.soft(unique.filter((line) => /Fatal|Parse/.test(line)), 'PHP fatals in debug.log').toEqual([]);
  },
});

test.beforeEach(() => {
  test.skip(BASE === '', 'WPE_E2E_BASE_URL is not set: these specs need a running WordPress (wp-env).');
});

/** Records a feature the plan lists as foundation/skeleton: not a failure. */
export function notBuilt(module, option, reason) {
  row({ module, option, status: 'NOT BUILT', error: reason, screenshot: '' });
}

/** Opens a WPEssential admin page and fails on the generic WordPress error screens. */
export async function openAdmin(page, slugOrQuery) {
  const response = await page.goto(`${BASE}/wp-admin/admin.php?page=${slugOrQuery}`, { waitUntil: 'load' });
  expect(response?.status(), `${slugOrQuery} HTTP status`).toBe(200);
  const body = page.locator('#wpbody-content');
  await expect(body).not.toContainText(/Sorry, you are not allowed|critical error|Fatal error|Warning: |Notice: |Deprecated: /);
  expect((await body.innerText()).trim().length, `${slugOrQuery} is not an empty page`).toBeGreaterThan(80);
  return body;
}


const CONTROLS = 'input:not([type=hidden]):not([type=file]):not([type=search]):not([type=submit]):not([type=button]), select, textarea';

/**
 * Sets every visible, enabled control inside `scope`: checkboxes on, selects to their last option,
 * text and number inputs to deterministic values. `overrides` maps a control id/name to a value
 * (`null` leaves the control alone); a control without id/name is keyed by its label text. Two passes, because a choice can reveal more controls.
 */
export async function fillAll(scope, overrides = {}) {
  const done = new Set();
  for (let pass = 0; pass < 2; pass += 1) {
    // Read the whole form first: an editor may re-render while it is being filled.
    const infos = await scope.locator(CONTROLS).evaluateAll((elements) => elements.map((el, index) => ({
      index, id: el.id, key: el.id || el.name || (el.closest('label')?.textContent ?? '').trim() || `#${index}`, tag: el.tagName, type: el.type,
      off: el.disabled || el.readOnly, options: el.tagName === 'SELECT' ? [...el.options].filter((o) => !o.disabled).map((o) => o.value) : [],
    })));
    for (const info of infos) {
      const control = info.id ? scope.locator(`[id="${info.id}"]`) : scope.locator(CONTROLS).nth(info.index);
      if (done.has(info.key) || info.off || !(await control.isVisible()) || !(await control.isEnabled())) continue;
      done.add(info.key);
      const wanted = overrides[info.key];
      if (wanted === null) continue;
      if (info.type === 'checkbox') {
        await control.setChecked(wanted ?? true);
      } else if (info.type === 'radio') {
        if (wanted !== undefined) await control.setChecked(Boolean(wanted));
      } else if (info.tag === 'SELECT') {
        const choice = wanted ?? info.options.filter((value) => value !== '').at(-1);
        if (choice !== undefined) await control.selectOption(choice);
      } else {
        const fallback = { number: '3', url: 'https://example.org/e2e', email: 'e2e@example.org', color: '#336699', date: '2026-11-16' }[info.type] ?? `e2e-${done.size}`;
        await control.fill(String(wanted ?? fallback));
      }
    }
  }
}

/** Current value of every control inside `scope`, keyed by id/name; used to prove values survive a reload. */
export async function snapshot(scope) {
  return scope.locator(CONTROLS).evaluateAll((elements) => Object.fromEntries(
    elements.map((el) => [el.id || el.name || (el.closest('label')?.textContent ?? '').trim(), el]).filter(([key]) => key).map(([key, el]) => [key, el.type === 'checkbox' || el.type === 'radio' ? el.checked : el.value]),
  ));
}

/** Keys whose saved value differs from what was entered. */
export function changed(entered, saved, ignore = /^$/) {
  return Object.keys(entered).filter((key) => !ignore.test(key) && key in saved && saved[key] !== entered[key]).map((key) => `${key}: ${JSON.stringify(entered[key])} → ${JSON.stringify(saved[key])}`);
}

/** WP-CLI inside the test site, for seeding data the UI cannot create. WPE_E2E_WP_CLI overrides the command. */
export function wp(args) {
  const command = process.env.WPE_E2E_WP_CLI ?? 'npx wp-env run cli wp';
  return execSync(`${command} ${args}`, { cwd: process.env.WPE_E2E_WP_ENV_DIR ?? process.cwd(), encoding: 'utf8', stdio: ['ignore', 'pipe', 'pipe'] });
}
