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

  expect(response).not.toBeNull();
  expect(response?.ok()).toBe(true);
  await expect(page.locator('#wpessential-taxonomy-root')).toHaveAttribute(
    'data-wpessential-enhanced',
    'ready',
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

test('CPT UI import preview maps safely and commits only after explicit valid preview', async ({ page }) => {
  await visitTaxonomies(page);
  await page.getByRole('button', { name: 'Advanced', exact: true }).click();

  const section = page.locator('#wpessential-taxonomy-cptui-preview');
  await expect(section).toBeVisible();
  await expect(
    page.locator('#wpessential-taxonomy-import-preview-bootstrap'),
  ).toHaveCount(1);
  await expect(page.locator('[data-wpessential-taxonomy-empty]')).toContainText(
    'No taxonomies have been created yet.',
  );

  const createImport = page.getByRole('button', { name: 'Import as new draft' });
  const updateImport = page.getByRole('button', { name: 'Update current definition' });
  await expect(createImport).toBeHidden();
  await expect(updateImport).toBeHidden();

  const source = page.getByLabel('CPT UI taxonomy JSON');
  await source.fill(
    JSON.stringify({
      name: 'book_genre',
      label: 'Book Genres',
      singular_label: 'Book Genre',
      description: 'Imported from CPT UI',
      object_types: ['post'],
      public: '1',
      show_in_rest: '1',
      hierarchical: '0',
      rewrite: '1',
      rewrite_slug: 'library/genre',
      rewrite_withfront: '0',
      query_var: '1',
      query_var_slug: 'book_genre',
      rest_base: 'genres',
      rest_namespace: 'acme/v1',
      labels: { menu_name: 'Book Genres' },
      future_cptui_field: 'warning-only',
    }),
  );
  await page.getByRole('button', { name: 'Preview mapping' }).click();

  const status = page.locator('#wpessential-taxonomy-cptui-preview-status');
  const commitStatus = page.locator('#wpessential-taxonomy-cptui-import-status');
  await expect(status).toContainText('Preview passed canonical Taxonomy validation');
  await expect(page.locator('#wpessential-taxonomy-cptui-preview-issues')).toContainText(
    'Unsupported CPT UI field(s) were not mapped: future_cptui_field.',
  );
  await expect(page.locator('#wpessential-taxonomy-cptui-preview-payload')).toContainText(
    '"taxonomy_key": "book_genre"',
  );
  await expect(createImport).toBeVisible();
  await expect(updateImport).toBeHidden();
  await expect(commitStatus).toContainText('imported as a new draft');

  const apply = page.getByRole('button', { name: 'Apply preview to editor' });
  await expect(apply).toBeVisible();
  await apply.click();
  await expect(status).toContainText('Nothing has been saved');
  await expect(page.getByLabel('Taxonomy key')).toHaveValue('book_genre');
  await expect(page.getByLabel('Plural name')).toHaveValue('Book Genres');
  await expect(page.getByLabel('Singular name')).toHaveValue('Book Genre');
  await expect(page.getByLabel('REST base')).toHaveValue('genres');
  await expect(page.getByLabel('REST namespace')).toHaveValue('acme/v1');
  await expect(page.locator('[data-wpessential-taxonomy-empty]')).toContainText(
    'No taxonomies have been created yet.',
  );

  await createImport.click();
  await expect(commitStatus).toContainText(
    'Taxonomy imported as a new draft through the canonical save path.',
  );

  await source.fill(
    JSON.stringify({
      name: 'changed_without_preview',
      label: 'Changed Without Preview',
      singular_label: 'Changed Without Preview',
      object_types: ['post'],
    }),
  );
  await expect(createImport).toBeHidden();
  await expect(updateImport).toBeHidden();
  await expect(commitStatus).toContainText('Source changed');

  await source.fill(
    JSON.stringify({
      name: 'unsafe_genre',
      label: 'Unsafe Genres',
      singular_label: 'Unsafe Genre',
      object_types: ['post'],
      rest_controller_class: 'Vendor\\UnsafeController',
    }),
  );
  await page.getByRole('button', { name: 'Preview mapping' }).click();
  await expect(status).toContainText('Preview is blocked');
  await expect(page.locator('#wpessential-taxonomy-cptui-preview-issues')).toContainText(
    'executable callback/class input',
  );
  await expect(apply).toBeHidden();
  await expect(createImport).toBeHidden();
  await expect(updateImport).toBeHidden();

  const accessibility = await new AxeBuilder({ page })
    .include('#wpessential-taxonomy-root')
    .analyze();
  expect(
    accessibility.violations,
    `Axe violations with CPT UI import commit controls visible: ${JSON.stringify(
      accessibility.violations.map((violation) => ({
        id: violation.id,
        impact: violation.impact,
        targets: violation.nodes.map((node) => node.target),
      })),
      null,
      2,
    )}`,
  ).toEqual([]);
});
