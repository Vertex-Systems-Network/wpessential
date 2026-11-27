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
        const cssValue = (name) => front.locator('html').evaluate((el, key) => getComputedStyle(el).getPropertyValue(key).trim(), name);
        await expect.poll(() => cssValue('--wpe-wp7-audit')).toBe('123');
        await expect.poll(() => cssValue('--wpe-wp7-second')).toBe('');
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
        const remaining = panel().getByRole('group', { name: 'WP7 second CSS', exact: true });
        await expect(remaining).toBeVisible();
        await remaining.locator('select[name$="[type]"]').selectOption('scss', { force: true });
        await remaining.getByRole('textbox', { name: 'Code', exact: true }).fill('$size: 456; :root { --wpe-wp7-second: $size; }');
        await remaining.locator('input[type=checkbox][name$="[enabled]"]').check();
        await save();
        await expect(remaining.locator('select[name$="[type]"]')).toHaveValue('scss');
        await front.goto(BASE + '/', { waitUntil: 'load' });
        await expect(style).toHaveCount(1);
        await expect.poll(() => cssValue('--wpe-wp7-second')).toBe('456');
        await expect.poll(() => cssValue('--wpe-wp7-audit')).toBe('');
      } finally {
        await front.close();
      }
    });
  } finally {
    run(' $old = json_decode(base64_decode("' + backup + '"), true); if ($old === null) { delete_option("wpessential_site_kit_snippets"); } else { update_option("wpessential_site_kit_snippets", $old); } echo "restored";');
    if (!wasOn) await setModule(page, 'site-kit', false);
  }
});

