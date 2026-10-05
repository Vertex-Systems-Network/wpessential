import AxeBuilder from '@axe-core/playwright';
import { expect, test } from '@playwright/test';
import { runCLI } from '@wp-playground/cli';

const pluginDirectory = process.env.WPE_E2E_PLUGIN_DIR;

if (!pluginDirectory) {
  throw new Error('WPE_E2E_PLUGIN_DIR must point to the unpacked WPEssential distributable.');
}

let playground;

async function visitDashboard(page) {
  const response = await page.goto(`${playground.serverUrl}/wp-admin/index.php`, {
    waitUntil: 'domcontentloaded',
  });

  expect(response, 'Dashboard navigation should return a response.').not.toBeNull();
  expect(response?.ok(), 'Dashboard should return a successful HTTP response.').toBe(true);
}

async function bootPackagedAdminBundle(page) {
  await page.addScriptTag({
    url: `${playground.serverUrl}/wp-content/plugins/wpessential/assets/admin/main.js`,
  });
}

async function installManualRefreshFixture(page) {
  await page.addInitScript(() => {
    document.addEventListener(
      'DOMContentLoaded',
      () => {
        const root = document.createElement('section');
        root.id = 'wpe-e2e-refresh';
        root.setAttribute('aria-label', 'WPEssential refresh fixture');
        root.dataset.wpessentialDashboardManualRefresh = '1';
        root.dataset.ajaxUrl = '/wp-admin/admin-ajax.php';
        root.dataset.ajaxAction = 'wpessential_dispatch';
        root.dataset.routeType = 'dashboard-widgets.refresh.manual';
        root.dataset.nonce = 'e2e-refresh-nonce';
        root.dataset.definitionId = '11111111-1111-4111-8111-111111111111';
        root.dataset.definitionRevision = '3';
        root.dataset.screen = 'site';
        root.innerHTML = `
          <div data-wpessential-dashboard-refresh-content="1"><p>Initial dashboard content</p></div>
          <p><button type="button" class="button button-secondary" data-wpessential-dashboard-refresh-button="1">Refresh</button></p>
          <template data-wpessential-dashboard-refresh-loading="1"><div><p>Trusted loading state</p></div></template>
          <p role="status" aria-live="polite" data-wpessential-dashboard-refresh-status="1"></p>
        `;
        const mount = document.getElementById('wpbody-content') ?? document.body;
        mount.prepend(root);
      },
      { once: true }
    );
  });
}

