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

// The editor (Add/Edit) and the list are separate pages.
async function visitCpts(page, slug = 'wpessential-cpt') {
  const response = await page.goto(
    `${playground.serverUrl}/wp-admin/admin.php?page=${slug}`,
    { waitUntil: 'domcontentloaded' },
  );

  expect(response, 'CPT Builder navigation should return a response.').not.toBeNull();
  expect(response?.ok(), 'CPT Builder should return a successful HTTP response.').toBe(true);

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
  await expect(page.getByRole('link', { name: '‹ All post types' })).toBeVisible();
  await expect(root).toHaveAttribute('data-wpessential-surface', 'custom-post-types');
  await expect(root).toHaveAttribute('data-wpessential-enhanced', 'ready');

  // The list is its own page, with a way to add a new post type.
  const listRoot = await visitCpts(page, 'wpessential-post-types');
  await expect(page.getByRole('table', { name: 'Saved custom post types' })).toBeVisible();
  await expect(page.getByText('No custom post types have been created yet.')).toBeVisible();
  await expect(page.getByLabel('Post type key')).toHaveCount(0);
  await expect(listRoot).toHaveAttribute('data-wpessential-enhanced', 'ready');
  await page.getByRole('link', { name: 'Add new post type' }).click();
  await expect(page.getByRole('heading', { name: 'Add custom post type', level: 2 })).toBeVisible();
  expect(pageErrors, `Unexpected CPT Builder browser errors: ${pageErrors.join(' | ')}`).toEqual([]);
});

test('bounded CPT editor preserves hidden runtime options and supports in its save contract', async ({ page }) => {
  const seeded = {
    id: '11111111-1111-4111-8111-111111111111',
    status: 'draft',
    revision: 1,
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
  };
  let currentDefinition = seeded;
  let capturedSavePayload = null;

  await visitCpts(page, 'wpessential-post-types');
  await page.route('**/wp-admin/admin-ajax.php', async (route) => {
    const request = route.request();
    if (request.method() !== 'POST') {
      await route.continue();
      return;
    }

    const body = new URLSearchParams(request.postData() ?? '');
    const type = body.get('type');
    if (type === 'cpt.list') {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({ success: true, data: { definitions: [currentDefinition] } }),
      });
      return;
    }

    if (type === 'cpt.validate') {
      const payload = JSON.parse(body.get('payload_json') ?? '{}');
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          success: true,
          data: {
            valid: true,
            issues: [],
            candidate: {
              post_type_key: payload?.payload?.post_type_key ?? null,
            },
          },
        }),
      });
      return;
    }

    if (type === 'cpt.save') {
      capturedSavePayload = JSON.parse(body.get('payload_json') ?? '{}');
      currentDefinition = {
        ...seeded,
        status: capturedSavePayload.status,
        revision: 2,
        payload: capturedSavePayload.payload,
      };
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({ success: true, data: { definition: currentDefinition } }),
      });
      return;
    }

    await route.continue();
  });

  await page.getByRole('button', { name: 'Refresh' }).click();
  await expect(page.getByText('Custom post types refreshed.')).toBeVisible();

  const row = page.locator(`tr[data-wpessential-cpt-row="${seeded.id}"]`);
  await expect(row).toBeVisible();
  await expect(row).toContainText('RC Books');
  await expect(row).toContainText('rc_book');
  // Edit opens the separate editor page for this definition.
  await row.getByRole('button', { name: 'Edit' }).click();
  await page.waitForURL(/page=wpessential-cpt&definition=/);

  await expect(page.getByLabel('Post type key')).toHaveAttribute('readonly', '');
  await expect(page.getByLabel('Plural name')).toHaveValue('RC Books');
  await expect(page.getByLabel('Title')).toBeChecked();
  await expect(page.getByLabel('Editor')).toBeChecked();

  await page.getByLabel('Plural name').fill('RC Books Updated');
  await page.getByLabel('Editor').uncheck();
  await page.getByRole('button', { name: 'Save custom post type' }).click();

  await expect(page.getByText('Custom post type updated.')).toBeVisible();
  expect(capturedSavePayload, 'The packaged editor should emit a save request.').toBeTruthy();
  expect(capturedSavePayload.id).toBe(seeded.id);
  expect(capturedSavePayload.expected_revision).toBe(1);
  expect(capturedSavePayload.payload.name).toBe('RC Books Updated');
  expect(capturedSavePayload.payload.has_archive).toBe('rc-books');
  expect(capturedSavePayload.payload.rewrite).toEqual({ slug: 'rc/library', with_front: false });
  expect(capturedSavePayload.payload.query_var).toBe('rc_book');
  expect(capturedSavePayload.payload.can_export).toBe(false);
  expect(capturedSavePayload.payload.delete_with_user).toBe(true);
  expect(capturedSavePayload.payload.menu_icon).toBe('dashicons-book-alt');
  expect(capturedSavePayload.payload.supports).toEqual(expect.arrayContaining(['title', 'author']));
  expect(capturedSavePayload.payload.supports).not.toContain('editor');
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

