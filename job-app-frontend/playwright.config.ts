import { defineConfig, devices } from '@playwright/test'

/**
 * Playwright config for the React SPA.
 *
 * `baseURL` points at the Vite dev server (started outside the tests — set
 * `npm run dev` first). The Laravel API must also be reachable at port 8000
 * for any flow that hits /api.
 *
 * We disable retries locally to keep failing-loud feedback, and run tests
 * sequentially because they share global state (login session, throttle
 * counters, password resets) — running them in parallel would race.
 */
export default defineConfig({
  testDir: './e2e',
  fullyParallel: false,
  workers: 1,
  retries: 0,
  reporter: [['list']],

  use: {
    baseURL: 'http://localhost:3000',
    headless: true,
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
    trace: 'retain-on-failure',
  },

  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],
})
