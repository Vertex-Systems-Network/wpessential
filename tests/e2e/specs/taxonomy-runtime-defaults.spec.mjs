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

test('packaged Taxonomy Expert UX authors, resets and searches bounded runtime defaults', async ({ page }) => {
  await visitTaxonomies(page);

  const expert = page.getByRole('button', { name: 'Expert', exact: true });
  const expertSection = page.locator('#wpessential-taxonomy-tier-expert');
  const runtimeDefaults = page.locator('#wpessential-taxonomy-runtime-defaults');

  await expect(expertSection).toBeHidden();

  const findSetting = page.getByLabel('Find setting');
  await findSetting.fill('Default term name');
  await expect(
    page.getByRole('button', { name: 'Default term name', exact: true }),
  ).toBeVisible();
  await page
    .getByRole('button', { name: 'Default term name', exact: true })
    .click();
  await expect(expert).toHaveAttribute('aria-pressed', 'true');
  await expect(expertSection).toBeVisible();
  await expect(runtimeDefaults).toBeVisible();
  await expect(page.getByLabel('Default term name')).toBeFocused();

  await page.getByLabel('Taxonomy key').fill('runtime_genre');
  await page.getByLabel('Plural name').fill('Genres');
  await page.getByLabel('Singular name').fill('Genre');
  await page.getByLabel('Default term name').fill('General');
  await page.getByLabel('Default term slug').fill('general');
  await page
    .getByLabel('Default term description')
    .fill('Fallback classification.');
  await page
    .getByLabel('Preserve object-term sort order')
    .selectOption('true');
  await page.getByLabel('Object-term order by').selectOption('name');
  await page
    .getByLabel('Object-term order direction')
    .selectOption('DESC');
  await page.getByLabel('Object-term fields mode').selectOption('slugs');

  await validate(page);

  const effective = await diagnosticJson(
    page,
    '[data-wpessential-taxonomy-effective-args]',
  );
  expect(effective).toMatchObject({
    sort: true,
    default_term: {
      name: 'General',
      slug: 'general',
      description: 'Fallback classification.',
    },
    args: {
      orderby: 'name',
      order: 'DESC',
      fields: 'slugs',
    },
  });

  await page.getByText('Explicit overrides', { exact: true }).click();
  const authored = await diagnosticJson(
    page,
    '[data-wpessential-taxonomy-overrides]',
  );
  expect(authored).toMatchObject({
    sort: true,
    default_term: {
      name: 'General',
      slug: 'general',
      description: 'Fallback classification.',
    },
    args: {
      orderby: 'name',
      order: 'DESC',
      fields: 'slugs',
    },
  });

  await page.getByLabel('Default term name').fill('');
  await page.getByLabel('Default term slug').fill('');
  await page.getByLabel('Default term description').fill('');
  await page
    .getByLabel('Preserve object-term sort order')
    .selectOption('inherit');
  await page.getByLabel('Object-term order by').selectOption('');
  await page.getByLabel('Object-term order direction').selectOption('');
  await page.getByLabel('Object-term fields mode').selectOption('');

  await validate(page);

  const resetEffective = await diagnosticJson(
    page,
    '[data-wpessential-taxonomy-effective-args]',
  );
  expect(resetEffective.sort).toBe(false);
  expect(resetEffective).not.toHaveProperty('default_term');
  expect(resetEffective).not.toHaveProperty('args');

  const resetOverrides = await diagnosticJson(
    page,
    '[data-wpessential-taxonomy-overrides]',
  );
  expect(resetOverrides).not.toHaveProperty('sort');
  expect(resetOverrides).not.toHaveProperty('default_term');
  expect(resetOverrides).not.toHaveProperty('args');

  await page.getByLabel('Default term slug').fill('missing-name');
  await page.getByRole('button', { name: 'Validate' }).click();
  await expect(
    page.locator('[data-wpessential-taxonomy-validation-summary]'),
  ).toContainText('Validation blocked');
  await expect(page.locator('[data-wpessential-taxonomy-validation-issues]')).toContainText(
    'default_term.name is required',
  );

  await page.getByLabel('Default term slug').fill('');
  const accessibility = await new AxeBuilder({ page })
    .include('#wpessential-taxonomy-root')
    .analyze();
  expect(
    accessibility.violations,
    `Axe violations with bounded runtime-default controls visible: ${JSON.stringify(
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