test('CPT list page has WordPress-style views, search and sortable columns', async ({ page }) => {
  const statuses = ['published', 'draft', 'disabled'];
  const definitions = Array.from({ length: 25 }, (_, index) => ({
    id: `22222222-2222-4222-8222-${String(index).padStart(12, '0')}`,
    status: statuses[index % 3],
    revision: index + 1,
    payload: { post_type_key: `list_${index}`, name: `List type ${index}`, singular_name: `List type ${index}` },
  }));
  await visitCpts(page, 'wpessential-post-types');
  await page.route('**/wp-admin/admin-ajax.php', async (route) => {
    const body = new URLSearchParams(route.request().postData() ?? '');
    if (body.get('type') !== 'cpt.list') {
      await route.continue();
      return;
    }
    await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ success: true, data: { definitions } }) });
  });
  await page.getByRole('button', { name: 'Refresh' }).click();

  const rows = page.locator('[data-wpessential-cpt-row]:visible');
  await expect(rows).toHaveCount(20);
  await expect(page.locator('.wpe-ui-tablenav__count').first()).toHaveText('25 items');

  // Status views with counts, like WordPress list tables.
  const views = page.getByRole('navigation', { name: 'Filter by status' });
  await expect(views).toContainText('All25');
  await views.getByRole('link', { name: /^Draft/ }).click();
  await expect(rows).toHaveCount(8);
  await views.getByRole('link', { name: /^All/ }).click();

  // Search runs on the Search button.
  await page.getByRole('searchbox', { name: 'Search Post Types' }).fill('List type 12');
  await page.getByRole('button', { name: 'Search Post Types' }).click();
  await expect(rows).toHaveCount(1);
  await page.getByRole('searchbox', { name: 'Search Post Types' }).fill('');
  await page.getByRole('button', { name: 'Search Post Types' }).click();
  await expect(rows).toHaveCount(20);

  // Paging: 20 per page with « ‹ n of N › » controls.
  await page.getByRole('button', { name: 'Next page' }).first().click();
  await expect(rows).toHaveCount(5);
  await page.getByRole('button', { name: 'First page' }).first().click();

  // Sortable columns.
  const revision = page.locator('thead').getByRole('columnheader', { name: 'Revision' });
  await revision.getByRole('button').click();
  await revision.getByRole('button').click();
  await expect(revision).toHaveAttribute('aria-sort', 'descending');
  await expect(rows.first()).toContainText('List type 24');

  const results = await new AxeBuilder({ page }).include('#wpessential-admin-root').analyze();
  expect(results.violations.map((violation) => violation.id)).toEqual([]);
});

