import { expect } from '@playwright/test';

/**
 * The Taxonomy list and the Add/Edit editor are separate pages. Saves the editor, then opens the
 * list, checks the saved row and follows its Edit button back into the editor.
 *
 * WordPress Playground runs on SQLite, where the plugin keeps definitions in memory for the
 * request only, so the list is served the definition the save returned (as the CPT spec does).
 */
/** Call before clicking Save: resolves to the definition the save returned. */
export function captureTaxonomySave(page) {
  return page
    .waitForResponse(
      (response) =>
        response.url().includes('admin-ajax.php') &&
        (response.request().postData() ?? '').includes('type=taxonomy.save'),
    )
    .then(async (response) => {
      const body = await response.json();
      expect(body.success, 'The taxonomy save must succeed.').toBe(true);
      return body.data.definition;
    });
}

/** Opens the list page served with the given definitions, then refreshes it. */
export async function showTaxonomiesInList(page, serverUrl, definitions) {
  await page.route('**/wp-admin/admin-ajax.php', async (route) => {
    const params = new URLSearchParams(route.request().postData() ?? '');
    if (params.get('type') !== 'taxonomy.list') {
      await route.continue();
      return;
    }
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({ success: true, data: { definitions } }),
    });
  });

  await page.goto(`${serverUrl}/wp-admin/admin.php?page=wpessential-taxonomies`, {
    waitUntil: 'domcontentloaded',
  });
  await page.getByRole('button', { name: 'Refresh' }).click();
  await expect(page.getByText('Taxonomies refreshed.')).toBeVisible();
}

export async function reopenTaxonomyFromList(page, serverUrl, definition, ...rowTexts) {
  const key = definition.payload.taxonomy_key;
  await showTaxonomiesInList(page, serverUrl, [definition]);
  const row = page.locator('[data-wpessential-taxonomy-row]').filter({ hasText: key });
  await expect(row).toBeVisible();
  for (const text of rowTexts) {
    await expect(row).toContainText(text);
  }
  await row.getByRole('button', { name: 'Edit', exact: true }).click();
  await page.waitForURL(/page=wpessential-taxonomy&definition=/);
  await expect(page.getByLabel('Taxonomy key')).toHaveValue(key);
  await expect(page.locator('#wpessential-taxonomy-root')).not.toHaveAttribute('aria-busy', 'true');
  return row;
}