test('Site Tools Kit: scoped browser snippets and opaque PHP isolation', async ({ page, anon, audit }) => {
  await openAdmin(page, 'wpessential-module-site-kit');
  const wasOn = (await toggle(page).getAttribute('aria-checked')) === 'true';
  if (!wasOn) await setModule(page, 'site-kit', true);
  const run = (code) => wp('--user=admin eval \'eval(base64_decode("' + Buffer.from(code).toString('base64') + '"));\'').trim().split('\n').at(-1);
  const backup = run('echo base64_encode(wp_json_encode(get_option("wpessential_site_kit_snippets", null)));');
  let postId = 0;
  try {
    await audit('site-kit', 'priority, scoped browser output and inert unsupported rows', async () => {
      postId = Number(run([
        '$id = wp_insert_post(["post_title"=>"WP7 scope audit","post_type"=>"page","post_status"=>"publish","post_content"=>"<p id=\'wp7-content\'>Scope audit</p>"], true);',
        'if (is_wp_error($id)) { throw new RuntimeException($id->get_error_message()); }',
        '$base = ["title"=>"WP7 test","scope"=>"all","enabled"=>true,"condition"=>"pages","page_ids"=>[$id],"location"=>"head","priority"=>10];',
        '$rows = [',
        'array_replace($base, ["id"=>"wp7late","type"=>"js","priority"=>20,"code"=>"document.documentElement.dataset.wp7Order += \'b\';"]),',
        'array_replace($base, ["id"=>"wp7early","type"=>"js","priority"=>1,"code"=>"document.documentElement.dataset.wp7Order = \'a\';"]),',
        'array_replace($base, ["id"=>"wp7before","type"=>"html","location"=>"before_content","code"=>"<span id=\'wp7-before\'>Before</span>"]),',
        'array_replace($base, ["id"=>"wp7after","type"=>"html","location"=>"after_content","code"=>"<span id=\'wp7-after\'>After</span>"]),',
        'array_replace($base, ["id"=>"wp7admin","type"=>"html","condition"=>"all","location"=>"admin","code"=>"<span id=\'wp7-admin\'>Admin only</span>"]),',
        'array_replace($base, ["id"=>"wp7login","type"=>"html","condition"=>"all","location"=>"login","code"=>"<span id=\'wp7-login\'>Login only</span>"]),',
        'array_replace($base, ["id"=>"wp7opaque","title"=>"WP7 opaque PHP","type"=>"php","code"=>"opaque-wp7-marker"]),',
        ']; update_option("wpessential_site_kit_snippets", $rows); echo $id;',
      ].join('\n')));
      expect(postId).toBeGreaterThan(0);
      const front = await page.context().newPage();
      try {
        await front.goto(BASE + '/?page_id=' + postId, { waitUntil: 'load' });
        await expect(front.locator('html')).toHaveAttribute('data-wp7-order', 'ab');
        await expect(front.locator('#wp7-before')).toBeVisible();
        await expect(front.locator('#wp7-after')).toBeVisible();
        await expect(front.locator('#wp7-admin, #wp7-login')).toHaveCount(0);
        expect(await front.content()).not.toContain('opaque-wp7-marker');
        await front.goto(BASE + '/', { waitUntil: 'load' });
        await expect(front.locator('html')).not.toHaveAttribute('data-wp7-order', 'ab');
        await expect(front.locator('#wp7-before, #wp7-after')).toHaveCount(0);
        await front.goto(BASE + '/?page_id=' + postId + '&wpe-safe-mode=1', { waitUntil: 'load' });
        await expect(front.locator('#wp7-before, #wp7-after, #wpe-snippet-wp7early, #wpe-snippet-wp7late')).toHaveCount(0);
        await openAdmin(page, 'wpessential-site-kit&group=snippets');
        await expect(page.locator('#wp7-admin')).toHaveCount(1);
        await expect(page.locator('#wp7-login')).toHaveCount(0);
        await expect(page.getByRole('group', { name: 'WP7 opaque PHP', exact: true })).toHaveCount(0);
        const login = await anon.get(BASE + '/wp-login.php');
        expect(await login.text()).toContain('wp7-login');
        const nav = page.waitForEvent('load');
        await page.locator('#wpe-sk-snippets').getByRole('button', { name: 'Save snippets', exact: true }).click();
        await nav;
        expect(run('$rows = get_option("wpessential_site_kit_snippets", []); foreach ($rows as $row) { if ($row["id"] === "wp7opaque") { echo $row["type"] . ":" . $row["code"]; } }')).toBe('php:opaque-wp7-marker');
      } finally {
        await front.close();
      }
    });
  } finally {
    run('if (' + postId + ' > 0) { wp_delete_post(' + postId + ', true); } $old = json_decode(base64_decode("' + backup + '"), true); if ($old === null) { delete_option("wpessential_site_kit_snippets"); } else { update_option("wpessential_site_kit_snippets", $old); } echo "restored";');
    if (!wasOn) await setModule(page, 'site-kit', false);
  }
});

