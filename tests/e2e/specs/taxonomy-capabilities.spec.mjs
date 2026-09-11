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

test('packaged Taxonomy Expert UX authors, diagnoses, hydrates and resets native capability maps', async ({ page }) => {
  await visitTaxonomies(page);

  const findSetting = page.getByLabel('Find setting');
  await findSetting.fill('Manage terms capability');
  await expect(
    page.getByRole('button', { name: 'Manage terms capability', exact: true }),
  ).toBeVisible();
  await page.getByRole('button', { name: 'Manage terms capability', exact: true }).click();
  await expect(page.getByRole('button', { name: 'Expert', exact: true })).toHaveAttribute(
    'aria-pressed',
    'true',
  );
  await expect(page.getByLabel('Manage terms capability')).toBeFocused();

  await page.getByLabel('Taxonomy key').fill('cap_genre');
  await page.getByLabel('Plural name').fill('Capability Genres');
  await page.getByLabel('Singular name').fill('Capability Genre');
  await page.getByLabel('Manage terms capability').fill('manage_library_genres');
  await page.getByLabel('Edit terms capability').fill('edit_library_genres');
  await page.getByLabel('Delete terms capability').fill('delete_library_genres');
  await page.getByLabel('Assign terms capability').fill('assign_library_genres');
  await page.getByLabel('Lifecycle status').selectOption('published');
  await validate(page);

  const issues = page.locator('[data-wpessential-taxonomy-validation-issues]');
  await expect(issues).toContainText('Current WordPress user does not have effective taxonomy capability check(s)');
  await expect(issues).toContainText('manage_terms=manage_library_genres');
  await expect(issues).toContainText('assign_terms=assign_library_genres');

  const effective = await diagnosticJson(
    page,
    '[data-wpessential-taxonomy-effective-args]',
  );
  expect(effective.capabilities).toEqual({
    manage_terms: 'manage_library_genres',
    edit_terms: 'edit_library_genres',
    delete_terms: 'delete_library_genres',
    assign_terms: 'assign_library_genres',
  });

  await ensureDetailsOpen(page, 'Explicit overrides');
  const authored = await diagnosticJson(
    page,
    '[data-wpessential-taxonomy-overrides]',
  );
  expect(authored.capabilities).toEqual({
    manage_terms: 'manage_library_genres',
    edit_terms: 'edit_library_genres',
    delete_terms: 'delete_library_genres',
    assign_terms: 'assign_library_genres',
  });

  await page.getByRole('button', { name: 'Save taxonomy' }).click();
  const savedRow = page.locator('[data-wpessential-taxonomy-row]');
  await expect(savedRow).toContainText('cap_genre');
  await expect(savedRow).toContainText('Published');
  await waitForIdle(page);

  await page.getByRole('button', { name: 'Edit', exact: true }).click();
  await page.getByRole('button', { name: 'Expert', exact: true }).click();
  await expect(page.getByLabel('Manage terms capability')).toHaveValue('manage_library_genres');
  await expect(page.getByLabel('Edit terms capability')).toHaveValue('edit_library_genres');
  await expect(page.getByLabel('Delete terms capability')).toHaveValue('delete_library_genres');
  await expect(page.getByLabel('Assign terms capability')).toHaveValue('assign_library_genres');

  await page.getByRole('button', { name: 'Cancel edit' }).click();
  await page.getByRole('button', { name: 'Expert', exact: true }).click();
  await page.getByLabel('Taxonomy key').fill('cap_topic');
  await page.getByLabel('Plural name').fill('Capability Topics');
  await page.getByLabel('Singular name').fill('Capability Topic');
  await expect(page.getByLabel('Manage terms capability')).toHaveValue('');
  await expect(page.getByLabel('Edit terms capability')).toHaveValue('');
  await expect(page.getByLabel('Delete terms capability')).toHaveValue('');
  await expect(page.getByLabel('Assign terms capability')).toHaveValue('');
  await validate(page);

  const defaultEffective = await diagnosticJson(
    page,
    '[data-wpessential-taxonomy-effective-args]',
  );
  expect(defaultEffective.capabilities).toEqual({
    manage_terms: 'manage_categories',
    edit_terms: 'manage_categories',
    delete_terms: 'manage_categories',
    assign_terms: 'edit_posts',
  });
  await ensureDetailsOpen(page, 'Explicit overrides');
  const resetOverrides = await diagnosticJson(
    page,
    '[data-wpessential-taxonomy-overrides]',
  );
  expect(resetOverrides).not.toHaveProperty('capabilities');

  const accessibility = await new AxeBuilder({ page })
    .include('#wpessential-taxonomy-root')
    .analyze();
  expect(
    accessibility.violations,
    `Axe violations with capability controls visible: ${JSON.stringify(
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
