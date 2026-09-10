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

test('Find Setting reveals tiered and collapsed Taxonomy controls accessibly', async ({ page }) => {
  await visitTaxonomies(page);

  const search = page.getByLabel('Find setting');
  const results = page.locator('#wpessential-taxonomy-setting-results');
  const status = page.locator('#wpessential-taxonomy-setting-search-status');
  const advancedSection = page.locator('#wpessential-taxonomy-tier-advanced');
  const labels = page.locator('#wpessential-taxonomy-labels');

  await expect(search).toBeVisible();
  await expect(advancedSection).toBeHidden();

  await search.fill('publicly queryable');
  await expect(status).toContainText('1 taxonomy setting matched');
  await expect(results).toBeVisible();
  await page.getByRole('button', { name: 'Publicly queryable', exact: true }).click();
  await expect(advancedSection).toBeVisible();
  await expect(page.getByRole('button', { name: 'Advanced', exact: true })).toHaveAttribute(
    'aria-pressed',
    'true',
  );
  await expect(page.getByLabel('Publicly queryable')).toBeFocused();

  await search.fill('search items');
  await expect(labels).not.toHaveAttribute('open', '');
  await page.getByRole('button', { name: 'Search items', exact: true }).click();
  await expect(labels).toHaveAttribute('open', '');
  await expect(page.locator('#wpessential-taxonomy-label-search-items')).toBeFocused();

  await search.fill('lifecycle status');
  await search.press('Enter');
  await expect(page.getByLabel('Lifecycle status')).toBeFocused();

  await search.fill('not-a-real-taxonomy-setting');
  await expect(status).toHaveText('No taxonomy settings matched your search.');
  await expect(results).toBeHidden();

  const accessibility = await new AxeBuilder({ page })
    .include('#wpessential-taxonomy-root')
    .analyze();
  expect(
    accessibility.violations,
    `Axe violations with Find Setting active: ${JSON.stringify(
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