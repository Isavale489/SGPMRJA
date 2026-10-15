import { test as setup, expect } from '@playwright/test';
import { ADMIN } from './datos';

// Inicia sesión una vez y guarda la sesión para el resto de los tests.
setup('login del administrador', async ({ page }) => {
  await page.goto('/login');
  await page.locator('input[name="email"]').fill(ADMIN.email);
  await page.locator('input[name="password"]').fill(ADMIN.password);
  await page.locator('#submitBtn').click();
  await expect(page).not.toHaveURL(/\/login/);
  await page.context().storageState({ path: 'tests/e2e/.auth/admin.json' });
});
