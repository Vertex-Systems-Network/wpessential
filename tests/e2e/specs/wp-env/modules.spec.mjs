import { BASE, expect, notBuilt, openAdmin, test, wp } from '../support/audit.mjs';

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

test('Site Tools Kit: shared Gutenberg and Elementor token references', async ({ page, audit }) => {
  await openAdmin(page, 'wpessential-module-site-kit');
  const wasOn = (await toggle(page).getAttribute('aria-checked')) === 'true';
  if (!wasOn) await setModule(page, 'site-kit', true);
  try {
    await audit('site-kit', 'registered blocks and widgets inherit global token slugs', async () => {
      // Seed and restore in normal CLI context. Inspect in admin context because Elementor strips
      // options on frontend requests; skip WooCommerce only there to avoid its CLI admin-menu fatal.
      const run = (code, admin = false) => wp(`${admin ? '--context=admin --skip-plugins=woocommerce ' : ''}--user=admin eval 'eval(base64_decode("${Buffer.from(code).toString('base64')}"));'`).trim().split('\n').at(-1);
      const backup = run('echo base64_encode(wp_json_encode(get_option("wpessential_site_kit", null)));');
      try {
        run(`
          $store = new \\WPEssential\\Modules\\SiteKit\\SiteKitStore();
          $store->update([
            "custom_colours" => ["tokens" => [["name" => "E2E brand", "slug" => "e2e-kit-brand", "color" => "#123456", "dark" => "#abcdef"]]],
            "custom_typography" => ["tokens" => [["name" => "E2E display", "slug" => "e2e-kit-display", "typography" => ["size" => ["desktop" => ["size" => 48, "unit" => "px"]], "weight" => "700"]]]],
          ]);
          echo "seeded";
        `);
        const result = JSON.parse(run(`
          if (!is_admin()) { throw new \\RuntimeException("Expected admin context for Elementor editor metadata"); }
          $type = \\WP_Block_Type_Registry::get_instance()->get_registered("wpessential/heading");
          if (!$type) { throw new \\RuntimeException("Missing heading block"); }
          $attrs = ["anchor" => "wp5-e2e-heading", "text" => "Token heading", "titleTypography" => ["preset" => "wpe-custom-e2e-kit-display", "weight" => "600"], "titleColor" => ["normal" => "wpe-custom-e2e-kit-brand"]];
          $html = do_blocks("<!-- wp:wpessential/heading " . wp_json_encode($attrs) . " /-->");
          $widgets = \\Elementor\\Plugin::instance()->widgets_manager->get_widget_types();
          $widget = $widgets["wpe-heading"] ?? null;
          if (!$widget) { throw new \\RuntimeException("Missing Elementor heading widget"); }
          // Elementor separates optimized style controls from the bulk controls list.
          $preset = $widget->get_controls("titleTypography_wpe_preset") ?? [];
          $colour = $widget->get_controls("titleColor_wpe_global") ?? [];
          echo wp_json_encode([
            "blockSize" => str_contains($html, "font-size:var(--wpe-typography-wpe-custom-e2e-kit-display-font-size,inherit)"),
            "blockWeight" => str_contains($html, "font-weight:600"),
            "blockColour" => str_contains($html, "color:var(--wp--preset--color--wpe-custom-e2e-kit-brand)"),
            "typography" => isset($preset["options"]["wpe-custom-e2e-kit-display"]) && str_contains($preset["selectors_dictionary"]["wpe-custom-e2e-kit-display"] ?? "", "var(--wpe-typography-wpe-custom-e2e-kit-display-font-size"),
            "colour" => isset($colour["options"]["wpe-custom-e2e-kit-brand"]),
            "premium" => \\WPEssential\\Modules\\SiteKit\\SiteKitElementor::enabled(),
          ]);
        `, true));
        expect(result, JSON.stringify(result)).toEqual({
          blockSize: true, blockWeight: true, blockColour: true, typography: true, colour: true,
          premium: process.env.WPE_E2E_PRO === '1',
        });
      } finally {
        run(`
          $old = json_decode(base64_decode("${backup}"), true);
          if ($old === null) { delete_option("wpessential_site_kit"); } else { update_option("wpessential_site_kit", $old); }
          wp_clean_theme_json_cache();
          echo "restored";
        `);
      }
    });
  } finally {
    if (!wasOn) await setModule(page, 'site-kit', false);
  }
});

