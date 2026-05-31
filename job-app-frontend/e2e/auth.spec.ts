import { test, expect } from '@playwright/test'

/**
 * E2E happy-path coverage for the auth surface of the candidate SPA.
 *
 * These hit the live Laravel backend at :8000. They rely on:
 *   - A user `exemple@exemple.com` with password `password12345678` existing.
 *   - For fast feedback, MAIL_MAILER should be set to `log` in the backend
 *     `.env`. With SMTP=Gmail the forgot-password test still passes but
 *     takes ~15s instead of <1s.
 *
 * Tests share the rate-limit budget (5 logins / IP / minute) so we use the
 * API directly for the auth-needed flows to leave headroom for human dev.
 */

const TEST_EMAIL    = 'exemple@exemple.com'
const TEST_PASSWORD = 'password12345678'

// Reach the API base URL the same way the SPA does — through the Vite proxy.
const BACKEND_URL = 'http://localhost:8000'

test.describe('Public pages', () => {
  test('home shows the marketing hero', async ({ page }) => {
    await page.goto('/')
    await expect(page.getByText('Plateforme N°1 en Algérie')).toBeVisible()
  })

  test('login page has the form, the Oublié link and a Se connecter button', async ({ page }) => {
    await page.goto('/login')
    await expect(page.getByRole('heading', { name: 'Connexion' })).toBeVisible()
    await expect(page.getByPlaceholder('vous@exemple.com')).toBeVisible()
    await expect(page.getByRole('link', { name: /Oublié/i })).toBeVisible()
    await expect(page.getByRole('button', { name: /Se connecter/i })).toBeVisible()
  })

  test('register: role chooser → information form', async ({ page }) => {
    await page.goto('/register')
    // Each role is a clickable block whose accessible name contains the
    // label. We scope by the role label text to disambiguate.
    await page.locator('div').filter({ hasText: /^Candidat/ }).first().click()
    // The "Continuer avec Google" button also matches /Continuer/, so we
    // anchor on the trailing arrow to pick the role-confirmation button.
    await page.getByRole('button', { name: /Continuer →/ }).click()
    await expect(page.getByRole('heading', { name: 'Vos informations' })).toBeVisible()
    await expect(page.getByPlaceholder('vous@exemple.com')).toBeVisible()
  })
})

test.describe('Phase 1 — forgot password', () => {
  // SMTP can be slow on Gmail; allow more time than the default 30s.
  test.slow()

  test('submitting an email shows the anti-enumeration success UI', async ({ page }) => {
    await page.goto('/forgot-password')
    await expect(page.getByRole('heading', { name: 'Mot de passe oublié ?' })).toBeVisible()

    // Random email so we never hit per-email throttling between local runs.
    await page.getByPlaceholder('vous@exemple.com').fill('e2e-' + Date.now() + '@example.com')
    await page.getByRole('button', { name: /Envoyer le lien/i }).click()

    // The success screen renders only when the backend POST resolves.
    await expect(page.getByRole('heading', { name: /Vérifiez votre boîte mail/i }))
      .toBeVisible({ timeout: 30_000 })
  })

  test('reset-password without a token shows the "lien invalide" screen', async ({ page }) => {
    await page.goto('/reset-password')
    await expect(page.getByRole('heading', { name: 'Lien invalide' })).toBeVisible()
    await expect(page.getByRole('link', { name: /Demander un nouveau lien/i })).toBeVisible()
  })
})

test.describe('Auth gate + Phase 5 profile section', () => {
  test('hitting a dashboard URL while logged out lands on /login', async ({ page }) => {
    await page.goto('/dashboard/profile')
    await expect(page).toHaveURL(/\/login/)
  })

  /**
   * Pre-warm a Sanctum token via the API and inject it into localStorage
   * before navigating. Much faster than driving the form (no SMTP, no
   * rate-limit pressure) and isolates from form-field changes.
   */
  async function loginViaApi(page: import('@playwright/test').Page) {
    const r = await page.request.post(`${BACKEND_URL}/api/login`, {
      data: { email: TEST_EMAIL, password: TEST_PASSWORD },
      headers: { Accept: 'application/json' },
    })
    expect(r.ok()).toBeTruthy()
    const body = await r.json()
    await page.addInitScript(({ token, user }) => {
      localStorage.setItem('token', token)
      localStorage.setItem('user', JSON.stringify(user))
    }, { token: body.token, user: body.user })
  }

  test('candidate dashboard loads with the welcome message', async ({ page }) => {
    await loginViaApi(page)
    await page.goto('/dashboard')
    await expect(page.getByText(/Bienvenue/i)).toBeVisible({ timeout: 10_000 })
  })

  test('profile shows the new "Méthodes de connexion" section (Phase 5)', async ({ page }) => {
    await loginViaApi(page)
    await page.goto('/dashboard/profile')

    // The page title isn't a semantic <h*>, so we match it as text.
    await expect(page.getByText('Mon profil', { exact: true })).toBeVisible({ timeout: 10_000 })
    await expect(page.getByText('Méthodes de connexion')).toBeVisible({ timeout: 10_000 })
    // The password row is always rendered.
    await expect(page.getByText('Mot de passe', { exact: true })).toBeVisible()
  })
})
