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
  const root = page.locator('#wpessential-taxonomy-root');
  await expect(root).toHaveAttribute('data-wpessential-enhanced', 'ready');
  return root;
}

async function beforeUnloadState(page) {
  return page.evaluate(() => {
    const event = new Event('beforeunload', { cancelable: true });
    const dispatched = window.dispatchEvent(event);
    return {
      defaultPrevented: event.defaultPrevented,
      dispatched,
    };
  });
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

test('packaged Taxonomy final admin UX renders canonical role impact and protects dirty editor state', async ({ page }) => {
  const root = await visitTaxonomies(page);
  await expect(root).toHaveAttribute('data-wpessential-taxonomy-dirty', 'false');

  const commandBar = page.locator('[data-wpessential-taxonomy-sticky-actions]');
  await expect(commandBar).toBeVisible();
  await expect(commandBar).toContainText('Validate');
  await expect(commandBar).toContainText('Save taxonomy');
  expect(await commandBar.evaluate((element) => getComputedStyle(element).position)).toBe('sticky');

  await page.getByLabel('Taxonomy key').fill('final_ux_genre');
  await page.getByLabel('Plural name').fill('Final UX Genres');
  await page.getByLabel('Singular name').fill('Final UX Genre');
  await expect(root).toHaveAttribute('data-wpessential-taxonomy-dirty', 'true');

  const dirtyBeforeUnload = await beforeUnloadState(page);
  expect(dirtyBeforeUnload.defaultPrevented).toBe(true);
  expect(dirtyBeforeUnload.dispatched).toBe(false);

  await page.getByRole('button', { name: 'Validate' }).click();
  const diagnostics = page.locator('#wpessential-taxonomy-diagnostics');
  await expect(diagnostics).toBeVisible();
  await expect(
    diagnostics.locator('[data-wpessential-taxonomy-diagnostic="role-impact-state"]'),
  ).toHaveAttribute('data-wpessential-taxonomy-role-impact-state', 'healthy');
  await expect(
    diagnostics.locator('[data-wpessential-taxonomy-role-impact-operation="manage_terms"]'),
  ).toContainText('manage_categories');
  await expect(
    diagnostics.locator('[data-wpessential-taxonomy-role-impact-operation="manage_terms"]'),
  ).toContainText('Allow:');
  await expect(
    diagnostics.locator('[data-wpessential-taxonomy-role-impact-operation="manage_terms"]'),
  ).toContainText('explicit deny:');
  await expect(
    diagnostics.locator('[data-wpessential-taxonomy-role-impact-operation="manage_terms"]'),
  ).toContainText('absent:');
  await expect(
    diagnostics.locator('[data-wpessential-taxonomy-role-impact-caveats]'),
  ).toContainText('not final user authorization');

  const accessibility = await new AxeBuilder({ page })
    .include('#wpessential-taxonomy-root')
    .analyze();
  expect(
    accessibility.violations,
    `Axe violations in final Taxonomy admin UX closure: ${JSON.stringify(
      accessibility.violations.map((violation) => ({
        id: violation.id,
        impact: violation.impact,
        targets: violation.nodes.map((node) => node.target),
      })),
      null,
      2,
    )}`,
  ).toEqual([]);

  await page.getByRole('button', { name: 'Save taxonomy' }).click();
  await expect(page.getByText('Taxonomy created.')).toBeVisible();
  await expect(root).toHaveAttribute('data-wpessential-taxonomy-dirty', 'false');

  const cleanBeforeUnload = await beforeUnloadState(page);
  expect(cleanBeforeUnload.defaultPrevented).toBe(false);
  expect(cleanBeforeUnload.dispatched).toBe(true);

  const row = page.locator('[data-wpessential-taxonomy-row]');
  await row.getByRole('button', { name: 'Edit' }).click();
  await expect(root).toHaveAttribute('data-wpessential-taxonomy-dirty', 'false');
  await page.getByLabel('Plural name').fill('Changed but unsaved');
  await expect(root).toHaveAttribute('data-wpessential-taxonomy-dirty', 'true');
  await page.getByRole('button', { name: 'Cancel edit' }).click();
  await expect(root).toHaveAttribute('data-wpessential-taxonomy-dirty', 'false');
});
