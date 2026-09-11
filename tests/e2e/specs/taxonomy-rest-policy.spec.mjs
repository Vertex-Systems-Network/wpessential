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

async function diagnosticJson(page, selector) {
  const source = page.locator(selector);
  await expect(source).toBeVisible();
  const value = await source.textContent();
  expect(value).not.toBeNull();
  return JSON.parse(value ?? '{}');
}

async function ensureDetailsOpen(page, label) {
  const summary = page.getByText(label, { exact: true });
  const details = summary.locator('..');
  if ((await details.getAttribute('open')) === null) {
    await summary.click();
  }
  await expect(details).toHaveAttribute('open', '');
}

async function waitForIdle(page) {
  await expect(page.locator('#wpessential-taxonomy-root')).not.toHaveAttribute(
    'aria-busy',
    'true',
  );
}

async function validate(page) {
  await page.getByRole('button', { name: 'Validate' }).click();
  await expect(
    page.locator('[data-wpessential-taxonomy-validation-summary]'),
  ).toContainText('Validation passed');
  await waitForIdle(page);
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

test('packaged Taxonomy Advanced UX authors, diagnoses, hydrates and resets REST policy', async ({ page }) => {
  await visitTaxonomies(page);

  const findSetting = page.getByLabel('Find setting');
  await findSetting.fill('REST namespace');
  await expect(
    page.getByRole('button', { name: 'REST namespace', exact: true }),
  ).toBeVisible();
  await page.getByRole('button', { name: 'REST namespace', exact: true }).click();
  await expect(page.getByRole('button', { name: 'Advanced', exact: true })).toHaveAttribute(
    'aria-pressed',
    'true',
  );
  await expect(page.getByLabel('REST namespace')).toBeFocused();

  await page.getByLabel('Taxonomy key').fill('rest_genre');
  await page.getByLabel('Plural name').fill('REST Genres');
  await page.getByLabel('Singular name').fill('REST Genre');
  await expect(page.getByLabel('Show in REST API')).toBeChecked();
  await page.getByLabel('REST base').fill('genres');
  await page.getByLabel('REST namespace').fill('acme/v1');
  await page.getByLabel('Lifecycle status').selectOption('published');
  await validate(page);

  await expect(
    page.locator('[data-wpessential-taxonomy-diagnostic="rest-route"]'),
  ).toHaveText('/acme/v1/genres');
  const effective = await diagnosticJson(
    page,
    '[data-wpessential-taxonomy-effective-args]',
  );
  expect(effective).toMatchObject({
    show_in_rest: true,
    rest_base: 'genres',
    rest_namespace: 'acme/v1',
  });

  await ensureDetailsOpen(page, 'Explicit overrides');
  const authored = await diagnosticJson(
    page,
    '[data-wpessential-taxonomy-overrides]',
  );
  expect(authored).toMatchObject({
    show_in_rest: true,
    rest_base: 'genres',
    rest_namespace: 'acme/v1',
  });

  await page.getByLabel('Show in REST API').uncheck();
  await validate(page);
  const issues = page.locator('[data-wpessential-taxonomy-validation-issues]');
  await expect(issues).toContainText('block-editor object type(s) "post"');
  await expect(
    page.locator('[data-wpessential-taxonomy-diagnostic="rest-route"]'),
  ).toHaveText('Disabled');

  await page.getByLabel('Show in REST API').check();
  await validate(page);
  await page.getByRole('button', { name: 'Save taxonomy' }).click();
  const savedRow = page.locator('[data-wpessential-taxonomy-row]');
  await expect(savedRow).toContainText('rest_genre');
  await expect(savedRow).toContainText('Published');
  await waitForIdle(page);

  await page.getByRole('button', { name: 'Edit', exact: true }).click();
  await page.getByRole('button', { name: 'Advanced', exact: true }).click();
  await expect(page.getByLabel('REST base')).toHaveValue('genres');
  await expect(page.getByLabel('REST namespace')).toHaveValue('acme/v1');
  await expect(page.getByLabel('Show in REST API')).toBeChecked();

  await page.getByRole('button', { name: 'Cancel edit' }).click();
  await page.getByRole('button', { name: 'Advanced', exact: true }).click();
  await page.getByLabel('Taxonomy key').fill('rest_topic');
  await page.getByLabel('Plural name').fill('REST Topics');
  await page.getByLabel('Singular name').fill('REST Topic');
  await expect(page.getByLabel('REST base')).toHaveValue('');
  await expect(page.getByLabel('REST namespace')).toHaveValue('');
  await validate(page);
  await ensureDetailsOpen(page, 'Explicit overrides');
  const resetOverrides = await diagnosticJson(
    page,
    '[data-wpessential-taxonomy-overrides]',
  );
  expect(resetOverrides).not.toHaveProperty('rest_base');
  expect(resetOverrides).not.toHaveProperty('rest_namespace');

  const accessibility = await new AxeBuilder({ page })
    .include('#wpessential-taxonomy-root')
    .analyze();
  expect(
    accessibility.violations,
    `Axe violations with REST policy controls visible: ${JSON.stringify(
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