test('Site Tools Kit: Customizer and admin share one published option', async ({ page, audit }) => {
  await openAdmin(page, 'wpessential-module-site-kit');
  const wasOn = (await toggle(page).getAttribute('aria-checked')) === 'true';
  if (!wasOn) await setModule(page, 'site-kit', true);
  const run = (code) => wp('--user=admin eval \'eval(base64_decode("' + Buffer.from(code).toString('base64') + '"));\'').trim().split('\n').at(-1);
  const backup = run('echo base64_encode(wp_json_encode(get_option("wpessential_site_kit", null)));');
  const legacy = run('echo base64_encode(wp_json_encode(get_theme_mod("wpessential_site", null)));');
  const setting = (key) => 'wpessential_site_kit[values][' + key + ']';
  const control = (key) => page.locator('[data-wpe-setting="' + setting(key) + '"]');
  const stored = () => JSON.parse(run('echo wp_json_encode(get_option("wpessential_site_kit", []));'));
  const openCustomizer = async (section) => {
    const url = BASE + '/wp-admin/customize.php?autofocus%5Bsection%5D=' + encodeURIComponent('wpessential_site_kit_' + section);
    const response = await page.goto(url, { waitUntil: 'load' });
    expect(response?.status()).toBe(200);
    await expect(page.locator('#customize-controls')).toBeVisible();
  };
  try {
    await audit('site-kit', 'Customizer reads admin changes and publishes scalar and responsive settings', async () => {
      run('update_option("wpessential_site_kit", ["version"=>1,"values"=>["color_primary"=>"#123456","color_secondary"=>"#789abc"]]); wp_clean_theme_json_cache(); echo "ready";');
      await openAdmin(page, 'wpessential-site-kit&group=colors');
      const adminField = page.locator('.wpec').filter({ has: page.locator('input[name="values[color_primary]"]') });
      await adminField.locator('.wpec-color__hex').fill('#234567');
      await adminField.locator('.wpec-color__hex').blur();
      await page.locator('#wpe-sk-colors').getByRole('button', { name: 'Save changes', exact: true }).click();
      await expect.poll(() => stored().values.color_primary, { timeout: 15000 }).toBe('#234567');
      await openCustomizer('colors');
      const hex = control('color_primary').locator('.wpec-color__hex');
      await expect(hex).toHaveValue('#234567');
      await hex.fill('#345678');
      await hex.blur();
      expect(stored().values.color_primary).toBe('#234567');
      await page.evaluate(() => window.wp.customize.section('wpessential_site_kit_type').expand());
      const size = control('size_body').locator('.wpec-range__num');
      await expect(size).toBeVisible();
      await size.fill('19');
      await expect.poll(() => page.evaluate((id) => window.wp.customize(id).get(), setting('size_body'))).toMatch(/19/);
      await expect(page.locator('#save')).toBeEnabled();
      await page.locator('#save').click();
      await expect.poll(() => stored().values.color_primary, { timeout: 30000 }).toBe('#345678');
      const values = stored().values;
      expect(values.color_secondary).toBe('#789abc');
      expect(JSON.stringify(values.size_body)).toContain('19');
      expect(run('echo base64_encode(wp_json_encode(get_theme_mod("wpessential_site", null)));')).toBe(legacy);
      await openAdmin(page, 'wpessential-site-kit&group=colors');
      await expect(adminField.locator('.wpec-color__hex')).toHaveValue('#345678');
      await openAdmin(page, 'wpessential-site-kit&group=type');
      const adminSize = page.locator('.wpec').filter({ has: page.locator('input[name="values[size_body]"]') });
      await expect(adminSize.locator('.wpec-range__num')).toHaveValue('19');
      await openCustomizer('colors');
      await expect(control('color_primary').locator('.wpec-color__hex')).toHaveValue('#345678');
      await control('color_primary').getByRole('button', { name: /Reset .* to the theme default/ }).click();
      await expect(page.locator('#save')).toBeEnabled();
      await page.locator('#save').click();
      await expect.poll(() => stored().values.color_primary ?? '', { timeout: 30000 }).toBe('');
      expect(stored().values.color_secondary).toBe('#789abc');
    });
  } finally {
    run(' $old = json_decode(base64_decode("' + backup + '"), true); if ($old === null) { delete_option("wpessential_site_kit"); } else { update_option("wpessential_site_kit", $old); } wp_clean_theme_json_cache(); echo "restored";');
    if (!wasOn) await setModule(page, 'site-kit', false);
  }
});