async function installFormActionFixture(page) {
  await page.addInitScript(() => {
    document.addEventListener(
      'DOMContentLoaded',
      () => {
        const root = document.createElement('section');
        root.id = 'wpe-e2e-form-action';
        root.setAttribute('aria-label', 'WPEssential form action fixture');
        root.dataset.wpessentialDashboardFormAction = '1';
        root.dataset.ajaxUrl = '/wp-admin/admin-ajax.php';
        root.dataset.ajaxAction = 'wpessential_dispatch';
        root.dataset.routeType = 'dashboard-widgets.form-action.confirm';
        root.dataset.nonce = 'e2e-preflight-nonce';
        root.dataset.executeRouteType = 'dashboard-widgets.form-action.execute';
        root.dataset.executeNonce = 'e2e-execution-nonce';
        root.dataset.definitionId = '22222222-2222-4222-8222-222222222222';
        root.dataset.definitionRevision = '7';
        root.innerHTML = `
          <button type="button" class="button button-primary" data-wpessential-form-action-open="1">Run action</button>
          <div data-wpessential-form-action-confirmation="1" hidden>
            <p><strong>Run action?</strong></p>
            <p>This action is lifecycle constrained.</p>
            <p>
              <button type="button" class="button button-primary" data-wpessential-form-action-confirm="1">Confirm</button>
              <button type="button" class="button" data-wpessential-form-action-cancel="1">Cancel</button>
            </p>
          </div>
          <p role="status" aria-live="polite" data-wpessential-form-action-status="1"></p>
        `;
        const mount = document.getElementById('wpbody-content') ?? document.body;
        mount.prepend(root);
      },
      { once: true }
    );
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

test('packaged Dashboard manual refresh shows loading and replaces trusted content once', async ({ page }) => {
  await installManualRefreshFixture(page);

  let pendingRoute = null;
  let refreshRequests = 0;
  await page.route('**/wp-admin/admin-ajax.php', async (route) => {
    const params = new URLSearchParams(route.request().postData() ?? '');
    if (params.get('type') !== 'dashboard-widgets.refresh.manual') {
      await route.continue();
      return;
    }

    refreshRequests += 1;
    pendingRoute = route;
  });

  await visitDashboard(page);
  await bootPackagedAdminBundle(page);

  const root = page.locator('#wpe-e2e-refresh');
  const content = root.locator('[data-wpessential-dashboard-refresh-content="1"]');
  const button = root.getByRole('button', { name: 'Refresh' });
  const status = root.locator('[data-wpessential-dashboard-refresh-status="1"]');

  await expect(root).toHaveAttribute('data-wpessential-manual-refresh-enhanced', 'ready');
  await expect(content).toContainText('Initial dashboard content');

  await button.click();

  await expect(root).toHaveAttribute('aria-busy', 'true');
  await expect(button).toBeDisabled();
  await expect(content).toContainText('Trusted loading state');
  await expect(status).toHaveText('Refreshing widget…');
  expect(refreshRequests).toBe(1);
  expect(pendingRoute).not.toBeNull();

  await pendingRoute.fulfill({
    status: 200,
    contentType: 'application/json',
    body: JSON.stringify({
      success: true,
      data: {
        state: 'rendered',
        notice: 'Widget refreshed.',
        html: '<div class="wpe-dashboard-widget"><p>Updated dashboard content</p></div>',
      },
    }),
  });

  await expect(content).toContainText('Updated dashboard content');
  await expect(content).not.toContainText('Initial dashboard content');
  await expect(status).toHaveText('Widget refreshed.');
  await expect(button).toBeEnabled();
  await expect(root).toHaveAttribute('aria-busy', 'false');
  expect(refreshRequests).toBe(1);

  const results = await new AxeBuilder({ page }).include('#wpe-e2e-refresh').analyze();
  expect(results.violations).toEqual([]);
});

test('packaged Dashboard manual refresh restores previous content on failure without retry', async ({ page }) => {
  await installManualRefreshFixture(page);

  let refreshRequests = 0;
  await page.route('**/wp-admin/admin-ajax.php', async (route) => {
    const params = new URLSearchParams(route.request().postData() ?? '');
    if (params.get('type') !== 'dashboard-widgets.refresh.manual') {
      await route.continue();
      return;
    }

    refreshRequests += 1;
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        success: true,
        data: {
          state: 'runtime_failure',
          notice: 'The widget could not be refreshed. Reload the dashboard and try again.',
        },
      }),
    });
  });

  await visitDashboard(page);
  await bootPackagedAdminBundle(page);

  const root = page.locator('#wpe-e2e-refresh');
  const content = root.locator('[data-wpessential-dashboard-refresh-content="1"]');
  const button = root.getByRole('button', { name: 'Refresh' });
  const status = root.locator('[data-wpessential-dashboard-refresh-status="1"]');

  await expect(root).toHaveAttribute('data-wpessential-manual-refresh-enhanced', 'ready');
  await button.click();

  await expect(content).toContainText('Initial dashboard content');
  await expect(status).toHaveText(
    'The widget could not be refreshed. Reload the dashboard and try again.'
  );
  await expect(button).toBeEnabled();
  await expect(root).toHaveAttribute('aria-busy', 'false');
  await page.waitForTimeout(250);
  expect(refreshRequests).toBe(1);
});

test('packaged form-action client accepts lifecycle_inactive and does not execute', async ({ page }) => {
  await installFormActionFixture(page);

  let preflightRequests = 0;
  let executionRequests = 0;
  await page.route('**/wp-admin/admin-ajax.php', async (route) => {
    const params = new URLSearchParams(route.request().postData() ?? '');
    const type = params.get('type');

    if (type === 'dashboard-widgets.form-action.confirm') {
      preflightRequests += 1;
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          success: true,
          data: {
            state: 'lifecycle_inactive',
            notice: 'This action is unavailable while the widget lifecycle is inactive.',
          },
        }),
      });
      return;
    }

    if (type === 'dashboard-widgets.form-action.execute') {
      executionRequests += 1;
      await route.fulfill({
        status: 500,
        contentType: 'application/json',
        body: JSON.stringify({ success: false }),
      });
      return;
    }

    await route.continue();
  });

  await visitDashboard(page);
  await bootPackagedAdminBundle(page);

  const root = page.locator('#wpe-e2e-form-action');
  await expect(root).toHaveAttribute('data-wpessential-form-action-enhanced', 'ready');

  await root.getByRole('button', { name: 'Run action' }).click();
  await root.getByRole('button', { name: 'Confirm' }).click();

  await expect(root.locator('[data-wpessential-form-action-status="1"]')).toHaveText(
    'This action is unavailable while the widget lifecycle is inactive.'
  );
  expect(preflightRequests).toBe(1);
  expect(executionRequests).toBe(0);
});
