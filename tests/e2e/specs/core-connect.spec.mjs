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
  const directory = path.resolve(process.cwd(), 'test-results', 'core-connect');
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

test.afterAll(async () => {
  await playground?.server?.close();
});

const screens = [
  ['wpessential-demos', 'Start from a ready-made site'],
  ['wpessential-elements', 'Blocks & widgets'],
  ['wpessential-ai', 'Connect your AI assistant'],
  ['wpessential-license', 'Account & subscription'],
  ['wpessential-docs', 'Learn WPEssential'],
];

async function expectNoAxeViolations(page) {
  const results = await new AxeBuilder({ page }).include('[data-wpessential-surface="core"]').analyze();
  expect(results.violations.map((violation) => violation.id)).toEqual([]);
}

test('every connect screen renders in light mode without axe violations', async ({ page }) => {
  for (const [slug, heading] of screens) {
    await visitCore(page, slug);
    await expect(page.getByRole('heading', { name: heading, level: 1 })).toBeVisible();
    await expectNoAxeViolations(page);
    await screenshot(page, `${slug}-light`);
  }
});

test('Elements ships empty for Gutenberg and Elementor', async ({ page }) => {
  await visitCore(page, 'wpessential-elements');
  await expect(page.getByRole('heading', { name: 'No Gutenberg blocks yet' })).toBeVisible();
  await page.getByRole('link', { name: /Elementor/ }).first().click();
  await expect(page.getByRole('heading', { name: 'No Elementor widgets yet' })).toBeVisible();
});

test('AI Connect is a Premium module and stays locked without Premium', async ({ page }) => {
  await visitCore(page, 'wpessential-ai');
  await expect(page.getByRole('heading', { name: 'AI Connect is a Premium module' })).toBeVisible();
  await expect(page.getByRole('switch', { name: 'Turn AI Connect on or off' })).toHaveCount(0);
  await expectNoAxeViolations(page);

  await visitCore(page, 'wpessential-modules');
  const card = page.locator('[data-wpe-module]').filter({ has: page.getByRole('heading', { name: 'AI Connect' }) });
  await expect(card.getByRole('link', { name: 'Premium' })).toBeVisible();
});

test('dark mode is saved per user and every screen passes axe in dark', async ({ page }) => {
  await visitCore(page, 'wpessential');
  await page.locator('[data-wpe-mode-form] button[type="submit"]').click();
  await page.waitForLoadState('domcontentloaded');
  if (!(await page.locator('body.wpe-mode-dark').count())) {
    await page.locator('[data-wpe-mode-form] button[type="submit"]').click();
    await page.waitForLoadState('domcontentloaded');
  }
  await expect(page.locator('body')).toHaveClass(/wpe-mode-dark/);
  await page.reload();
  await expect(page.locator('body'), 'the AJAX mode switch must be saved').toHaveClass(/wpe-mode-dark/);

  for (const [slug] of screens) {
    await visitCore(page, slug);
    await expect(page.locator('body')).toHaveClass(/wpe-mode-dark/);
    await expectNoAxeViolations(page);
    await screenshot(page, `${slug}-dark`);
  }
});
