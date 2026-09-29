import { test, expect } from '@playwright/test';

/**
 * Pedidos migrados a Inertia: «Ver» por pasos, editar pagos / entrega /
 * prioridad (las líneas quedan fijas) y pantallas en móvil. Usa el pedido
 * sembrado «Taller Guanare OP» (abonado completo en efectivo).
 */
const CLIENTE_OP = 'Taller Guanare OP';

test('ver un pedido por pasos: cliente, productos y pago', async ({ page }) => {
  await page.goto('/pedidos', { waitUntil: 'networkidle' });
  const fila = page.getByRole('row', { name: new RegExp(CLIENTE_OP) });
  await fila.getByRole('button', { name: /Ver pedido #\d+/ }).click();
  const ver = page.getByRole('dialog', { name: /Pedido #\d+/ });
  await expect(ver).toContainText(CLIENTE_OP);
  await ver.getByRole('button', { name: 'Siguiente' }).click();
  await expect(ver.getByRole('row', { name: /Braga OP/ })).toBeVisible();
  await ver.getByRole('button', { name: 'Siguiente' }).click();
  await expect(ver.getByText('Efectivo')).toBeVisible();
  await expect(ver.getByText(/Tasa BCV \(\d{2}\/\d{2}\/\d{4}\)/)).toBeVisible();
});

test('editar la prioridad y los pagos de un pedido; las líneas no se tocan', async ({ page }) => {
  await page.goto('/pedidos', { waitUntil: 'networkidle' });
  const fila = page.getByRole('row', { name: new RegExp(CLIENTE_OP) });
  await fila.getByRole('button', { name: /Más acciones del pedido/ }).click();
  await page.getByRole('menuitem', { name: 'Editar pagos y entrega' }).click();
  await expect(page.getByRole('heading', { name: /Editar pedido #\d+/ })).toBeVisible();

  const formulario = page.locator('#form-pedido');
  await formulario.getByRole('radio', { name: 'Urgente' }).check();
  await formulario.getByRole('button', { name: 'Siguiente' }).click();
  await expect(formulario.getByText(/quedaron fijas al crearlo/)).toBeVisible();
  await formulario.getByRole('button', { name: 'Siguiente' }).click();

  // Una transferencia sin banco ni referencia no pasa.
  await formulario.getByLabel('Monto del pago 1 en dólares').fill('200');
  await formulario.getByRole('button', { name: /Transferencia/ }).click();
  await formulario.getByLabel('Monto del pago 2 en dólares').fill('40');
  await formulario.getByRole('button', { name: 'Siguiente' }).click();
  await expect(page.getByText('Las transferencias y los pagos móviles necesitan banco y referencia.')).toBeVisible();
  await formulario.getByRole('combobox', { name: 'Banco del pago 2' }).click();
  await page.getByRole('option', { name: 'Banco de Venezuela' }).click();
  await formulario.getByLabel('Referencia del pago 2').fill('556677');
  await formulario.getByRole('button', { name: 'Siguiente' }).click();
  await page.getByRole('button', { name: 'Guardar cambios' }).click();

  await expect(page.getByText(/Pedido #\d+ actualizado\./)).toBeVisible();
  // Vuelve al listado con la ficha abierta: ahí se ve lo guardado.
  const ver = page.getByRole('dialog', { name: /Pedido #\d+/ });
  await expect(ver).toContainText('Urgente');
  await ver.getByRole('button', { name: 'Siguiente' }).click();
  await ver.getByRole('button', { name: 'Siguiente' }).click();
  await expect(ver.getByText('Transferencia')).toBeVisible();
  await expect(ver.getByText('Ref. 556677')).toBeVisible();
});

test('en móvil las páginas no se desbordan', async ({ page }) => {
  await page.setViewportSize({ width: 375, height: 800 });
  for (const url of ['/pedidos', '/pedidos/crear']) {
    await page.goto(url, { waitUntil: 'networkidle' });
    const ancho = await page.evaluate(() => document.documentElement.scrollWidth);
    expect(ancho, url).toBeLessThanOrEqual(375);
  }
});
