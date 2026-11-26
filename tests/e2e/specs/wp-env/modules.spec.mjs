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
    let restoreOff = false;

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
      await openAdmin(page, `wpessential-module-${id}`);
      // Leave the module as found: opt-in modules such as coming-soon change what visitors see.
      const wasOn = (await toggle(page).getAttribute('aria-checked')) === 'true';
      await setModule(page, id, false);
      await expect(page.getByRole('link', { name: 'Open builder' })).toHaveCount(0);
      await setModule(page, id, true);
      restoreOff = !wasOn;
    });

    if (builder) {
      await audit(id, 'Open builder', async () => {
        await openAdmin(page, `wpessential-module-${id}`);
        await page.getByRole('link', { name: 'Open builder' }).click();
        await expect(page).toHaveURL(new RegExp(`${BASE}/wp-admin/admin\\.php\\?page=wpessential-`));
        await openAdmin(page, new URL(page.url()).searchParams.get('page'));
      });
    }

    if (restoreOff) {
      await setModule(page, id, false);
    }
  });
}

test('Site Tools Kit: named colour token persists, updates, and removes', async ({ page, audit }) => {
  await openAdmin(page, 'wpessential-module-site-kit');
  const wasOn = (await toggle(page).getAttribute('aria-checked')) === 'true';
  if (!wasOn) await setModule(page, 'site-kit', true);
  const slug = 'wp5-e2e-brand';
  const openColours = () => openAdmin(page, 'wpessential-site-kit&group=colors');
  const panel = () => page.locator('#wpe-sk-colors');
  const field = () => panel().locator('.wpec--colour_tokens');
  const save = async () => {
    const navigation = page.waitForEvent('load');
    await panel().getByRole('button', { name: 'Save changes', exact: true }).click();
    await navigation;
    await expect(field().getByRole('button', { name: 'Add custom colour' })).toBeVisible();
  };
  try {
    await audit('site-kit', 'create and edit a named light/dark token', async () => {
      await openColours();
      await field().getByRole('button', { name: 'Add custom colour' }).click();
      const added = field().getByRole('group', { name: /^Custom colour [0-9]+$/ }).last();
      await added.getByRole('textbox', { name: 'Name', exact: true }).fill('E2E brand');
      await added.getByRole('textbox', { name: 'Stable slug' }).fill(slug);
      await added.getByRole('textbox', { name: 'Light colour' }).fill('#3458c9');
      await added.getByRole('textbox', { name: 'Dark colour (optional)' }).fill('#99aaff');
      await added.getByRole('textbox', { name: 'Dark colour (optional)' }).blur();
      await save();
      const stored = field().locator('.wpec__value');
      await expect(stored).toHaveValue(/wpe-custom-wp5-e2e-brand/);
      await expect(stored).toHaveValue(/#99aaff/);
      // Query by input value property rather than the static HTML attribute.
      const rows = field().getByRole('group', { name: /^Custom colour [0-9]+$/ });
      const index = await rows.evaluateAll((items, key) => items.findIndex((el) => el.querySelector('input[aria-label="Stable slug"]')?.value === key), 'wpe-custom-' + slug);
      expect(index).toBeGreaterThanOrEqual(0);
      const saved = rows.nth(index);
      await saved.getByRole('textbox', { name: 'Light colour' }).fill('#123456');
      await saved.getByRole('textbox', { name: 'Light colour' }).blur();
      await save();
      await expect(stored).toHaveValue(/#123456/);
      const frontend = await page.context().newPage();
      try {
        await frontend.goto(BASE + '/', { waitUntil: 'load' });
        const token = '--wp--preset--color--wpe-custom-' + slug;
        const read = () => frontend.evaluate((key) => getComputedStyle(document.documentElement).getPropertyValue(key).trim().toLowerCase(), token);
        await frontend.evaluate(() => document.documentElement.setAttribute('data-wpe-scheme', 'light'));
        await expect.poll(read).toBe('#123456');
        await frontend.evaluate(() => document.documentElement.setAttribute('data-wpe-scheme', 'dark'));
        await expect.poll(read).toBe('#99aaff');
      } finally {
        await frontend.close();
      }
    });
  } finally {
    await openColours();
    const rows = field().getByRole('group', { name: /^Custom colour [0-9]+$/ });
    const index = await rows.evaluateAll((items, key) => items.findIndex((el) => el.querySelector('input[aria-label="Stable slug"]')?.value === key), 'wpe-custom-' + slug);
    if (index >= 0) {
      await rows.nth(index).getByRole('button', { name: /^Remove custom colour/ }).click();
      await save();
      await expect(field().locator('.wpec__value')).not.toHaveValue(/wpe-custom-wp5-e2e-brand/);
    }
    if (!wasOn) await setModule(page, 'site-kit', false);
  }
});
