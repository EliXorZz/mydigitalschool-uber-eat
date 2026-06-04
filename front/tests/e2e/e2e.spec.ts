import { test, expect, type Page } from '@playwright/test'

const emptyRestaurants = {
  data: [],
  current_page: 1,
  last_page: 1,
  per_page: 12,
  total: 0
}

async function mockRestaurants(page: Page) {
  await page.route('**/api/restaurants**', route =>
    route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify(emptyRestaurants) })
  )
}

async function mockAuth(page: Page, user: object) {
  await page.route('**/api/auth/login', route =>
    route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({ data: { token: 'test-token', type: 'bearer', expires_in: 3600 } })
    })
  )
  await page.route('**/api/auth/me', route =>
    route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ data: user }) })
  )
  await page.route('**/api/broadcasting/auth', route => route.fulfill({ status: 403 }))
}

test('page d\'accueil accessible', async ({ page }) => {
  await mockRestaurants(page)
  await page.goto('http://localhost:3000')
  await expect(page).toHaveTitle(/Eat Research/)
})

test('connexion admin - accès administration', async ({ page }) => {
  await mockRestaurants(page)
  await mockAuth(page, { id: 1, name: 'Admin', email: 'admin@test.com', role: 'admin', avatar: null })

  await page.goto('http://localhost:3000/auth/login')
  await page.getByRole('textbox', { name: 'Adresse mail' }).fill('admin@test.com')
  await page.getByRole('textbox', { name: 'Mot de passe' }).fill('password')
  await page.getByRole('button', { name: 'Se connecter' }).click()

  await expect(page.getByRole('navigation').getByRole('img')).toBeVisible({ timeout: 10000 })
  await page.getByRole('navigation').getByRole('img').click()
  await expect(page.getByRole('menuitem', { name: 'Administration' })).toBeVisible()
})

test('connexion owner - accès administration', async ({ page }) => {
  await mockRestaurants(page)
  await mockAuth(page, { id: 2, name: 'Dylan', email: 'dylan@test.com', role: 'owner', avatar: null })

  await page.goto('http://localhost:3000/auth/login')
  await page.getByRole('textbox', { name: 'Adresse mail' }).fill('dylan@test.com')
  await page.getByRole('textbox', { name: 'Mot de passe' }).fill('password')
  await page.getByRole('button', { name: 'Se connecter' }).click()

  await expect(page.getByRole('navigation').getByRole('img')).toBeVisible({ timeout: 10000 })
  await page.getByRole('navigation').getByRole('img').click()
  await expect(page.getByRole('menuitem', { name: 'Administration' })).toBeVisible()
})
