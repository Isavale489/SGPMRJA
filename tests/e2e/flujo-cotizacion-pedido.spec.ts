import { test, expect } from '@playwright/test';
import { CLIENTE } from './datos';

/**
 * Flujo real por la interfaz: cotización Aprobada (sembrada) → menú ⋮
 * «Convertir a pedido» → asistente de Pedido (entrega, abono mínimo en efectivo)
 * → pedido guardado y cotización Convertida.
 */
test('convertir una cotización aprobada en pedido desde el asistente', async ({ page }) => {
  const errores: string[] = [];
  page.on('pageerror', (e) => errores.push(`JS: ${e.message}`));
  page.on('response', (r) => { if (r.status() >= 500) errores.push(`HTTP ${r.status()} ${r.url()}`); });

  await page.goto('/cotizaciones', { waitUntil: 'networkidle' });
  const fila = page.getByRole('row', { name: new RegExp(CLIENTE) }).filter({ hasText: 'Aprobada' });
  await expect(fila).toBeVisible();
  await fila.getByRole('button', { name: /Más acciones de la cotización/ }).click();
  await page.getByRole('menuitem', { name: 'Convertir a pedido' }).click();
  await expect(page).toHaveURL(/\/pedidos\/crear\?cotizacion=\d+/);
  await expect(page.getByRole('heading', { name: 'Nuevo pedido' })).toBeVisible();

  // Paso 1 — la cotización y su cliente; entrega propuesta en días hábiles.
  const formulario = page.locator('#form-pedido');
  await expect(formulario.getByText(/Cotización #\d+/)).toBeVisible();
  await expect(formulario.getByText(CLIENTE).first()).toBeVisible();
  await formulario.getByRole('button', { name: '30 días hábiles' }).click();
  await formulario.getByRole('button', { name: 'Siguiente' }).click();

  // Paso 2 — productos de la cotización (solo lectura).
  await expect(formulario.getByRole('row', { name: /Chemise/ })).toBeVisible();
  await formulario.getByRole('button', { name: 'Siguiente' }).click();

  // Paso 3 — sin pagos no se avanza; «Efectivo» propone el abono mínimo.
  await formulario.getByRole('button', { name: 'Siguiente' }).click();
  await expect(page.getByText(/no alcanza el mínimo/)).toBeVisible();
  await formulario.getByRole('button', { name: /Efectivo/ }).click();
  await expect(formulario.getByLabel('Monto del pago 1 en dólares')).toHaveValue('90'); // 50 % de $180
  await formulario.getByRole('button', { name: 'Siguiente' }).click();

  // Paso 4 — resumen y guardado.
  await expect(formulario.getByText('Saldo por cobrar').first()).toBeVisible();
  await page.getByRole('button', { name: 'Crear pedido' }).click();
  await expect(page.getByText(/Pedido #\d+ creado\./)).toBeVisible();
  await expect(page).toHaveURL(/\/pedidos\?ver=\d+/);
  await expect(page.getByRole('dialog', { name: /Pedido #\d+/ })).toContainText(CLIENTE);

  await page.goto('/cotizaciones', { waitUntil: 'networkidle' });
  await expect(page.getByRole('row', { name: new RegExp(CLIENTE) })).toContainText('Convertida');
  expect(errores, errores.join('\n')).toEqual([]);
});