test('Site Tools Kit: paired Hi WPEssential palette handoff and fallback', async ({ page, audit }) => {
  test.skip(process.env.WPE_E2E_THEME_PAIR !== '1', 'Requires the pinned companion theme fixture.');
  await openAdmin(page, 'wpessential-module-site-kit');
  const wasOn = (await toggle(page).getAttribute('aria-checked')) === 'true';
  const run = (code) => wp('--user=admin eval \'eval(base64_decode("' + Buffer.from(code).toString('base64') + '"));\'').trim().split('\n').at(-1);
  const originalTheme = run('echo get_stylesheet();');
  const backup = run('echo base64_encode(wp_json_encode(get_option("wpessential_site_kit", null)));');
  const read = () => JSON.parse(run('echo wp_json_encode(["legacy"=>get_theme_mod("wpessential_site", []),"handed"=>get_theme_mod("wpessential_site_kit_palette_handoff", []),"effective"=>wpessential_site_values(),"canonical"=>get_option("wpessential_site_kit", [])]);'));
  let themeBackup = '';
  try {
    wp('theme activate hi-wpessential');
    expect(run('echo get_template();')).toBe('hi-wpessential');
    themeBackup = run('echo base64_encode(wp_json_encode(get_theme_mods()));');
    if (!wasOn) await setModule(page, 'site-kit', true);
    await audit('site-kit', 'paired theme retains legacy data and delegates transferred palette fields', async () => {
      run('set_theme_mod("wpessential_site", ["color_primary"=>"#aabbcc","color_secondary"=>"#112233","font_body"=>"Inter"]); remove_theme_mod("wpessential_site_kit_palette_handoff"); update_option("wpessential_site_kit", ["version"=>1,"values"=>["color_primary"=>"#123456"]]); wp_clean_theme_json_cache(); echo "ready";');
      const original = read().legacy;
      expect(read().effective.color_secondary).toBe('#112233');
      await openAdmin(page, 'wpessential-site-kit&group=colors');
      const transfer = page.getByRole('button', { name: 'Transfer legacy palette ownership', exact: true });
      await expect(transfer).toBeVisible();
      const navigation = page.waitForEvent('load');
      await transfer.click();
      await navigation;
      const migrated = read();
      expect(migrated.legacy).toEqual(original);
      expect(migrated.canonical.values.color_primary).toBe('#123456');
      expect(migrated.canonical.values.color_secondary).toBe('#112233');
      expect(migrated.handed).toEqual(expect.arrayContaining(['color_primary', 'color_secondary']));
      expect(migrated.effective.color_primary).toBeUndefined();
      expect(migrated.effective.color_secondary).toBeUndefined();
      expect(migrated.effective.font_body).toBe('Inter');
      await page.goto(BASE + '/wp-admin/customize.php?autofocus%5Bsection%5D=wpessential_site_kit_colors', { waitUntil: 'load' });
      const field = page.locator('[data-wpe-setting="wpessential_site_kit[values][color_secondary]"]');
      await expect(field.locator('.wpec-color__hex')).toHaveValue('#112233');
      await expect.poll(() => page.evaluate(() => Boolean(window.wp?.customize?.('wpessential_site[color_secondary]')))).toBe(false);
      await field.getByRole('button', { name: /Reset .* to the theme default/ }).click();
      await expect(page.locator('#save')).toBeEnabled();
      await page.locator('#save').click();
      await expect.poll(() => read().canonical.values.color_secondary ?? '', { timeout: 30000 }).toBe('');
      const reset = read();
      expect(reset.legacy).toEqual(original);
      expect(reset.effective.color_secondary).toBeUndefined();
      expect(reset.handed).toContain('color_secondary');
      await setModule(page, 'site-kit', false);
      expect(read().effective.color_secondary).toBe('#112233');
      expect(read().legacy).toEqual(original);
      await setModule(page, 'site-kit', true);
      expect(read().effective.color_secondary).toBeUndefined();
    });
  } finally {
    if (themeBackup) {
      run('$mods = json_decode(base64_decode("' + themeBackup + '"), true); update_option("theme_mods_" . get_stylesheet(), $mods); echo "restored";');
    }
    run('$old = json_decode(base64_decode("' + backup + '"), true); if ($old === null) { delete_option("wpessential_site_kit"); } else { update_option("wpessential_site_kit", $old); } wp_clean_theme_json_cache(); echo "restored";');
    if (originalTheme !== 'hi-wpessential') wp('theme activate ' + originalTheme);
    await setModule(page, 'site-kit', wasOn);
  }
});
