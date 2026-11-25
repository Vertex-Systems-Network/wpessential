import { BASE, expect, notBuilt, openAdmin, test } from '../support/audit.mjs';

// Must match frameworks/Platform/Admin/Core/ModuleCatalog.php.
const MODULES = [
  'custom-post-types', 'taxonomies', 'custom-fields', 'relations', 'custom-tables', 'status', 'query',
  'admin-columns', 'admin-menu', 'dashboard-widgets', 'settings', 'roles', 'profiles', 'membership',
  'dashboard', 'listings', 'site-kit', 'builder-widgets', 'pro-elements', 'forms-workflows',
  'notifications', 'emails', 'chat', 'cron', 'import-export', 'reset', 'rest-api', 'coming-soon', 'backup', 'media',
  'protector', 'connections', 'ai-connect',
];

const toggle = (page) => page.locator('.wpe-ui-module-head__actions .wpe-ui-toggle[role="switch"]');

async function setModule(page, id, on) {
  await openAdmin(page, `wpessential-module-${id}`);
  if ((await toggle(page).getAttribute('aria-checked')) !== String(on)) {
    await toggle(page).click();
    await expect(toggle(page)).toHaveAttribute('aria-checked', String(on));
  }
  // The switch works in place; the stored state is what a fresh request renders.
  await openAdmin(page, `wpessential-module-${id}`);
  await expect(toggle(page)).toHaveAttribute('aria-checked', String(on));
}

test('Modules screen lists all 32 modules', async ({ page, audit }) => {
  await audit('Modules', 'list shows every module', async () => {
    await openAdmin(page, 'wpessential-modules');
    await expect(page.locator('[data-wpe-module]')).toHaveCount(MODULES.length);
    for (const id of MODULES) {
      await expect(page.locator(`a[href*="page=wpessential-module-${id}"]`).first()).toBeVisible();
    }
  });
  await audit('Modules', 'edition and state filters', async () => {
    for (const name of ['Free', 'Premium', 'Active', 'Inactive', 'All']) {
      await page.getByRole('button', { name, exact: true }).click();
    }
    await expect(page.locator('[data-wpe-module]:visible')).toHaveCount(MODULES.length);
  });
});

for (const id of MODULES) {
  test(`module ${id}: page, switch and builder`, async ({ page, audit }) => {
    let locked = false;
    let builder = false;

    await audit(id, 'module page opens', async () => {
      await openAdmin(page, `wpessential-module-${id}`);
      locked = (await page.locator('.wpe-ui-module-head__actions .wpe-ui-toggle--locked').count()) > 0;
      builder = (await page.getByRole('link', { name: 'Open builder' }).count()) > 0;
      const pill = (await page.locator('.wpe-ui-module-head__copy').innerText()).match(/Status:\s*(.+)/)?.[1] ?? '';
      if (/Coming soon|Under development/.test(pill)) {
        notBuilt(id, 'product runtime', `Stage tag on the module page: ${pill.trim()}`);
      }
    });

    if (locked) {
      notBuilt(id, 'switch on / off', 'Locked: needs WPEssential Pro with an active licence or trial.');
      return;
    }

    await audit(id, 'switch off then on', async () => {
      await setModule(page, id, false);
      await expect(page.getByRole('link', { name: 'Open builder' })).toHaveCount(0);
      await setModule(page, id, true);
    });

    if (builder) {
      await audit(id, 'Open builder', async () => {
        await openAdmin(page, `wpessential-module-${id}`);
        await page.getByRole('link', { name: 'Open builder' }).click();
        await expect(page).toHaveURL(new RegExp(`${BASE}/wp-admin/admin\\.php\\?page=wpessential-`));
        await openAdmin(page, new URL(page.url()).searchParams.get('page'));
      });
    }
  });
}