test('CPT list trashes, restores, locks and deletes like WordPress, with our confirm dialog', async ({ page }) => {
  const make = (index, status, extra = {}) => ({
    id: `33333333-3333-4333-8333-${String(index).padStart(12, '0')}`,
    status,
    revision: 1,
    payload: { post_type_key: `del_${index}`, name: `Delete type ${index}`, singular_name: `Delete type ${index}`, ...extra },
  });
  let definitions = [make(1, 'draft'), make(2, 'draft', { locked: true }), make(3, 'disabled', { trashed: true }), make(4, 'draft', { trashed: true })];
  const calls = [];
  await visitCpts(page, 'wpessential-post-types');
  await page.route('**/wp-admin/admin-ajax.php', async (route) => {
    const body = new URLSearchParams(route.request().postData() ?? '');
    const type = body.get('type') ?? '';
    if (type === 'cpt.list') {
      await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ success: true, data: { definitions } }) });
      return;
    }
    if (['cpt.trash', 'cpt.restore', 'cpt.lock', 'cpt.delete'].includes(type)) {
      const input = JSON.parse(body.get('payload_json') ?? '{}');
      calls.push([type, input]);
      definitions = definitions.flatMap((definition) => {
        if (definition.id !== input.id) return [definition];
        if (type === 'cpt.delete') return [];
        const payload = { ...definition.payload };
        if (type === 'cpt.trash') payload.trashed = true;
        if (type === 'cpt.restore') delete payload.trashed;
        if (type === 'cpt.lock') payload.locked = input.locked;
        return [{ ...definition, revision: definition.revision + 1, payload }];
      });
      await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ success: true, data: {} }) });
      return;
    }
    await route.continue();
  });
  await page.getByRole('button', { name: 'Refresh' }).click();

  const views = page.locator('.wpe-ui-views');
  await expect(views).toContainText('All2');
  await expect(views).toContainText('Locked1');
  await expect(views).toContainText('Trash2');
  const row = (index) => page.locator(`tr[data-wpessential-cpt-row="${make(index).id}"]`);
  await expect(row(2)).toContainText('Locked');
  await expect(row(2).getByRole('button', { name: 'Trash' })).toBeDisabled();

  // Trash asks in our own dialog; Cancel changes nothing.
  await row(1).getByRole('button', { name: 'Trash' }).click();
  const dialog = page.locator('dialog.wpe-ui-dialog');
  await expect(dialog).toContainText('Move this custom post type to the Trash?');
  await dialog.getByRole('button', { name: 'Cancel' }).click();
  expect(calls).toEqual([]);
  await row(1).getByRole('button', { name: 'Trash' }).click();
  await dialog.getByRole('button', { name: 'Move to Trash' }).click();
  await expect(page.getByText('1 custom post type moved to the Trash.')).toBeVisible();

  // Unlock (no confirmation needed).
  await row(2).getByRole('button', { name: 'Unlock' }).click();
  await expect(page.getByText('1 custom post type unlocked.')).toBeVisible();

  // Trash view: Restore one, then bulk-delete the rest permanently.
  await views.getByRole('link', { name: /^Trash/ }).click();
  await row(4).getByRole('button', { name: 'Restore' }).click();
  await expect(page.getByText('1 custom post type restored.')).toBeVisible();
  await page.locator('thead .wpe-ui-table__check input').check();
  await page.getByRole('combobox', { name: 'Bulk actions' }).selectOption('delete');
  await page.getByRole('button', { name: 'Apply' }).click();
  await expect(dialog).toContainText('Delete 2 custom post types permanently?');
  await dialog.getByRole('button', { name: 'Delete permanently' }).click();
  await expect(page.getByText('2 custom post types deleted permanently.')).toBeVisible();
  expect(calls.map(([type]) => type)).toEqual(['cpt.trash', 'cpt.lock', 'cpt.restore', 'cpt.delete', 'cpt.delete']);
  expect(calls[1][1]).toEqual({ id: make(2).id, expected_revision: 1, locked: false });
  await expect(page.locator('tfoot')).toContainText('Revision');
});

test('CPT editor fills the singular name, key and labels from the plural name, with counters', async ({ page }) => {
  await visitCpts(page);
  await page.getByLabel('Plural name').fill('Movies');
  await expect(page.getByLabel('Singular name')).toHaveValue('Movie');
  await expect(page.getByLabel('Post type key')).toHaveValue('movie');
  await expect(page.locator('#wpessential-cpt-key-count')).toHaveText('5 / 20');
  await expect(page.locator('#wpessential-cpt-label-add_new_item')).toHaveValue('Add New Movie');
  await expect(page.locator('#wpessential-cpt-label-not_found')).toHaveValue('No movies found.');
  await expect(page.locator('[data-wpessential-cpt-opt="show_ui"] option[value=""]')).toHaveText('Default: Yes (as Public)');
});
