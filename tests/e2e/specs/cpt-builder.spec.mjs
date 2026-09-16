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

async function visitCpts(page) {
  const response = await page.goto(
    `${playground.serverUrl}/wp-admin/admin.php?page=wpessential-cpt`,
    { waitUntil: 'domcontentloaded' },
  );

  expect(response, 'CPT Builder navigation should return a response.').not.toBeNull();
  expect(response?.ok(), 'CPT Builder should return a successful HTTP response.').toBe(true);

  const root = page.locator('#wpessential-admin-root');
  await expect(root).toBeVisible();
  return root;
}

async function postCptRoute(page, routeKey, payload) {
  return page.evaluate(
    async ({ routeKey: key, requestPayload }) => {
      const bootstrapNode = document.querySelector('#wpessential-admin-bootstrap');
      if (!(bootstrapNode instanceof HTMLScriptElement)) {
        throw new Error('CPT bootstrap payload is unavailable.');
      }

      const bootstrap = JSON.parse(bootstrapNode.textContent ?? '{}');
      const route = bootstrap?.routes?.[key];
      if (!route || typeof route.type !== 'string' || typeof route.nonce !== 'string') {
        throw new Error(`CPT route ${key} is unavailable.`);
      }

      const body = new URLSearchParams();
      body.set('action', bootstrap.ajaxAction);
      body.set('type', route.type);
      body.set('nonce', route.nonce);
      body.set('payload_json', JSON.stringify(requestPayload));

      const response = await fetch(bootstrap.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
        },
        body: body.toString(),
      });
      const value = await response.json();
      if (!response.ok || value?.success !== true) {
        throw new Error(value?.error?.message ?? `CPT route ${key} failed.`);
      }
      return value.data;
    },
    { routeKey, requestPayload: payload },
  );
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

test('packaged CPT Builder renders the bounded editor and progressively enhances', async ({ page }) => {
  const pageErrors = [];
  page.on('pageerror', (error) => {
    pageErrors.push(error.message);
  });

  const root = await visitCpts(page);

  await expect(page.getByRole('heading', { name: 'Custom Post Types', level: 1 })).toBeVisible();
  await expect(page.getByRole('heading', { name: 'Add custom post type', level: 2 })).toBeVisible();
  await expect(page.getByLabel('Post type key')).toBeVisible();
  await expect(page.getByLabel('Plural name')).toBeVisible();
  await expect(page.getByLabel('Singular name')).toBeVisible();
  await expect(page.getByRole('group', { name: 'Behavior' })).toBeVisible();
  await expect(page.getByRole('group', { name: 'Editor supports' })).toBeVisible();
  await expect(page.getByRole('button', { name: 'Validate' })).toBeVisible();
  await expect(page.getByRole('button', { name: 'Save custom post type' })).toBeVisible();
  await expect(page.getByRole('table', { name: 'Saved custom post types' })).toBeVisible();
  await expect(page.getByText('No custom post types have been created yet.')).toBeVisible();

  await expect(root).toHaveAttribute('data-wpessential-surface', 'custom-post-types');
  await expect(root).toHaveAttribute('data-wpessential-enhanced', 'ready');
  expect(pageErrors, `Unexpected CPT Builder browser errors: ${pageErrors.join(' | ')}`).toEqual([]);
});

test('bounded CPT editor preserves hidden runtime options and supports on update', async ({ page }) => {
  await visitCpts(page);

  await postCptRoute(page, 'save', {
    status: 'draft',
    payload: {
      post_type_key: 'rc_book',
      name: 'RC Books',
      singular_name: 'RC Book',
      description: 'Seeded with advanced runtime options.',
      public: true,
      show_in_rest: true,
      hierarchical: false,
      supports: ['title', 'editor', 'author'],
      has_archive: 'rc-books',
      rewrite: { slug: 'rc/library', with_front: false },
      query_var: 'rc_book',
      can_export: false,
      delete_with_user: true,
      menu_icon: 'dashicons-book-alt',
    },
  });

  await page.getByRole('button', { name: 'Refresh' }).click();
  await expect(page.getByText('Custom post types refreshed.')).toBeVisible();

  const row = page.getByRole('row', { name: /RC Books rc_book Draft 1/ });
  await expect(row).toBeVisible();
  await row.getByRole('button', { name: 'Edit' }).click();

  await expect(page.getByLabel('Post type key')).toHaveAttribute('readonly', '');
  await expect(page.getByLabel('Plural name')).toHaveValue('RC Books');
  await expect(page.getByLabel('Title')).toBeChecked();
  await expect(page.getByLabel('Editor')).toBeChecked();

  await page.getByLabel('Plural name').fill('RC Books Updated');
  await page.getByLabel('Editor').uncheck();
  await page.getByRole('button', { name: 'Save custom post type' }).click();

  await expect(page.getByText('Custom post type updated.')).toBeVisible();

  const list = await postCptRoute(page, 'list', {});
  const updated = list.definitions.find(
    (definition) => definition?.payload?.post_type_key === 'rc_book',
  );

  expect(updated, 'Updated CPT definition should remain readable.').toBeTruthy();
  expect(updated.payload.name).toBe('RC Books Updated');
  expect(updated.payload.has_archive).toBe('rc-books');
  expect(updated.payload.rewrite).toEqual({ slug: 'rc/library', with_front: false });
  expect(updated.payload.query_var).toBe('rc_book');
  expect(updated.payload.can_export).toBe(false);
  expect(updated.payload.delete_with_user).toBe(true);
  expect(updated.payload.menu_icon).toBe('dashicons-book-alt');
  expect(updated.payload.supports).toEqual(expect.arrayContaining(['title', 'author']));
  expect(updated.payload.supports).not.toContain('editor');
});

test('packaged CPT Builder has zero axe violations in its initial bounded editor', async ({ page }) => {
  await visitCpts(page);

  const results = await new AxeBuilder({ page })
    .include('#wpessential-admin-root')
    .analyze();

  const evidenceDirectory = path.resolve(process.cwd(), 'test-results');
  await fs.mkdir(evidenceDirectory, { recursive: true });
  await fs.writeFile(
    path.join(evidenceDirectory, 'axe-cpt-builder.json'),
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
    `Axe violations in WPE-owned CPT Builder:\n${JSON.stringify(summary, null, 2)}`,
  ).toEqual([]);
});
