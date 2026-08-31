import crypto from 'node:crypto';
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

function canonicalize(value) {
  if (Array.isArray(value)) {
    return value.map(canonicalize);
  }
  if (value && typeof value === 'object') {
    return Object.fromEntries(
      Object.keys(value)
        .sort()
        .map((key) => [key, canonicalize(value[key])]),
    );
  }
  return value;
}

function checksum(value) {
  return crypto
    .createHash('sha256')
    .update(JSON.stringify(canonicalize(value)))
    .digest('hex');
}

function portablePackage() {
  const payload = {
    post_type_key: 'portable_book',
    name: 'Portable Books',
    singular_name: 'Portable Book',
    public: true,
    show_in_rest: true,
    supports: ['title', 'editor'],
  };
  const definition = {
    id: '55555555-5555-4555-8555-555555555555',
    slug: 'cpt-portable-book',
    type: 'post_type',
    schema_version: 1,
    owner_surface_id: 1,
    status: 'draft',
    payload,
    source_revision: 9,
    dependencies: [],
    checksum: checksum(payload),
  };
  const definitions = [definition];
  return {
    manifest: {
      format: 'wpessential-package',
      format_version: 1,
      package_type: 'definition',
      package_id: '66666666-6666-4666-8666-666666666666',
      created_at_utc: '2026-08-31T10:00:00Z',
      product_version: '0.1.0-dev',
      secret_policy: 'excluded',
      runtime_data_included: false,
      definition_count: 1,
      definitions_checksum: checksum(definitions),
    },
    definitions,
  };
}

async function visitPackages(page) {
  const response = await page.goto(
    `${playground.serverUrl}/wp-admin/admin.php?page=wpessential-configuration-packages`,
    { waitUntil: 'domcontentloaded' },
  );

  expect(response, 'Configuration Packages navigation should return a response.').not.toBeNull();
  expect(response?.ok(), 'Configuration Packages should return a successful HTTP response.').toBe(true);

  const root = page.locator('#wpessential-import-export-root');
  await expect(root).toBeVisible();
  await expect(root).toHaveAttribute('data-wpessential-enhanced', 'ready');
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

test('packaged Configuration Packages surface progressively enhances with safe defaults', async ({ page }) => {
  const pageErrors = [];
  page.on('pageerror', (error) => {
    pageErrors.push(error.message);
  });

  const root = await visitPackages(page);

  await expect(page.getByRole('heading', { name: 'Configuration Packages', level: 1 })).toBeVisible();
  await expect(page.getByRole('heading', { name: 'Export definition package', level: 2 })).toBeVisible();
  await expect(page.getByRole('heading', { name: 'Inspect and import definition package', level: 2 })).toBeVisible();
  await expect(page.getByLabel('Custom Post Types')).toBeChecked();
  await expect(page.getByLabel('Taxonomies')).toBeChecked();
  await expect(page.getByLabel('Import strategy')).toHaveValue('create_only');
  await expect(page.getByRole('button', { name: 'Apply import' })).toBeDisabled();
  await expect(root).toHaveAttribute('data-wpessential-surface', 'configuration-packages');
  expect(pageErrors, `Unexpected Configuration Packages browser errors: ${pageErrors.join(' | ')}`).toEqual([]);
});

test('packaged Configuration Packages imports stable identity only after dry run and re-exports it', async ({ page }) => {
  await visitPackages(page);

  const json = JSON.stringify(portablePackage(), null, 2);
  await page.getByLabel('Package JSON').fill(json);
  await page.getByRole('button', { name: 'Inspect / dry run' }).click();

  const report = page.locator('#wpessential-package-report');
  await expect(report).toBeVisible();
  await expect(report).toContainText('Preflight passed: 1 create, 0 update, 0 no change.');
  await expect(report).toContainText('create: post_type portable_book');

  const apply = page.getByRole('button', { name: 'Apply import' });
  await expect(apply).toBeEnabled();
  await apply.click();
  await expect(page.getByText('Configuration package applied: 1 created, 0 updated, 0 unchanged.')).toBeVisible();

  await page.getByRole('button', { name: 'Inspect / dry run' }).click();
  await expect(report).toContainText('Preflight passed: 0 create, 0 update, 1 no change.');

  await page.getByRole('button', { name: 'Generate package' }).click();
  const exported = page.getByLabel('Generated package JSON');
  await expect(exported).toHaveValue(/55555555-5555-4555-8555-555555555555/);
  await expect(exported).toHaveValue(/portable_book/);
  await expect(exported).toHaveValue(/"runtime_data_included": false/);
  await expect(exported).toHaveValue(/"secret_policy": "excluded"/);
});

test('packaged Configuration Packages has zero axe violations', async ({ page }) => {
  await visitPackages(page);

  const results = await new AxeBuilder({ page })
    .include('#wpessential-import-export-root')
    .analyze();

  const evidenceDirectory = path.resolve(process.cwd(), 'test-results');
  await fs.mkdir(evidenceDirectory, { recursive: true });
  await fs.writeFile(
    path.join(evidenceDirectory, 'axe-configuration-packages.json'),
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
    `Axe violations in WPE-owned Configuration Packages surface:\n${JSON.stringify(summary, null, 2)}`,
  ).toEqual([]);
});
