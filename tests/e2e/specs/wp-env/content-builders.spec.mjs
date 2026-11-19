import { changed, expect, fillAll, openAdmin, snapshot, test, wp } from '../support/audit.mjs';

// A fresh key per run: a failed earlier run must not collide with this one.
const RUN = Date.now().toString(36).slice(-4);
const dialog = (page) => page.locator('dialog.wpe-ui-dialog');

// One spec per Free builder: create with every option set, reload, edit, check the site, delete.
for (const builder of [
  {
    module: 'custom-post-types', list: 'wpessential-post-types', editor: 'wpessential-cpt', prefix: 'wpessential-cpt', row: 'data-wpessential-cpt-row',
    add: 'Add new post type', save: 'Save custom post type', cli: 'post-type', key: `e2e_book_${RUN}`, plural: 'E2E Books', singular: 'E2E Book',
    overrides: { 'wpessential-cpt-locked': false, 'wpessential-cpt-opt-rest_namespace': 'e2e/v1', 'wpessential-cpt-opt-template': 'core/paragraph', 'wpessential-cpt-permalink-pattern': 'e2e-books/%postname%' },
  },
  {
    module: 'taxonomies', list: 'wpessential-taxonomies', editor: 'wpessential-taxonomy', prefix: 'wpessential-taxonomy', row: 'data-wpessential-taxonomy-row',
    add: 'Add new taxonomy', save: 'Save taxonomy', cli: 'taxonomy', key: `e2e_genre_${RUN}`, plural: 'E2E Genres', singular: 'E2E Genre',
    overrides: { 'wpessential-taxonomy-locked': false, 'wpessential-taxonomy-rest-namespace': 'e2e/v1', 'wpessential-taxonomy-object-types-extra': '', 'wpessential-taxonomy-cptui-source': null },
  },
]) {
  test(`${builder.module}: create, reload, edit, front end, delete`, async ({ page, audit, anon }) => {
    const form = page.locator('#wpbody-content form').filter({ has: page.getByRole('button', { name: builder.save }) });
    const row = page.locator(`tr[${builder.row}]`).filter({ hasText: builder.key });
    let entered = {};
    // Saving answers with a status line; a blocked save lists its reasons in the validation report.
    const save = async (done) => {
      await page.getByRole('button', { name: builder.save }).click();
      await expect(page.locator('#wpbody-content').getByText(/(created|updated)\.|Validation blocked/).first()).toBeVisible();
      expect(await page.locator('#wpbody-content').getByText(/^blocked:/).allInnerTexts(), 'validation report').toEqual([]);
      await expect(page.locator('#wpbody-content').getByText(done).first()).toBeVisible();
    };

    await audit(builder.module, 'create with every option set', async () => {
      await openAdmin(page, builder.list);
      await page.getByRole('link', { name: builder.add }).click();
      await page.getByRole('button', { name: 'Expert', exact: true }).click();
      await fillAll(form, {
        [`${builder.prefix}-name`]: builder.plural,
        [`${builder.prefix}-singular`]: builder.singular,
        [`${builder.prefix}-key`]: builder.key,
        'Wizard mode': null,
        ...builder.overrides,
      });
      entered = await snapshot(form);
      await save(/created\./);
    });

    await audit(builder.module, 'every value survives a reload', async () => {
      await openAdmin(page, builder.list);
      await row.getByRole('button', { name: 'Edit' }).click();
      await page.getByRole('button', { name: 'Expert', exact: true }).click();
      expect(changed(entered, await snapshot(form), /-(id|revision|key)$/)).toEqual([]);
    });

    await audit(builder.module, 'edit and save again', async () => {
      await page.locator(`#${builder.prefix}-name`).fill(`${builder.plural} Edited`);
      await save(/updated\./);
      await openAdmin(page, builder.list);
      await expect(row).toContainText(`${builder.plural} Edited`);
    });

    await audit(builder.module, 'publish', async () => {
      await row.getByRole('button', { name: 'Publish' }).click();
      await expect(row.getByRole('button', { name: 'Disable' })).toBeVisible();
    });

    await audit(builder.module, 'registered in WordPress after publish (REST)', async () => {
      expect(wp(`${builder.cli} get ${builder.key} --field=name`).trim()).toBe(builder.key);
      expect((await anon.get(`/?${builder.cli === 'taxonomy' ? 'taxonomy' : 'post_type'}=${builder.key}`)).status()).toBeLessThan(500);
    });

    await audit(builder.module, 'disable, trash and delete permanently', async () => {
      await openAdmin(page, builder.list);
      await row.getByRole('button', { name: 'Disable' }).click();
      await expect(row.getByRole('button', { name: 'Publish' })).toBeVisible();
      await row.getByRole('button', { name: 'Trash' }).click();
      await dialog(page).getByRole('button', { name: 'Move to Trash' }).click();
      await page.locator('.wpe-ui-views').getByRole('link', { name: /^Trash/ }).click();
      await row.getByRole('button', { name: 'Delete permanently' }).click();
      await dialog(page).getByRole('button', { name: 'Delete permanently' }).click();
      await expect(row).toHaveCount(0);
      await openAdmin(page, builder.list);
      await expect(row).toHaveCount(0);
    });
  });
}
