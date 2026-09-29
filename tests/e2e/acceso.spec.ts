import { test, expect, type Page } from '@playwright/test';
import { ADMIN } from './datos';

/**
 * Pantallas de acceso en Inertia: login y recuperación por preguntas. Sin sesión
 * propia (no la compartida). La recuperación llega hasta la pantalla de nueva
 * clave SIN guardarla: cambiar la clave del admin rompería los demás specs.
 */
test.use({ storageState: { cookies: [], origins: [] } });

function vigilarErrores(page: Page): string[] {
  const errores: string[] = [];
  page.on('pageerror', (e) => errores.push(`JS: ${e.message}`));
  page.on('response', (r) => { if (r.status() >= 500 || (r.status() === 404 && !r.url().includes('favicon'))) errores.push(`HTTP ${r.status()} ${r.url()}`); });
  return errores;
}

test('el login muestra el error, deja ver la clave y entra', async ({ page }) => {
  const errores = vigilarErrores(page);
  await page.goto('/login');
  await expect(page.getByRole('heading', { name: 'Bienvenido de nuevo' })).toBeVisible();
  expect(await page.locator('link[href*="bootstrap"]').count()).toBe(0);

  await page.getByLabel('Correo electrónico').fill(ADMIN.email);
  await page.getByLabel('Contraseña', { exact: true }).fill('Incorrecta.1');
  await page.screenshot({ path: 'test-results/acceso-login.png' });
  await page.getByRole('button', { name: 'Mostrar la contraseña' }).click();
  await expect(page.getByLabel('Contraseña', { exact: true })).toHaveAttribute('type', 'text');
  await page.locator('#submitBtn').click();
  await expect(page.getByText(/credenciales|no coinciden/i)).toBeVisible();
  await expect(page.getByLabel('Contraseña', { exact: true })).toHaveValue(''); // la clave no se queda escrita

  await page.getByLabel('Contraseña', { exact: true }).fill(ADMIN.password);
  await page.locator('#submitBtn').click();
  await expect(page).toHaveURL(/\/dashboard/);
  expect(errores, errores.join('\n')).toEqual([]);
});

test('la recuperación por preguntas: correo, respuestas y nueva clave', async ({ page }) => {
  const errores = vigilarErrores(page);
  await page.goto('/login');
  await page.getByRole('link', { name: '¿Olvidaste tu contraseña?' }).click();
  await expect(page.getByRole('heading', { name: 'Recuperar contraseña' })).toBeVisible();
  await page.getByRole('link', { name: /Preguntas de seguridad/ }).click();

  // Un correo sin cuenta: mensaje genérico (no revela si existe).
  await page.getByLabel('Correo electrónico').fill('nadie@atlantico.test');
  await page.locator('#submitBtn').click();
  await expect(page.getByRole('alert')).toContainText('Si el correo está registrado');

  await page.getByLabel('Correo electrónico').fill(ADMIN.email);
  await page.locator('#submitBtn').click();
  await expect(page.getByRole('heading', { name: 'Verificar identidad' })).toBeVisible();
  const respuestas = page.getByPlaceholder('Tu respuesta…');
  await expect(respuestas).toHaveCount(3);

  // Una mal: error y vuelve a pedirlas.
  await respuestas.nth(0).fill('respuesta');
  await respuestas.nth(1).fill('respuesta');
  await respuestas.nth(2).fill('otra cosa');
  await page.locator('#submitBtn').click();
  await expect(page.getByRole('alert')).toContainText('Una o más respuestas son incorrectas.');

  // Bien (con mayúsculas y espacios de más, que no importan): pasa a la nueva clave.
  await respuestas.nth(0).fill('  RESPUESTA ');
  await respuestas.nth(1).fill('respuesta');
  await respuestas.nth(2).fill('Respuesta');
  await page.screenshot({ path: 'test-results/acceso-preguntas.png' });
  await page.locator('#submitBtn').click();
  await expect(page.getByText('Identidad verificada')).toBeVisible();
  await expect(page).toHaveURL(/\/recovery\/reset\//);
  await expect(page.getByLabel('Nueva contraseña')).toBeVisible();
  expect(errores, errores.join('\n')).toEqual([]);
});

test('en móvil el login no se desborda', async ({ page }) => {
  await page.setViewportSize({ width: 375, height: 800 });
  await page.goto('/login');
  const ancho = await page.evaluate(() => document.documentElement.scrollWidth);
  expect(ancho).toBeLessThanOrEqual(375);
});