test('Site Tools Kit: global styling defaults render on frontend', async ({ page, audit }) => {
  await openAdmin(page, 'wpessential-module-site-kit');
  const wasOn = (await toggle(page).getAttribute('aria-checked')) === 'true';
  if (!wasOn) await setModule(page, 'site-kit', true);
  const run = (code) => wp(`--user=admin eval 'eval(base64_decode("${Buffer.from(code).toString('base64')}"));'`).trim().split('\n').at(-1);
  let postId = 0;
  let backup = '';
  try {
    await audit('site-kit', 'computed image, form, link, button and shadow styling', async () => {
      backup = run('echo base64_encode(wp_json_encode(get_option("wpessential_site_kit", null)));');
      postId = Number(run(`
        $store = new \\WPEssential\\Modules\\SiteKit\\SiteKitStore();
        $store->update([
          'btn_variant_primary_bg' => '#123456', 'btn_variant_primary_text' => '#ffffff',
          'custom_colours' => ['tokens' => [['name' => 'E2E local', 'slug' => 'wp6-local', 'color' => '#fedcba']]],
          'btn_scale_sm_height' => ['desktop' => ['size' => 40, 'unit' => 'px'], 'mobile' => ['size' => 32, 'unit' => 'px']],
          'image_radius' => ['desktop' => ['size' => 9, 'unit' => 'px']],
          'field_placeholder' => '#345678', 'link_decoration_style' => 'dotted',
          'custom_shadows' => ['tokens' => [['name' => 'E2E card', 'slug' => 'wp6-card', 'shadow' => ['x' => 4, 'y' => 8, 'blur' => 16, 'spread' => -2, 'color' => '#123456']]]],
        ]);
        $content = '<!-- wp:wpessential/button ' . wp_json_encode(['text' => 'WP6 button', 'variant' => 'primary', 'size' => 'sm', 'url' => '#wp6']) . ' /-->';
        $content .= '<!-- wp:wpessential/button ' . wp_json_encode(['text' => 'WP6 local override', 'variant' => 'primary', 'size' => 'sm', 'url' => '#wp6', 'textColor' => ['normal' => 'wpe-custom-wp6-local'], 'shadow' => ['preset' => 'wpe-custom-wp6-card']]) . ' /-->';
        $content .= '<!-- wp:html --><div class="entry-content" id="wp6-fixture"><figure class="wp-block-image"><img alt="Test" width="20" height="20" src="' . esc_url( includes_url('images/blank.gif') ) . '"></figure><form><label for="wp6-input">Field</label><input id="wp6-input" type="text" placeholder="Example"></form><a id="wp6-link" href="#wp6">Example link</a></div><!-- /wp:html -->';
        $id = wp_insert_post(['post_title' => 'WP6 style audit', 'post_type' => 'page', 'post_status' => 'publish', 'post_content' => $content], true);
        if (is_wp_error($id)) { throw new \\RuntimeException($id->get_error_message()); }
        echo $id;
      `));
      expect(postId).toBeGreaterThan(0);
      const frontend = await page.context().newPage();
      try {
        await frontend.setViewportSize({ width: 1280, height: 900 });
        await frontend.goto(BASE + '/?page_id=' + postId, { waitUntil: 'load' });
        const button = frontend.locator('.wpe-button__link').filter({ hasText: 'WP6 button' });
        await expect(button).toHaveCSS('background-color', 'rgb(18, 52, 86)');
        await expect(button).toHaveCSS('min-height', '40px');
        const local = frontend.locator('.wpe-button__link').filter({ hasText: 'WP6 local override' });
        await expect(local).toHaveCSS('color', 'rgb(254, 220, 186)');
        await expect(local).toHaveCSS('box-shadow', 'rgb(18, 52, 86) 4px 8px 16px -2px');
        await expect(frontend.locator('#wp6-fixture img')).toHaveCSS('border-radius', '9px');
        await expect(frontend.locator('#wp6-link')).toHaveCSS('text-decoration-style', 'dotted');
        expect(await frontend.locator('#wp6-input').evaluate((el) => getComputedStyle(el, '::placeholder').color)).toBe('rgb(52, 86, 120)');
        const shadow = await frontend.evaluate(() => getComputedStyle(document.documentElement).getPropertyValue('--wp--preset--shadow--wpe-custom-wp6-card').trim());
        expect(shadow).toBe('4px 8px 16px -2px #123456');
        await frontend.setViewportSize({ width: 390, height: 844 });
        await expect(button).toHaveCSS('min-height', '32px');
      } finally {
        await frontend.close();
      }
    });
  } finally {
    if (backup) {
      run(`
        if (${postId || 0} > 0) { wp_delete_post(${postId || 0}, true); }
        $old = json_decode(base64_decode("${backup}"), true);
        if ($old === null) { delete_option('wpessential_site_kit'); } else { update_option('wpessential_site_kit', $old); }
        wp_clean_theme_json_cache();
        echo 'restored';
      `);
    }
    if (!wasOn) await setModule(page, 'site-kit', false);
  }
});

