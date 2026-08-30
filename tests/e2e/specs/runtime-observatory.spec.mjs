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

async function visitRuntimeObservatory(page) {
  const response = await page.goto(
    `${playground.serverUrl}/wp-admin/admin.php?page=wpessential`,
    { waitUntil: 'domcontentloaded' },
  );

  expect(response, 'Runtime Observatory navigation should return a response.').not.toBeNull();
  expect(response?.ok(), 'Runtime Observatory should return a successful HTTP response.').toBe(true);

  const root = page.locator('#wpessential-admin-root');
  await expect(root).toBeVisible();
  return root;
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

test('packaged Runtime Observatory renders and progressively enhances', async ({ page }) => {
  const pageErrors = [];
  page.on('pageerror', (error) => {
    pageErrors.push(error.message);
  });

  const root = await visitRuntimeObservatory(page);

  await expect(page.getByRole('heading', { name: 'WPEssential', level: 1 })).toBeVisible();
  await expect(page.getByRole('heading', { name: 'Runtime Observatory', level: 2 })).toBeVisible();
  await expect(
    page.getByRole('table', { name: 'WPEssential runtime diagnostics' }),
  ).toBeVisible();

  await expect(page.getByRole('row', { name: /Platform version 0\.1\.0-dev/ })).toBeVisible();
  await expect(page.getByRole('row', { name: /WordPress 7\.1/ })).toBeVisible();
  await expect(page.getByRole('row', { name: /PHP 8\.2/ })).toBeVisible();
  await expect(page.getByRole('row', { name: /Kernel booted yes/ })).toBeVisible();
  await expect(page.getByRole('row', { name: /Multisite no/ })).toBeVisible();

  await expect(root).toHaveAttribute('data-wpessential-surface', 'runtime-observatory');
  await expect(root).toHaveAttribute('data-wpessential-enhanced', 'ready');
  expect(pageErrors, `Unexpected browser page errors: ${pageErrors.join(' | ')}`).toEqual([]);
});

test('plugin-owned Runtime Observatory has zero axe violations', async ({ page }) => {
  await visitRuntimeObservatory(page);

  const results = await new AxeBuilder({ page })
    .include('#wpessential-admin-root')
    .analyze();

  const evidenceDirectory = path.resolve(process.cwd(), 'test-results');
  await fs.mkdir(evidenceDirectory, { recursive: true });
  await fs.writeFile(
    path.join(evidenceDirectory, 'axe-runtime-observatory.json'),
    `${JSON.stringify(results, null, 2)}\n`,
    'utf8',
  );

  const summary = results.violations.map((violation) => ({
    id: violation.id,
    impact: violation.impact,
    help: violation.help,
    targets: violation.nodes.map((node) => node.target),
  }));

  expect(
    results.violations,
    `Axe violations in WPE-owned Runtime Observatory:\n${JSON.stringify(summary, null, 2)}`,
  ).toEqual([]);
});
