import { test, expect, type Page } from '@playwright/test';

/**
 * Movimientos de insumo migrados a Inertia. Usa «Hilo Mov E2E» del E2eSeeder
 * (12 conos, mínimo 15: arranca en alerta).
 */
test.describe.configure({ mode: 'serial' });

function vigilarErrores(page: Page): string[] {
  const errores: string[] = [];
  page.on('pageerror', (e) => errores.push(`JS: ${e.message}`));
  page.on('response', (r) => { if (r.status() >= 500) errores.push(`HTTP ${r.status()} ${r.url()}`); });
  return errores;
}

test('registrar una salida: descuenta la existencia y rechaza sacar de más', async ({ page }) => {
  const errores = vigilarErrores(page);
  await page.goto('/movimiento-insumo');
  await expect(page.getByRole('heading', { name: 'Movimientos de insumos' })).toBeVisible();

  await page.getByRole('button', { name: 'Registrar salida' }).click();
  const d = page.getByRole('dialog', { name: 'Registrar salida' });
  await d.getByLabel('Insumo').click();
  await page.getByRole('option', { name: /Hilo Mov E2E/ }).click();
  await expect(d.getByText('Existencia actual: 12 Cono')).toBeVisible();

  // Más de lo que hay: el servidor lo rechaza en el campo.
  await d.getByLabel('Cantidad').fill('50');
  await d.getByLabel('Motivo').fill('Consumo en taller');
  await d.getByRole('button', { name: 'Registrar salida' }).click();
  await expect(d.getByText(/No hay suficiente existencia: quedan 12/)).toBeVisible();

  await d.getByLabel('Cantidad').fill('2');
  await d.getByRole('button', { name: 'Registrar salida' }).click();
  await expect(page.getByText('Salida registrada correctamente.')).toBeVisible();

  const fila = page.getByRole('row', { name: /Hilo Mov E2E/ }).first();
  await expect(fila).toContainText('Salida');
  await expect(fila).toContainText('12 → 10');
  expect(errores, errores.join('\n')).toEqual([]);
});

test('pestaña de existencias con filtro de alerta, e historial del insumo', async ({ page }) => {
  await page.goto('/movimiento-insumo');
  await page.getByRole('tab', { name: 'Existencias' }).click();
  await expect(page).toHaveURL(/vista=existencias/);
  await page.getByRole('button', { name: 'Solo en o bajo el mínimo' }).click();
  await expect(page).toHaveURL(/alerta=1/);
  const fila = page.getByRole('row', { name: /Hilo Mov E2E/ });
  await expect(fila).toContainText('En o bajo el mínimo');
  await expect(page.getByRole('row', { name: /Oxford E2E/ })).toHaveCount(0);

  await fila.getByRole('link').click();
  await expect(page.getByRole('heading', { name: 'Historial de Hilo Mov E2E' })).toBeVisible();
  await expect(page.getByRole('row', { name: /Consumo en taller/ })).toBeVisible();
});

test('alertas y rotación', async ({ page }) => {
  await page.goto('/movimiento-insumo/alertas');
  await expect(page.getByRole('row', { name: /Hilo Mov E2E/ })).toBeVisible();
  // Reponer: abre una compra prellenada (mínimo 15 − existencia 10 = 5).
  await page.getByRole('button', { name: 'Comprar lo que falta' }).click();
  await expect(page).toHaveURL(/\/compras\/crear\?prefill=1/);
  await expect(page.getByLabel('Cantidad de Hilo Mov E2E')).toHaveValue('5');
  await page.goto('/movimiento-insumo/rotacion');
  await expect(page.getByRole('row', { name: /Hilo Mov E2E/ })).toContainText('2 Cono');
});

test('en móvil la página no se desborda', async ({ page }) => {
  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto('/movimiento-insumo');
  const desborde = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
  expect(desborde).toBeLessThanOrEqual(0);
});