test('Site Tools Kit: named shadow admin lifecycle and Elementor picker', async ({ page, audit }) => {
  await openAdmin(page, 'wpessential-module-site-kit');
  const wasOn = (await toggle(page).getAttribute('aria-checked')) === 'true';
  if (!wasOn) await setModule(page, 'site-kit', true);
  const run = (code, admin = false) => wp(`${admin ? '--context=admin --skip-plugins=woocommerce ' : ''}--user=admin eval 'eval(base64_decode("${Buffer.from(code).toString('base64')}"));'`).trim().split('\n').at(-1);
  const backup = run('echo base64_encode(wp_json_encode(get_option("wpessential_site_kit", null)));');
  const panel = () => page.locator('#wpe-sk-shadows');
  const field = () => panel().locator('.wpec--shadow_tokens');
  const save = async () => {
    const nav = page.waitForEvent('load');
    await panel().getByRole('button', { name: 'Save changes', exact: true }).click();
    await nav;
    await expect(field().getByRole('button', { name: 'Add custom shadow' })).toBeVisible();
  };
  try {
    await audit('site-kit', 'named shadow save, registered Elementor picker and remove', async () => {
      await openAdmin(page, 'wpessential-site-kit&group=shadows');
      await field().getByRole('button', { name: 'Add custom shadow' }).click();
      const row = field().getByRole('group', { name: /^Custom shadow [0-9]+$/ }).last();
      await row.getByRole('textbox', { name: 'Name', exact: true }).fill('E2E shadow');
      await row.getByRole('textbox', { name: 'Stable slug' }).fill('wp6-admin-shadow');
      await row.getByRole('button', { name: 'Edit Shadow', exact: true }).click();
      await row.getByRole('textbox', { name: 'Colour', exact: true }).fill('#123456');
      await row.getByRole('textbox', { name: 'Colour', exact: true }).blur();
      await save();
      await expect(field().locator('.wpec__value')).toHaveValue(/wpe-custom-wp6-admin-shadow/);
      const found = run(`
        if (!is_admin()) { throw new \\RuntimeException("Expected admin context"); }
        $widget = \\Elementor\\Plugin::instance()->widgets_manager->get_widget_types()["wpe-button"];
        $control = $widget->get_controls("shadow_wpe_preset");
        echo isset($control["options"]["wpe-custom-wp6-admin-shadow"]) && str_contains($control["selectors_dictionary"]["wpe-custom-wp6-admin-shadow"] ?? "", "var(--wp--preset--shadow--wpe-custom-wp6-admin-shadow") ? "yes" : "no";
      `, true);
      expect(found).toBe('yes');
      const rows = field().getByRole('group', { name: /^Custom shadow [0-9]+$/ });
      const index = await rows.evaluateAll((items) => items.findIndex((el) => el.querySelector('input[aria-label="Stable slug"]')?.value === 'wpe-custom-wp6-admin-shadow'));
      expect(index).toBeGreaterThanOrEqual(0);
      await rows.nth(index).getByRole('button', { name: /^Remove custom shadow/ }).click();
      await save();
      await expect(field().locator('.wpec__value')).not.toHaveValue(/wpe-custom-wp6-admin-shadow/);
    });
  } finally {
    run(`
      $old = json_decode(base64_decode("${backup}"), true);
      if ($old === null) { delete_option("wpessential_site_kit"); } else { update_option("wpessential_site_kit", $old); }
      wp_clean_theme_json_cache();
      echo "restored";
    `);
    if (!wasOn) await setModule(page, 'site-kit', false);
  }
});

