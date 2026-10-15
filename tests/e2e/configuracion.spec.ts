import { test, expect } from '@playwright/test';

/** Configuración del sistema migrada a Inertia: parámetros del registry e impuestos. */
test.describe.configure({ mode: 'serial' });

test('cambiar un parámetro, ver el error de uno inválido y restablecerlo', async ({ page }) => {
  const errores: string[] = [];
  page.on('pageerror', (e) => errores.push(e.message));
  await page.goto('/configuracion#cotizaciones');
  const dias = page.getByLabel(/Vigencia de precios por defecto/);
  await expect(page.getByText('Por defecto: 15 días')).toBeVisible();
  await dias.fill('20');
  await page.getByRole('button', { name: 'Guardar' }).click();
  await expect(page.getByText('Configuración guardada correctamente.')).toBeVisible();
  await expect(page.getByRole('button', { name: /Restablecer \(15 días\)/ })).toBeVisible();

  await page.getByRole('button', { name: 'Pedidos' }).click();
  await page.getByLabel(/Abono mínimo/).fill('150');
  await page.getByRole('button', { name: 'Guardar' }).click();
  await expect(page.getByText('El campo Abono mínimo (%) no debe ser mayor a 100.')).toBeVisible();
  await page.getByRole('button', { name: 'Descartar cambios' }).click();

  await page.getByRole('button', { name: 'Cotizaciones' }).click();
  await page.getByRole('button', { name: /Restablecer \(15 días\)/ }).click();
  await expect(page.getByText('Parámetro restablecido a su valor por defecto.')).toBeVisible();
  await expect(dias).toHaveValue('15');
  expect(errores).toEqual([]);
});

test('impuestos: agregar, editar y eliminar; el IVA no se elimina', async ({ page }) => {
  await page.goto('/configuracion#impuestos');
  await page.getByRole('button', { name: 'Agregar impuesto' }).click();
  const d = page.getByRole('dialog', { name: 'Agregar impuesto' });
  await d.getByLabel('Código').fill('igtf');
  await expect(d.getByLabel('Código')).toHaveValue('IGTF');
  await d.getByLabel('Nombre').fill('IGTF');
  await d.getByLabel('Porcentaje').fill('3');
  await d.getByRole('button', { name: 'Agregar impuesto' }).click();
  await expect(page.getByText('Impuesto creado correctamente.')).toBeVisible();

  await page.getByRole('button', { name: 'Editar IGTF' }).click();
  await page.getByRole('dialog').getByLabel('Porcentaje').fill('2');
  await page.getByRole('dialog').getByRole('button', { name: 'Guardar cambios' }).click();
  await expect(page.getByRole('row', { name: /IGTF/ })).toContainText('2 %');

  await page.getByRole('button', { name: 'Eliminar IGTF' }).click();
  await page.getByRole('alertdialog').getByRole('button', { name: 'Eliminar' }).click();
  await expect(page.getByText('Impuesto eliminado.')).toBeVisible();
  await expect(page.getByRole('row', { name: /IGTF/ })).toHaveCount(0);

  const iva = page.getByRole('row', { name: /IVA/ });
  if (await iva.count()) await expect(iva.getByRole('button', { name: /Eliminar/ })).toHaveCount(0);
});

test('el menú de usuario lleva a Configuración', async ({ page }) => {
  await page.goto('/proveedores');
  await page.getByRole('button', { name: /Admin E2E/ }).click();
  await page.getByRole('menuitem', { name: 'Configuración' }).click();
  await expect(page.getByRole('heading', { name: 'Configuración del sistema' })).toBeVisible();
});

test('en móvil no se desborda', async ({ page }) => {
  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto('/configuracion');
  expect(await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth)).toBeLessThanOrEqual(0);
});
