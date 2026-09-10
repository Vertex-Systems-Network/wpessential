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
  await expect(page.locator('#wpessential-taxonomy-root')).toHaveAttribute(
    'data-wpessential-enhanced',
    'ready',
  );
}

async function effectiveArgs(page) {
  const source = page.locator('[data-wpessential-taxonomy-effective-args]');
  await expect(source).toBeVisible();
  const value = await source.textContent();
  expect(value).not.toBeNull();
  return JSON.parse(value ?? '{}');
}

function labelRow(page, key) {
  return page.locator(`[data-wpessential-taxonomy-label-row="${key}"]`);
}

function labelInput(page, key) {
  return page.locator(`[data-wpessential-taxonomy-label-field="${key}"]`);
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

test('packaged Taxonomy label authoring reports truthful sources and exposes opt-out/reset semantics accessibly', async ({ page }) => {
  await visitTaxonomies(page);

  const labelEditor = page.locator('#wpessential-taxonomy-labels');
  const automatic = page.getByLabel('Generate adaptive labels automatically');
  const hierarchical = page.getByLabel('Hierarchical');
  const generationState = page.locator(
    '[data-wpessential-taxonomy-label-generation-state]',
  );
  const labelFields = page.locator('[data-wpessential-taxonomy-label-field]');

  await expect(labelEditor).not.toHaveAttribute('open', '');
  await page.getByText('Customize labels', { exact: true }).click();
  await expect(labelEditor).toHaveAttribute('open', '');
  await expect(labelFields).toHaveCount(28);
  await expect(automatic).toBeChecked();
  await expect(generationState).toContainText('tag-like');

  await expect(labelRow(page, 'menu_name')).toContainText('Generated');
  await expect(labelRow(page, 'popular_items')).toContainText('Generated');
  await expect(labelRow(page, 'parent_item')).toContainText('WordPress default');
  await expect(labelRow(page, 'template_name')).toContainText('WordPress default');
  await expect(labelRow(page, 'name_field_description')).toContainText('WordPress default');
  await expect(labelInput(page, 'menu_name')).toHaveAttribute(
    'aria-describedby',
    'wpessential-taxonomy-label-menu-name-state',
  );
  await expect(page.getByRole('button', { name: 'Reset Menu name' })).toBeVisible();

  await hierarchical.check();
  await expect(generationState).toContainText('category-like');
  await expect(labelRow(page, 'parent_item')).toContainText('Generated');
  await expect(labelRow(page, 'popular_items')).toContainText('WordPress default');
  await expect(labelRow(page, 'template_name')).toContainText('WordPress default');

  await page.getByLabel('Taxonomy key').fill('authoring_genre');
  await page.getByLabel('Plural name').fill('Genres');
  await page.getByLabel('Singular name').fill('Genre');
  await labelInput(page, 'menu_name').fill('Library Genres');
  await labelInput(page, 'not_found').fill('No library genres found');
  await expect(labelRow(page, 'menu_name')).toContainText('Explicit override');
  await expect(labelRow(page, 'not_found')).toContainText('Explicit override');

  await page.getByRole('button', { name: 'Validate' }).click();
  await expect(page.locator('#wpessential-taxonomy-diagnostics')).toBeVisible();
  const overridden = await effectiveArgs(page);
  expect(overridden.labels).toMatchObject({
    menu_name: 'Library Genres',
    not_found: 'No library genres found',
    parent_item: 'Parent Genre',
    parent_item_colon: 'Parent Genre:',
  });
  expect(overridden.labels).not.toHaveProperty('popular_items');
  expect(overridden.labels).not.toHaveProperty('template_name');

  await page.getByRole('button', { name: 'Reset Menu name' }).click();
  await expect(labelInput(page, 'menu_name')).toHaveValue('');
  await expect(labelRow(page, 'menu_name')).toContainText('Generated');
  await expect(page.locator('#wpessential-taxonomy-diagnostics')).toBeHidden();

  await automatic.uncheck();
  await expect(generationState).toContainText('WordPress defaults');
  await expect(labelRow(page, 'menu_name')).toContainText('WordPress default');
  await expect(labelRow(page, 'parent_item')).toContainText('WordPress default');
  await labelInput(page, 'menu_name').fill('Saved Genres');
  await expect(labelRow(page, 'menu_name')).toContainText('Explicit override');

  await page.getByRole('button', { name: 'Validate' }).click();
  const defaultsOnly = await effectiveArgs(page);
  expect(defaultsOnly.labels).toMatchObject({
    name: 'Genres',
    singular_name: 'Genre',
    menu_name: 'Saved Genres',
    not_found: 'No library genres found',
  });
  expect(defaultsOnly.labels).not.toHaveProperty('parent_item');
  expect(defaultsOnly.labels).not.toHaveProperty('popular_items');

  await page.getByRole('button', { name: 'Save taxonomy' }).click();
  await expect(page.getByText('Taxonomy created.')).toBeVisible();
  await expect(labelEditor).not.toHaveAttribute('open', '');

  const row = page.getByRole('row', {
    name: /Genres authoring_genre post Draft 1/,
  });
  await expect(row).toBeVisible();
  await row.getByRole('button', { name: 'Edit' }).click();

  await expect(labelEditor).toHaveAttribute('open', '');
  await expect(automatic).not.toBeChecked();
  await expect(labelInput(page, 'menu_name')).toHaveValue('Saved Genres');
  await expect(labelInput(page, 'not_found')).toHaveValue('No library genres found');
  await expect(labelRow(page, 'menu_name')).toContainText('Explicit override');
  await expect(labelRow(page, 'template_name')).toContainText('WordPress default');

  await page.getByRole('button', { name: 'Reset Menu name' }).click();
  await expect(labelInput(page, 'menu_name')).toHaveValue('');
  await expect(labelRow(page, 'menu_name')).toContainText('WordPress default');
  await labelInput(page, 'menu_name').fill('Temporary Genres');
  await page.getByRole('button', { name: 'Reset all label overrides' }).click();
  await expect(labelInput(page, 'menu_name')).toHaveValue('');
  await expect(labelInput(page, 'not_found')).toHaveValue('');
  await expect(labelRow(page, 'menu_name')).toContainText('WordPress default');
  await expect(labelRow(page, 'not_found')).toContainText('WordPress default');
  await expect(automatic).not.toBeChecked();

  await page.getByRole('button', { name: 'Validate' }).click();
  await expect(page.locator('#wpessential-taxonomy-diagnostics')).toBeVisible();
  const resetDefaults = await effectiveArgs(page);
  expect(resetDefaults.labels).toMatchObject({
    name: 'Genres',
    singular_name: 'Genre',
  });
  expect(resetDefaults.labels).not.toHaveProperty('menu_name');
  expect(resetDefaults.labels).not.toHaveProperty('not_found');
  expect(resetDefaults.labels).not.toHaveProperty('parent_item');
  expect(resetDefaults.labels).not.toHaveProperty('popular_items');

  const accessibility = await new AxeBuilder({ page })
    .include('#wpessential-taxonomy-root')
    .analyze();
  expect(
    accessibility.violations,
    `Axe violations with Taxonomy label editor open: ${JSON.stringify(
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
