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

test('packaged Taxonomy validation adaptively generates hierarchical and flat labels', async ({ page }) => {
  await visitTaxonomies(page);

  await page.getByLabel('Taxonomy key').fill('adaptive_genre');
  await page.getByLabel('Plural name').fill('Genres');
  await page.getByLabel('Singular name').fill('Genre');

  const hierarchical = page.getByLabel('Hierarchical');
  await hierarchical.check();
  await page.getByRole('button', { name: 'Validate' }).click();
  await expect(page.locator('#wpessential-taxonomy-diagnostics')).toBeVisible();

  const categoryLike = await effectiveArgs(page);
  expect(categoryLike.labels).toMatchObject({
    name: 'Genres',
    singular_name: 'Genre',
    parent_item: 'Parent Genre',
    parent_item_colon: 'Parent Genre:',
  });
  expect(categoryLike.labels).not.toHaveProperty('popular_items');
  expect(categoryLike.labels).not.toHaveProperty('separate_items_with_commas');

  await hierarchical.uncheck();
  await page.getByRole('button', { name: 'Validate' }).click();
  await expect(page.locator('#wpessential-taxonomy-diagnostics')).toBeVisible();

  const tagLike = await effectiveArgs(page);
  expect(tagLike.labels).toMatchObject({
    name: 'Genres',
    singular_name: 'Genre',
    popular_items: 'Popular Genres',
    separate_items_with_commas: 'Separate Genres with commas',
    add_or_remove_items: 'Add or remove Genres',
    choose_from_most_used: 'Choose from the most used Genres',
  });
  expect(tagLike.labels).not.toHaveProperty('parent_item');
  expect(tagLike.labels).not.toHaveProperty('parent_item_colon');
});
