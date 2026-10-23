import fs from 'node:fs/promises';
import path from 'node:path';
import process from 'node:process';

import AxeBuilder from '@axe-core/playwright';
import { expect, test } from '@playwright/test';
import { runCLI } from '@wp-playground/cli';

const pluginDirectory = process.env.WPE_E2E_PLUGIN_DIR;

if (!pluginDirectory) {
  throw new Error('WPE_E2E_PLUGIN_DIR must point to the unpacked WPEssential distributable.');
}

let playground;

// Entrance animations fade colours in; axe must measure the settled page.
test.use({ reducedMotion: 'reduce' });

async function visitCore(page, slug) {
  const response = await page.goto(
    `${playground.serverUrl}/wp-admin/admin.php?page=${slug}`,
    { waitUntil: 'domcontentloaded' },
  );

  expect(response?.ok(), `${slug} should return a successful HTTP response.`).toBe(true);
  const root = page.locator('[data-wpessential-surface="core"]');
  await expect(root).toBeVisible();
  return root;
}

async function screenshot(page, name) {
  const directory = path.resolve(process.cwd(), 'test-results', 'core-ui');
  await fs.mkdir(directory, { recursive: true });
  await page.screenshot({ path: path.join(directory, `${name}.png`), fullPage: true });
}

test.beforeAll(async () => {
  playground = await runCLI({
    command: 'server',
    port: 0,
    quiet: true,
    skipBrowser: true,
    mount: [
      {
        hostPath: pluginDirectory,
        vfsPath: '/wordpress/wp-content/plugins/wpessential',
      },
    ],
    blueprint: {
      preferredVersions: {
        php: '8.2',
        wp: '7.1',
      },
      login: true,
      steps: [
        {
          step: 'activatePlugin',
          pluginPath: '/wordpress/wp-content/plugins/wpessential/wpessential.php',
        },
      ],
    },
  });
});

test.afterAll(async () => {
  await playground?.server?.close();
});

test('Dashboard renders inside the toolkit shell', async ({ page }) => {
  await visitCore(page, 'wpessential');

  await expect(page.getByRole('navigation', { name: 'WPEssential', exact: true })).toBeVisible();
  await expect(page.getByRole('heading', { name: 'Everything your WordPress site needs, in one place.', level: 1 })).toBeVisible();
  await expect(page.getByRole('heading', { name: 'Active modules', level: 2 })).toBeVisible();
  await screenshot(page, 'dashboard');
});

test('Free module can be switched off and on from the module manager', async ({ page }) => {
  await visitCore(page, 'wpessential-modules');

  const card = page.locator('[data-wpe-module]').filter({ has: page.getByRole('heading', { name: 'Taxonomies' }) });
  const toggle = card.getByRole('switch', { name: 'Turn Taxonomies on or off' });
  await expect(toggle).toHaveAttribute('aria-checked', 'true');

  const url = page.url();
  await toggle.click();
  await expect(page.getByRole('status').filter({ hasText: 'Switched off.' })).toBeVisible();
  expect(page.url(), 'the switch must update in place over AJAX').toBe(url);
  await expect(card.getByRole('switch')).toHaveAttribute('aria-checked', 'false');

  await card.getByRole('switch').click();
  await expect(page.getByRole('status').filter({ hasText: 'Switched on.' })).toBeVisible();
  await expect(card.getByRole('switch')).toHaveAttribute('aria-checked', 'true');

  const premium = page.locator('[data-wpe-module][data-edition="pro"]').first();
  await expect(premium.getByRole('link', { name: 'Premium' })).toBeVisible();
  await screenshot(page, 'modules');
});

test('module overview lists contract options with tier filters', async ({ page }) => {
  await visitCore(page, 'wpessential-module-taxonomies');

  await expect(page.getByRole('heading', { name: /Taxonomies/, level: 1 })).toBeVisible();
  await page.getByRole('tab', { name: /Essential/ }).click();
  await expect(page.locator('[data-wpe-option]:not([hidden])').first()).toHaveAttribute('data-tier', 'Essential');
  await screenshot(page, 'module-taxonomies');
});

test('completed module screens adopt the toolkit shell', async ({ page }) => {
  await page.goto(`${playground.serverUrl}/wp-admin/admin.php?page=wpessential-taxonomy`, { waitUntil: 'domcontentloaded' });
  await expect(page.locator('.wrap.wpe-ui .wpe-ui-topbar')).toBeVisible();
  await expect(page.getByRole('heading', { name: 'Taxonomies', level: 1 })).toBeVisible();
  await screenshot(page, 'taxonomies-builder');
});

test('Module manager has zero axe violations', async ({ page }) => {
  await visitCore(page, 'wpessential-modules');

  const results = await new AxeBuilder({ page }).include('[data-wpessential-surface="core"]').analyze();
  expect(results.violations.map((violation) => violation.id)).toEqual([]);
});

