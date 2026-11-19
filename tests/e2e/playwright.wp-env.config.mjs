import process from 'node:process';

import { defineConfig } from '@playwright/test';

// End-to-end audit against a real WordPress started with wp-env (see .wp-env.json).
// WPE_E2E_SPEC_DIRS lets the Pro repository add its own spec directory.
const extra = (process.env.WPE_E2E_SPEC_DIRS ?? '').split(',').map((dir) => dir.trim()).filter(Boolean);
const executablePath = process.env.WPE_E2E_CHROMIUM || undefined;

export default defineConfig({
  fullyParallel: false,
  forbidOnly: Boolean(process.env.CI),
  retries: 0,
  workers: 1,
  timeout: 240_000,
  expect: { timeout: 15_000 },
  outputDir: 'test-results/artifacts',
  reporter: [['line'], ['json', { outputFile: 'test-results/wp-env-results.json' }]],
  use: {
    browserName: 'chromium',
    viewport: { width: 1400, height: 900 },
    actionTimeout: 15_000,
    launchOptions: { executablePath },
  },
  projects: ['./specs/wp-env', ...extra].map((testDir, index) => ({ name: index === 0 ? 'free' : `extra-${index}`, testDir })),
});