test('Site Tools Kit: snippet save, disable, safe mode and explicit remove', async ({ page, audit }) => {
  await openAdmin(page, 'wpessential-module-site-kit');
  const wasOn = (await toggle(page).getAttribute('aria-checked')) === 'true';
  if (!wasOn) await setModule(page, 'site-kit', true);
  const run = (code) => wp('--user=admin eval \'eval(base64_decode("' + Buffer.from(code).toString('base64') + '"));\'').trim().split('\n').at(-1);
  const backup = run('echo base64_encode(wp_json_encode(get_option("wpessential_site_kit_snippets", null)));');
  const panel = () => page.locator('#wpe-sk-snippets');
  const row = () => panel().getByRole('group', { name: 'WP7 audit CSS', exact: true });
  const save = async () => {
    const nav = page.waitForEvent('load');
    await panel().getByRole('button', { name: 'Save snippets', exact: true }).click();
    await nav;
    await expect(panel()).toBeVisible();
  };
  const marker = '--wpe-wp7-audit: 123';
  try {
    await audit('site-kit', 'snippet admin lifecycle and frontend isolation', async () => {
      run('update_option("wpessential_site_kit_snippets", []); echo "ready";');
      await openAdmin(page, 'wpessential-site-kit&group=snippets');
      const fresh = panel().getByRole('group', { name: 'New snippet', exact: true });
      await fresh.getByRole('textbox', { name: 'Snippet title' }).fill('WP7 audit CSS');
      await fresh.getByRole('textbox', { name: 'Code', exact: true }).fill(':root { ' + marker + '; }');
      await fresh.locator('input[type=checkbox][name$="[enabled]"]').check();
      await panel().getByRole('button', { name: 'Add snippet', exact: true }).click();
      const second = panel().getByRole('group', { name: 'New snippet', exact: true }).last();
      await second.getByRole('textbox', { name: 'Snippet title' }).fill('WP7 second CSS');
      await second.getByRole('textbox', { name: 'Code', exact: true }).fill(':root { --wpe-wp7-second: 456; }');
      await expect(second.locator('input[type=checkbox][name$="[enabled]"]')).not.toBeChecked();
      await save();
      await expect(row()).toBeVisible();
      await expect(panel().getByRole('group', { name: 'WP7 second CSS', exact: true })).toBeVisible();
      const front = await page.context().newPage();
      try {
        const style = front.locator('#wpessential-site-kit-snippets-inline-css');
        await front.goto(BASE + '/', { waitUntil: 'load' });
        await expect(style).toHaveCount(1);
        await expect(front.locator('html')).toHaveCSS('--wpe-wp7-audit', '123');
        await expect(front.locator('html')).toHaveCSS('--wpe-wp7-second', '');
        await front.goto(BASE + '/?wpe-safe-mode=1', { waitUntil: 'load' });
        await expect(style).toHaveCount(0);
        await row().locator('input[type=checkbox][name$="[enabled]"]').uncheck();
        await save();
        await expect(row()).toBeVisible();
        await front.goto(BASE + '/', { waitUntil: 'load' });
        await expect(style).toHaveCount(0);
        await row().getByRole('checkbox', { name: 'Remove on save' }).check();
        await save();
        await expect(row()).toHaveCount(0);
        await expect(panel().getByRole('group', { name: 'WP7 second CSS', exact: true })).toBeVisible();
      } finally {
        await front.close();
      }
    });
  } finally {
    run(' $old = json_decode(base64_decode("' + backup + '"), true); if ($old === null) { delete_option("wpessential_site_kit_snippets"); } else { update_option("wpessential_site_kit_snippets", $old); } echo "restored";');
    if (!wasOn) await setModule(page, 'site-kit', false);
  }
});
