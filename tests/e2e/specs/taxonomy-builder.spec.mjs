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

async function visitTaxonomies(page) {
  const response = await page.goto(
    `${playground.serverUrl}/wp-admin/admin.php?page=wpessential-taxonomy`,
    { waitUntil: 'domcontentloaded' },
  );

  expect(response, 'Taxonomy Builder navigation should return a response.').not.toBeNull();
  expect(response?.ok(), 'Taxonomy Builder should return a successful HTTP response.').toBe(true);

  const root = page.locator('#wpessential-taxonomy-root');
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

test('packaged Taxonomy Builder renders and progressively enhances', async ({ page }) => {
  const pageErrors = [];
  page.on('pageerror', (error) => {
    pageErrors.push(error.message);
  });

  const root = await visitTaxonomies(page);

  await expect(page.getByRole('heading', { name: 'Taxonomies', level: 1 })).toBeVisible();
  await expect(page.getByRole('heading', { name: 'Add taxonomy', level: 2 })).toBeVisible();
  await expect(page.getByLabel('Taxonomy key')).toBeVisible();
  await expect(page.getByLabel('Plural name')).toBeVisible();
  await expect(page.getByLabel('Singular name')).toBeVisible();
  await expect(page.getByLabel('Object types')).toBeVisible();
  await expect(page.getByRole('button', { name: 'Validate' })).toBeVisible();
  await expect(page.getByRole('button', { name: 'Save taxonomy' })).toBeVisible();
  await expect(page.getByRole('table', { name: 'Saved taxonomies' })).toBeVisible();
  await expect(page.getByText('No taxonomies have been created yet.')).toBeVisible();

  await expect(root).toHaveAttribute('data-wpessential-surface', 'taxonomies');
  await expect(root).toHaveAttribute('data-wpessential-enhanced', 'ready');
  expect(pageErrors, `Unexpected Taxonomy Builder browser errors: ${pageErrors.join(' | ')}`).toEqual([]);
});

test('packaged Taxonomy preflight blocks reserved keys and saves a valid draft', async ({ page }) => {
  await visitTaxonomies(page);

  await page.getByLabel('Taxonomy key').fill('category');
  await page.getByLabel('Plural name').fill('Categories');
  await page.getByLabel('Singular name').fill('Category');
  await page.getByLabel('Object types').fill('post');
  await page.getByRole('button', { name: 'Validate' }).click();

  const validation = page.locator('#wpessential-taxonomy-validation');
  await expect(validation).toBeVisible();
  await expect(validation).toContainText(/Validation blocked by \d+ issue/);
  await expect(validation).toContainText('reserved by WordPress');
  await expect(page.getByText('No taxonomies have been created yet.')).toBeVisible();

  await page.getByLabel('Taxonomy key').fill('library_genre');
  await page.getByLabel('Plural name').fill('Genres');
  await page.getByLabel('Singular name').fill('Genre');
  await page.getByLabel('Object types').fill('post');
  await page.getByRole('button', { name: 'Save taxonomy' }).click();

  await expect(page.getByText('Taxonomy created.')).toBeVisible();
  const row = page.getByRole('row', { name: /Genres library_genre post Draft 1/ });
  await expect(row).toBeVisible();
  await expect(row.getByRole('button', { name: 'Edit' })).toBeVisible();
  await expect(row.getByRole('button', { name: 'Publish' })).toBeVisible();
});

test('packaged Taxonomy Builder has zero axe violations', async ({ page }) => {
  await visitTaxonomies(page);

  const results = await new AxeBuilder({ page })
    .include('#wpessential-taxonomy-root')
    .analyze();

  const evidenceDirectory = path.resolve(process.cwd(), 'test-results');
  await fs.mkdir(evidenceDirectory, { recursive: true });
  await fs.writeFile(
    path.join(evidenceDirectory, 'axe-taxonomy-builder.json'),
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
    `Axe violations in WPE-owned Taxonomy Builder:\n${JSON.stringify(summary, null, 2)}`,
  ).toEqual([]);
});
