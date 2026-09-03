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

async function visitQueryBuilder(page) {
  const response = await page.goto(
    `${playground.serverUrl}/wp-admin/admin.php?page=wpessential-query`,
    { waitUntil: 'domcontentloaded' },
  );
  expect(response, 'Query Builder navigation should return a response.').not.toBeNull();
  expect(response?.ok(), 'Query Builder should return a successful HTTP response.').toBe(true);
  const root = page.locator('#wpessential-query-root');
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
      {
        hostPath: path.resolve(process.cwd(), 'fixtures/query-module-fixture.php'),
        vfsPath: '/wordpress/wp-content/plugins/wpe-query-e2e/wpe-query-e2e.php',
      },
    ],
    blueprint: {
      preferredVersions: { php: '8.2', wp: '7.1' },
      login: true,
      steps: [
        { step: 'activatePlugin', pluginPath: '/wordpress/wp-content/plugins/wpe-query-e2e/wpe-query-e2e.php' },
        { step: 'activatePlugin', pluginPath: '/wordpress/wp-content/plugins/wpessential/wpessential.php' },
      ],
    },
  });
});

test.afterAll(async () => {
  await playground?.server?.close();
});

test('packaged Query Builder is reachable, enhanced, bounded, and execution-disabled', async ({ page }) => {
  const pageErrors = [];
  page.on('pageerror', (error) => pageErrors.push(error.message));

  const root = await visitQueryBuilder(page);
  await expect(page.getByRole('heading', { name: 'Query Builder', level: 1 })).toBeVisible();
  await expect(root).toHaveAttribute('data-wpessential-surface', 'query');
  await expect(root).toHaveAttribute('data-wpessential-enhanced', 'ready');
  await expect(page.getByLabel('Data source')).toHaveValue('wordpress.posts');
  await expect(page.getByLabel('Page size')).toHaveValue('20');
  const execute = page.getByRole('button', { name: 'Preview execution unavailable' });
  await expect(execute).toBeDisabled();
  await expect(page.locator('#wpessential-query-bootstrap')).toContainText('wordpress.posts');
  expect(pageErrors, `Unexpected Query Builder browser errors: ${pageErrors.join(' | ')}`).toEqual([]);
});

test('packaged Query Builder has zero axe violations', async ({ page }) => {
  await visitQueryBuilder(page);
  const results = await new AxeBuilder({ page }).include('#wpessential-query-root').analyze();
  const evidenceDirectory = path.resolve(process.cwd(), 'test-results');
  await fs.mkdir(evidenceDirectory, { recursive: true });
  await fs.writeFile(
    path.join(evidenceDirectory, 'axe-query-builder.json'),
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
    `Axe violations in WPE-owned Query Builder:\n${JSON.stringify(summary, null, 2)}`,
  ).toEqual([]);
});
