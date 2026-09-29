import { test, expect } from '@playwright/test';
import { ADMIN } from './datos';

/**
 * Cerrar sesión desde el menú del usuario. Usa una sesión propia (no la
 * compartida de auth.setup): el logout la invalida en el servidor.
 */
test.use({ storageState: { cookies: [], origins: [] } });

test('cerrar sesión desde el menú del usuario lleva al login y corta el acceso', async ({ page }) => {
  await page.goto('/login');
  await page.locator('input[name="email"]').fill(ADMIN.email);
  await page.locator('input[name="password"]').fill(ADMIN.password);
  await page.locator('#submitBtn').click();
  await expect(page).not.toHaveURL(/\/login/);

  // Navegar por Inertia antes (el token del <meta> es el de la carga inicial).
  await page.goto('/dashboard');
  await page.getByRole('link', { name: 'Clientes' }).first().click();
  await expect(page).toHaveURL(/\/clientes/);

  await page.getByRole('button', { name: /Admin E2E/ }).click();
  await page.getByRole('menuitem', { name: 'Cerrar sesión' }).click();

  await expect(page).not.toHaveURL(/\/clientes/);
  await page.goto('/dashboard');
  await expect(page).toHaveURL(/\/login/);
});
