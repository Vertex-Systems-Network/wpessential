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

test('packaged Taxonomy tier navigation preserves hidden values and authors truthful inherited visibility', async ({ page }) => {
  await visitTaxonomies(page);

  const essential = page.getByRole('button', { name: 'Essential', exact: true });
  const advanced = page.getByRole('button', { name: 'Advanced', exact: true });
  const expert = page.getByRole('button', { name: 'Expert', exact: true });
  const advancedSection = page.locator('#wpessential-taxonomy-tier-advanced');
  const expertSection = page.locator('#wpessential-taxonomy-tier-expert');
  const tierStatus = page.locator('#wpessential-taxonomy-tier-status');

  await expect(essential).toHaveAttribute('aria-pressed', 'true');
  await expect(advanced).toHaveAttribute('aria-pressed', 'false');
  await expect(expert).toHaveAttribute('aria-pressed', 'false');
  await expect(advancedSection).toBeHidden();
  await expect(expertSection).toBeHidden();
  await expect(tierStatus).toContainText('Essential controls are shown');

  await advanced.click();
  await expect(essential).toHaveAttribute('aria-pressed', 'false');
  await expect(advanced).toHaveAttribute('aria-pressed', 'true');
  await expect(advancedSection).toBeVisible();
  await expect(expertSection).toBeHidden();
  await expect(tierStatus).toContainText('Essential and Advanced controls are shown');

  const showUi = page.getByLabel('Show Admin UI');
  const publiclyQueryable = page.getByLabel('Publicly queryable');
  const showInMenu = page.getByLabel('Show in menu');
  const showInNavMenus = page.getByLabel('Show in navigation menus');
  const showTagcloud = page.getByLabel('Show tag cloud');
  const showInQuickEdit = page.getByLabel('Show in quick edit');

  await expect(showUi).toHaveValue('inherit');
  await expect(publiclyQueryable).toHaveValue('inherit');
  await expect(showTagcloud).toHaveValue('inherit');
  await expect(
    page.locator('[data-wpessential-taxonomy-inheritance-state="publicly_queryable"]'),
  ).toHaveText('Default / inherited');

  await showUi.selectOption('true');
  await publiclyQueryable.selectOption('false');
  await showInMenu.selectOption('false');
  await showInNavMenus.selectOption('true');
  await showInQuickEdit.selectOption('false');

  await expect(
    page.locator('[data-wpessential-taxonomy-inheritance-state="show_ui"]'),
  ).toHaveText('Explicit: enabled');
  await expect(
    page.locator('[data-wpessential-taxonomy-inheritance-state="publicly_queryable"]'),
  ).toHaveText('Explicit: disabled');

  await essential.click();
  await expect(advancedSection).toBeHidden();
  await advanced.click();
  await expect(publiclyQueryable).toHaveValue('false');
  await expect(showInMenu).toHaveValue('false');
  await expect(showInNavMenus).toHaveValue('true');
  await expect(showInQuickEdit).toHaveValue('false');
  await expect(showTagcloud).toHaveValue('inherit');

  await page.getByLabel('Taxonomy key').fill('tier_genre');
  await page.getByLabel('Plural name').fill('Genres');
  await page.getByLabel('Singular name').fill('Genre');
  await page.getByRole('button', { name: 'Validate' }).click();

  const explicit = await effectiveArgs(page);
  expect(explicit).toMatchObject({
    public: true,
    hierarchical: false,
    show_in_rest: true,
    show_ui: true,
    publicly_queryable: false,
    show_in_menu: false,
    show_in_nav_menus: true,
    show_in_quick_edit: false,
  });
  expect(explicit).not.toHaveProperty('show_tagcloud');

  await publiclyQueryable.selectOption('inherit');
  await page.getByRole('button', { name: 'Validate' }).click();
  const inherited = await effectiveArgs(page);
  expect(inherited).not.toHaveProperty('publicly_queryable');
  expect(inherited.show_ui).toBe(true);
  expect(inherited.show_in_menu).toBe(false);

  await expert.click();
  await expect(advancedSection).toBeVisible();
  await expect(expertSection).toBeVisible();
  await expect(expert).toHaveAttribute('aria-pressed', 'true');
  await expect(expertSection).toContainText(
    'Controlled providers, bounded runtime defaults, portability and guarded key migration',
  );

  await essential.focus();
  await expect(essential).toBeFocused();
  await advanced.focus();
  await expect(advanced).toBeFocused();
  await expert.focus();
  await expect(expert).toBeFocused();

  const accessibility = await new AxeBuilder({ page })
    .include('#wpessential-taxonomy-root')
    .analyze();
  expect(
    accessibility.violations,
    `Axe violations with Expert tier visible: ${JSON.stringify(
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
