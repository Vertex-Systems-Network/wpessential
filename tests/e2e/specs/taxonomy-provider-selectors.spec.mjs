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

async function validate(page) {
  await page.getByRole('button', { name: 'Validate' }).click();
  await expect(
    page.locator('[data-wpessential-taxonomy-validation-summary]'),
  ).toContainText('Validation passed');
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

test('packaged Taxonomy Expert UX exposes only registered redacted provider IDs and preserves reset semantics', async ({ page }) => {
  await visitTaxonomies(page);

  const findSetting = page.getByLabel('Find setting');
  await findSetting.fill('Meta-box provider');
  await expect(
    page.getByRole('button', { name: 'Meta-box provider', exact: true }),
  ).toBeVisible();
  await page
    .getByRole('button', { name: 'Meta-box provider', exact: true })
    .click();

  const metaBox = page.getByLabel('Meta-box provider');
  await expect(page.getByRole('button', { name: 'Expert', exact: true })).toHaveAttribute(
    'aria-pressed',
    'true',
  );
  await expect(metaBox).toBeFocused();

  await expect(metaBox.locator('option[value="wordpress.disabled"]')).toContainText(
    'available',
  );
  await expect(page.locator('#wpessential-taxonomy-runtime-providers')).not.toContainText(
    'WP_REST_Terms_Controller',
  );

  await page.getByLabel('Taxonomy key').fill('provider_genre');
  await page.getByLabel('Plural name').fill('Genres');
  await page.getByLabel('Singular name').fill('Genre');
  await metaBox.selectOption('wordpress.disabled');
  await expect(
    page.locator('[data-wpessential-taxonomy-provider-state="meta_box"]'),
  ).toContainText('Available registered provider');

  await validate(page);
  await expect(
    page.locator('[data-wpessential-taxonomy-diagnostic="providers"]'),
  ).toContainText('meta_box: wordpress.disabled');

  await page.getByRole('button', { name: 'Save taxonomy' }).click();
  await expect(page.locator('[data-wpessential-taxonomy-row]')).toContainText(
    'provider_genre',
  );
  await page.getByRole('button', { name: 'Edit', exact: true }).click();
  await page.getByRole('button', { name: 'Expert', exact: true }).click();
  await expect(page.getByLabel('Meta-box provider')).toHaveValue('wordpress.disabled');

  await page.getByLabel('Meta-box provider').selectOption('');
  await validate(page);
  await expect(
    page.locator('[data-wpessential-taxonomy-diagnostic="providers"]'),
  ).toHaveText('WordPress defaults');

  const accessibility = await new AxeBuilder({ page })
    .include('#wpessential-taxonomy-root')
    .analyze();
  expect(
    accessibility.violations,
    `Axe violations with controlled provider selectors visible: ${JSON.stringify(
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
