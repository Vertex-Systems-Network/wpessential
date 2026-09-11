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

async function validate(page) {
  await page.getByRole('button', { name: 'Validate' }).click();
  await expect(
    page.locator('[data-wpessential-taxonomy-validation-summary]'),
  ).toContainText('Validation passed');
}

async function authorRoutingPolicy(page, { key, plural, singular }) {
  await page.getByLabel('Taxonomy key').fill(key);
  await page.getByLabel('Plural name').fill(plural);
  await page.getByLabel('Singular name').fill(singular);
  await page.getByLabel('Rewrite mode').selectOption('custom');
  await page.getByLabel('Rewrite slug').fill('library/genre');
  await page.getByLabel('Use front base').selectOption('false');
  await page.getByLabel('Rewrite path hierarchy').selectOption('true');
  await page.getByLabel('Endpoint mask').fill('1');
  await page.getByLabel('Query variable mode').selectOption('custom');
  await page.getByLabel('Custom query variable name').fill('library_genre_query');
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

test('packaged Taxonomy Expert UX authors, hydrates and resets rewrite/query-var policy', async ({ page }) => {
  await visitTaxonomies(page);

  const findSetting = page.getByLabel('Find setting');
  await findSetting.fill('Rewrite mode');
  await expect(
    page.getByRole('button', { name: 'Rewrite mode', exact: true }),
  ).toBeVisible();
  await page.getByRole('button', { name: 'Rewrite mode', exact: true }).click();
  await expect(page.getByRole('button', { name: 'Expert', exact: true })).toHaveAttribute(
    'aria-pressed',
    'true',
  );
  await expect(page.getByLabel('Rewrite mode')).toBeFocused();

  await authorRoutingPolicy(page, {
    key: 'routing_genre',
    plural: 'Routing Genres',
    singular: 'Routing Genre',
  });
  await page.getByLabel('Lifecycle status').selectOption('published');
  await validate(page);

  const effective = await diagnosticJson(
    page,
    '[data-wpessential-taxonomy-effective-args]',
  );
  expect(effective).toMatchObject({
    rewrite: {
      slug: 'library/genre',
      with_front: false,
      hierarchical: true,
      ep_mask: 1,
    },
    query_var: 'library_genre_query',
  });
  await expect(
    page.locator('[data-wpessential-taxonomy-diagnostic="rewrite-path"]'),
  ).toHaveText('/library/genre/{parent/.../}{term-slug}/');

  await ensureDetailsOpen(page, 'Explicit overrides');
  const authored = await diagnosticJson(
    page,
    '[data-wpessential-taxonomy-overrides]',
  );
  expect(authored).toMatchObject({
    rewrite: {
      slug: 'library/genre',
      with_front: false,
      hierarchical: true,
      ep_mask: 1,
    },
    query_var: 'library_genre_query',
  });

  await page.getByRole('button', { name: 'Save taxonomy' }).click();
  const savedRow = page.locator('[data-wpessential-taxonomy-row]');
  await expect(savedRow).toContainText('routing_genre');
  await expect(savedRow).toContainText('Published');
  await page.getByRole('button', { name: 'Edit', exact: true }).click();
  await page.getByRole('button', { name: 'Expert', exact: true }).click();
  await expect(page.getByLabel('Rewrite mode')).toHaveValue('custom');
  await expect(page.getByLabel('Rewrite slug')).toHaveValue('library/genre');
  await expect(page.getByLabel('Use front base')).toHaveValue('false');
  await expect(page.getByLabel('Rewrite path hierarchy')).toHaveValue('true');
  await expect(page.getByLabel('Endpoint mask')).toHaveValue('1');
  await expect(page.getByLabel('Query variable mode')).toHaveValue('custom');
  await expect(page.getByLabel('Custom query variable name')).toHaveValue(
    'library_genre_query',
  );

  await page.getByRole('button', { name: 'Cancel edit' }).click();
  await page.getByRole('button', { name: 'Expert', exact: true }).click();
  await page.getByLabel('Taxonomy key').fill('routing_topic');
  await page.getByLabel('Plural name').fill('Routing Topics');
  await page.getByLabel('Singular name').fill('Routing Topic');
  await expect(page.getByLabel('Rewrite mode')).toHaveValue('');
  await expect(page.getByLabel('Query variable mode')).toHaveValue('');
  await validate(page);
  await ensureDetailsOpen(page, 'Explicit overrides');
  const resetOverrides = await diagnosticJson(
    page,
    '[data-wpessential-taxonomy-overrides]',
  );
  expect(resetOverrides).not.toHaveProperty('rewrite');
  expect(resetOverrides).not.toHaveProperty('query_var');

  const accessibility = await new AxeBuilder({ page })
    .include('#wpessential-taxonomy-root')
    .analyze();
  expect(
    accessibility.violations,
    `Axe violations with rewrite/query-var controls visible: ${JSON.stringify(
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
