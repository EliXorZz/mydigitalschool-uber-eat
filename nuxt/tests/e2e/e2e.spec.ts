import { test, expect } from '@playwright/test';

test('authentification (login) @admin', async ({ page }) => {
  await page.goto('http://localhost:3000');
  await page.getByRole('link', { name: 'Se connecter' }).click();
  await page.waitForTimeout(5000)

  await page.getByRole('textbox', { name: 'Nom d\'utilisateur*' }).fill('admin');
  await page.getByRole('textbox', { name: 'Mot de passe*' }).fill('admin-mydigitalschool');

  await page.waitForTimeout(1000)

  await page.getByRole('button', { name: 'Se connecter' }).click()
  await page.waitForTimeout(1000)

  await page.getByRole('banner').getByRole('img').click();
  await page.waitForTimeout(1000)

  await page.getByRole('menuitem', { name: 'Administration' }).click();
  await page.waitForTimeout(1000)

  const item = page.getByText('Liste des restaurants')
  await expect(item).toBeVisible()
});

test('authentification (login) @owner', async ({ page }) => {
  await page.goto('http://localhost:3000');
  await page.getByRole('link', { name: 'Se connecter' }).click();
  await page.waitForTimeout(5000)

  await page.getByRole('textbox', { name: 'Nom d\'utilisateur*' }).fill('dylan');
  await page.getByRole('textbox', { name: 'Mot de passe*' }).fill('admin-mydigitalschool');

  await page.waitForTimeout(1000)

  await page.getByRole('button', { name: 'Se connecter' }).click()
  await page.waitForTimeout(1000)

  await page.getByRole('banner').getByRole('img').click();
  await page.waitForTimeout(1000)

  await page.getByRole('menuitem', { name: 'Administration' }).click();
  await page.waitForTimeout(1000)

  const item = page.getByRole('button', { name: 'Modifier le restaurant' })
  await expect(item).toBeVisible()
});

test('show item', async ({ page }) => {
  await page.goto('http://localhost:3000');

  await page.getByRole('link', { name: 'Mama Tokyo À emporter' }).click();
  await page.waitForTimeout(1000)

  await page.getByRole('link', { name: 'Ramen miso Ramen miso' }).click();
  await page.waitForTimeout(1000)

  const item = page.getByText('Bouillon maison, porc mariné');
  await expect(item).toBeVisible()
});