test('WordPress sidebar lists Dashboard and modules; product pages live in the top navigation', async ({ page }) => {
  await visitCore(page, 'wpessential');

  const submenu = page.locator('#toplevel_page_wpessential .wp-submenu');
  for (const name of ['Modules', 'Demo Library', 'Elements', 'Docs', 'AI Connect', 'Account', 'System Status']) {
    await expect(submenu.getByRole('link', { name, exact: true }), `${name} must not be in the WordPress sidebar`).toHaveCount(0);
  }
  await expect(submenu.getByRole('link', { name: 'Dashboard', exact: true })).toHaveCount(1);
  await expect(submenu.getByRole('link', { name: 'Post Types', exact: true })).toHaveCount(1);
  await expect(submenu.getByRole('link', { name: 'Taxonomies', exact: true })).toHaveCount(1);

  const nav = page.getByRole('navigation', { name: 'WPEssential', exact: true });
  for (const name of ['Dashboard', 'Modules', 'Demo Library', 'Elements']) {
    await expect(nav.getByRole('link', { name })).toBeVisible();
  }
  for (const name of ['AI Connect', 'Account', 'System Status', 'Docs']) {
    await expect(nav.getByRole('link', { name }), `${name} is not a top navigation tab`).toHaveCount(0);
  }

  // Account is an icon beside light/dark with Log in / Register, Docs and System Status.
  const account = page.locator('[data-wpe-account]');
  await account.locator('summary').click();
  for (const name of ['Log in / Register', 'Docs', 'System Status']) {
    await expect(account.getByRole('link', { name })).toBeVisible();
  }
  await page.keyboard.press('Escape');
  await expect(account.getByRole('link', { name: 'Docs' })).toBeHidden();

  // Product pages still open, with the WPEssential sidebar item kept highlighted.
  await visitCore(page, 'wpessential-modules');
  await expect(page.locator('#toplevel_page_wpessential')).toHaveClass(/wp-has-current-submenu|current/);
});

test('top navigation opens screens in place without a page reload', async ({ page }) => {
  await visitCore(page, 'wpessential');
  await page.evaluate(() => {
    window.__wpeSamePage = true;
  });

  const nav = page.getByRole('navigation', { name: 'WPEssential', exact: true });
  await nav.getByRole('link', { name: 'Modules' }).click();
  await expect(page).toHaveURL(/page=wpessential-modules/);
  await expect(page.locator('[data-wpe-module]').first()).toBeVisible();
  expect(await page.evaluate(() => window.__wpeSamePage === true), 'the screen must load over AJAX').toBe(true);
  await expect(nav.getByRole('link', { name: 'Modules' })).toHaveAttribute('aria-current', 'page');

  await page.goBack();
  await expect(page).toHaveURL(/page=wpessential$/);
  expect(await page.evaluate(() => window.__wpeSamePage === true)).toBe(true);
});

test('Dashboard, Modules, Demo Library and Elements switch with React Router, without a reload', async ({ page }) => {
  const errors = [];
  page.on('pageerror', (error) => errors.push(error.message));
  await visitCore(page, 'wpessential');
  await expect(page.locator('[data-wpe-app-root]')).toHaveCount(1);
  // A full page load would drop this marker.
  await page.evaluate(() => {
    window.wpeSameDocument = true;
  });

  const nav = page.getByRole('navigation', { name: 'WPEssential', exact: true });
  await nav.getByRole('link', { name: 'Modules' }).click();
  await expect(page).toHaveURL(/page=wpessential-modules/);
  await expect(page.getByRole('heading', { name: 'Free + Premium modules', level: 1 })).toBeVisible();
  await expect(nav.getByRole('link', { name: 'Modules' })).toHaveAttribute('aria-current', 'page');
  await expect(page.locator('[data-wpe-module]').first()).toBeVisible();

  await nav.getByRole('link', { name: 'Elements' }).click();
  await expect(page.getByRole('heading', { name: 'Blocks & widgets', level: 1 })).toBeVisible();
  await page.getByRole('link', { name: /Elementor/ }).first().click();
  await expect(page).toHaveURL(/type=elementor/);
  await expect(page.getByRole('heading', { name: 'No Elementor widgets yet' })).toBeVisible();

  await nav.getByRole('link', { name: 'Demo Library' }).click();
  await expect(page.getByRole('heading', { name: 'Start from a ready-made site', level: 1 })).toBeVisible();

  await page.goBack();
  await expect(page.getByRole('heading', { name: 'No Elementor widgets yet' })).toBeVisible();
  await nav.getByRole('link', { name: 'Dashboard' }).click();
  await expect(page.getByRole('heading', { name: 'Everything your WordPress site needs, in one place.' })).toBeVisible();

  expect(await page.evaluate(() => window.wpeSameDocument)).toBe(true);
  expect(errors).toEqual([]);
  const results = await new AxeBuilder({ page }).include('[data-wpessential-surface="core"]').analyze();
  expect(results.violations.map((violation) => violation.id)).toEqual([]);
});
