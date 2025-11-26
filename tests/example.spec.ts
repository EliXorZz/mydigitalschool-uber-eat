import { test, expect } from '@playwright/test';

test('authentification (login) @admin', async ({ page }) => {
  await page.goto('http://localhost:3000');
  await page.getByRole('link', { name: 'Se connecter' }).click();

  await page.getByRole('textbox', { name: 'Nom d\'utilisateur*' }).fill('admin');
  await page.getByRole('textbox', { name: 'Mot de passe*' }).fill('admin-mydigitalschool');

  await page.waitForTimeout(10000)

  await page.getByRole('button', { name: 'Se connecter' }).click()

  await page.waitForTimeout(10000)
  //
  // await page.getByRole('banner').getByRole('img').click();
  // await page.getByRole('menuitem', { name: 'Administration' }).click();
  //
  // const item = page.getByText('Liste des restaurants')
  // await expect(item).toBeVisible()
});