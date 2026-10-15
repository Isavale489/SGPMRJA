import { test, expect, type Page } from '@playwright/test';

import { CLIENTE, EMPLEADO } from './datos';

/** Clientes migrado a Inertia + React. Crea sus propios datos (documentos únicos). */
test.describe.configure({ mode: 'serial' });

const NATURAL = { cedula: '18765432', nombre: 'Rosa Linares' };

function vigilarErrores(page: Page): string[] {
  const errores: string[] = [];
  page.on('pageerror', (e) => errores.push(`JS: ${e.message}`));
  page.on('response', (r) => { if (r.status() >= 500) errores.push(`HTTP ${r.status()} ${r.url()}`); });
  return errores;
}

async function elegir(page: Page, etiqueta: string | RegExp, opcion: string) {
  await page.getByRole('dialog').getByRole('combobox', { name: etiqueta }).click();
  await page.getByRole('option', { name: opcion, exact: true }).click();
}

test('alta de un cliente natural', async ({ page }) => {
  const errores = vigilarErrores(page);
  await page.goto('/clientes');
  await expect(page.getByRole('heading', { name: 'Clientes', exact: true })).toBeVisible();

  await page.getByRole('button', { name: 'Agregar cliente' }).click();
  const dialogo = page.getByRole('dialog');
  await dialogo.getByRole('textbox', { name: 'Cédula' }).fill(NATURAL.cedula);
  await dialogo.getByLabel('Nombre y apellido').fill(NATURAL.nombre);
  await dialogo.getByLabel('Correo electrónico').fill('rosa.linares@correo.com');
  await dialogo.getByLabel('Dirección').fill('Calle 3, Acarigua');
  await dialogo.getByLabel('Número del teléfono 1').fill('5551234');
  await elegir(page, 'Estado', 'Portuguesa');
  await elegir(page, 'Municipio', 'Páez');
  await dialogo.getByRole('button', { name: 'Agregar cliente' }).click();

  await expect(page.getByText('Cliente creado exitosamente.')).toBeVisible();
  await expect(dialogo).toBeHidden();
  const fila = page.getByRole('row', { name: new RegExp(NATURAL.nombre) });
  await expect(fila).toContainText(`V-${NATURAL.cedula}`);
  await expect(fila).toContainText('Natural');
  expect(errores, errores.join('\n')).toEqual([]);
});

test('el prefijo G- define un cliente gubernamental', async ({ page }) => {
  await page.goto('/clientes');
  await page.getByRole('button', { name: 'Agregar cliente' }).click();
  const dialogo = page.getByRole('dialog');
  await elegir(page, 'Prefijo del documento', 'G-');
  await expect(dialogo.getByText(/Cliente gubernamental\./)).toBeVisible();
  await dialogo.getByRole('textbox', { name: 'RIF' }).fill('200001234');
  await dialogo.getByLabel('Razón social').fill('Alcaldía de Araure');
  await dialogo.getByLabel('Número del teléfono 1').fill('5559876');
  await dialogo.getByRole('button', { name: 'Agregar cliente' }).click();

  await expect(page.getByText('Cliente creado exitosamente.')).toBeVisible();
  await expect(page.getByRole('row', { name: /Alcaldía de Araure/ })).toContainText('Gubernamental');
});

test('un documento que ya es cliente se avisa y no deja guardar', async ({ page }) => {
  await page.goto('/clientes');
  await page.getByRole('button', { name: 'Agregar cliente' }).click();
  const dialogo = page.getByRole('dialog');
  await dialogo.getByRole('textbox', { name: 'Cédula' }).fill('12345678'); // CLIENTE del seeder
  await dialogo.getByLabel('Nombre y apellido').click(); // sale del documento

  await expect(dialogo.getByText('Este documento ya está registrado como cliente.')).toBeVisible();
  await expect(dialogo.getByRole('button', { name: 'Agregar cliente' })).toBeDisabled();
});

test('una persona que ya es empleado se reutiliza con sus datos', async ({ page }) => {
  const errores = vigilarErrores(page);
  await page.goto('/clientes');
  await page.getByRole('button', { name: 'Agregar cliente' }).click();
  const dialogo = page.getByRole('dialog');
  await dialogo.getByRole('textbox', { name: 'Cédula' }).fill(EMPLEADO.cedula);
  await dialogo.getByLabel('Nombre y apellido').click();

  await expect(dialogo.getByText(/ya está registrada como empleado/)).toBeVisible();
  await expect(dialogo.getByRole('button', { name: 'Agregar cliente' })).toBeDisabled();
  await dialogo.getByRole('button', { name: 'Usar sus datos' }).click();
  await expect(dialogo.getByLabel('Nombre y apellido')).toHaveValue(EMPLEADO.nombre);
  await expect(dialogo.getByLabel('Nombre y apellido')).toHaveAttribute('readonly', '');
  await dialogo.getByLabel('Número del teléfono 1').fill('5554321');
  await dialogo.getByRole('button', { name: 'Agregar cliente' }).click();

  await expect(page.getByText('Cliente creado exitosamente.')).toBeVisible();
  await page.getByRole('button', { name: `Ver ${EMPLEADO.nombre}` }).click();
  await expect(page.getByRole('dialog')).toContainText('También registrado como');
  expect(errores, errores.join('\n')).toEqual([]);
});

test('editar conserva el documento y guarda los cambios', async ({ page }) => {
  await page.goto('/clientes');
  await page.getByRole('button', { name: `Más acciones para ${NATURAL.nombre}` }).click();
  await page.getByRole('menuitem', { name: 'Editar' }).click();
  const dialogo = page.getByRole('dialog');
  await expect(dialogo.getByRole('textbox', { name: 'Cédula' })).toBeDisabled();
  await dialogo.getByLabel('Nombre y apellido').fill(`${NATURAL.nombre} Pérez`);
  await dialogo.getByRole('button', { name: 'Guardar cambios' }).click();

  await expect(page.getByText('Cliente actualizado exitosamente.')).toBeVisible();
  await expect(page.getByRole('row', { name: new RegExp(`${NATURAL.nombre} Pérez`) })).toContainText(`V-${NATURAL.cedula}`);
});

test('inhabilitar lo pasa al historial y se puede restaurar', async ({ page }) => {
  await page.goto('/clientes');
  await page.getByRole('button', { name: `Más acciones para ${CLIENTE}` }).click();
  await page.getByRole('menuitem', { name: 'Inhabilitar' }).click();
  await page.getByRole('alertdialog').getByRole('button', { name: 'Inhabilitar' }).click();
  // El cliente del seeder tiene una cotización: el aviso lo menciona.
  await expect(page.getByText(/Cliente inhabilitado exitosamente\. Este cliente tenía 1 cotización/)).toBeVisible();
  await expect(page.getByRole('menu')).toHaveCount(0);

  await page.getByRole('link', { name: 'Inhabilitados' }).click();
  await page.getByRole('button', { name: `Más acciones para ${CLIENTE}` }).click();
  await page.getByRole('menuitem', { name: 'Restaurar' }).click();
  await expect(page.getByText('Cliente restaurado exitosamente.')).toBeVisible();
});

test('en móvil la página no se desborda', async ({ page }) => {
  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto('/clientes');
  const desborde = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
  expect(desborde).toBeLessThanOrEqual(0);
});
