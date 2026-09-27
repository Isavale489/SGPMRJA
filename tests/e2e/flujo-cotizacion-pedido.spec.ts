import { test, expect } from '@playwright/test';
import { CLIENTE } from './datos';

/**
 * Flujo real por la interfaz: cotización Aprobada (sembrada) → menú ⋮
 * "Convertir a Pedido" → wizard de Pedido (fechas, abono en efectivo) →
 * pedido guardado y cotización Convertida.
 */
test('convertir una cotización aprobada en pedido desde el wizard', async ({ page }) => {
  const errores: string[] = [];
  page.on('pageerror', (e) => errores.push(`JS: ${e.message}`));
  page.on('response', (r) => { if (r.status() >= 500) errores.push(`HTTP ${r.status()} ${r.url()}`); });

  await page.goto('/cotizaciones');
  const fila = page.locator('#cotizaciones-table tbody tr', { hasText: CLIENTE });
  await expect(fila).toBeVisible();
  await expect(fila).toContainText('Aprobada');

  await fila.locator('button[title="Más acciones"]').click();
  await fila.locator('.convert-to-pedido-btn').click();
  await expect(page).toHaveURL(/\/pedidos\?convertir=\d+/);

  // Paso 1 — cliente heredado de la cotización; fechas y prioridad.
  const wizard = page.locator('.modal.show');
  await expect(wizard.locator('#ped-wiz-step-1')).toBeVisible();
  const entrega = new Date(Date.now() + 10 * 86_400_000).toISOString().slice(0, 10);
  await wizard.locator('#ped-fecha-entrega-field').fill(entrega);
  await wizard.locator('#btn-ped-next').click();

  // Paso 2 — productos heredados (solo lectura).
  await expect(wizard.locator('#ped-wiz-step-2')).toBeVisible();
  await wizard.locator('#btn-ped-next').click();

  // Paso 3 — abono total en efectivo (cumple el mínimo configurado).
  await expect(wizard.locator('#ped-wiz-step-3')).toBeVisible();
  await wizard.locator('#ped-pay-add-efectivo').click();
  const total = await wizard.locator('#ped-pago-total-display').inputValue();
  await wizard.locator('#ped-pay-list .ped-pay-monto').first().fill(String(parseFloat(total)));
  await wizard.locator('#btn-ped-next').click();

  // Paso 4 — resumen y guardado.
  await expect(wizard.locator('#ped-wiz-step-4')).toBeVisible();
  await wizard.locator('#ped-wiz-add-btn').click();

  await expect(page.locator('.swal2-popup.swal2-icon-success')).toBeVisible({ timeout: 15_000 });

  await page.goto('/pedidos');
  await expect(page.locator('table.dataTable tbody tr', { hasText: CLIENTE })).toBeVisible();

  await page.goto('/cotizaciones');
  await expect(page.locator('#cotizaciones-table tbody tr', { hasText: CLIENTE })).toContainText('Convertida');

  expect(errores, errores.join('\n')).toEqual([]);
});
