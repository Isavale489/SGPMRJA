import { test, expect, type Page } from '@playwright/test';

/** Insumos migrado a Inertia + React, con su catálogo de tipos. Crea sus propios datos. */
test.describe.configure({ mode: 'serial' });

const TIPO = 'Entretela E2E';
const INSUMO = 'Entretela fusionable blanca';

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

test('agregar un tipo de insumo desde su catálogo', async ({ page }) => {
  const errores = vigilarErrores(page);
  await page.goto('/insumos');
  await page.getByRole('button', { name: 'Tipos de insumo' }).click();
  const dialogo = page.getByRole('dialog');
  await dialogo.getByLabel('Nombre del nuevo tipo').fill(TIPO);
  await dialogo.getByRole('button', { name: 'Agregar' }).click();
  await expect(page.getByText('Tipo de insumo creado correctamente.')).toBeVisible();
  await expect(dialogo.getByRole('list', { name: 'Tipos activos' })).toContainText(TIPO);

  // Uno repetido se marca en el campo.
  await dialogo.getByLabel('Nombre del nuevo tipo').fill(TIPO.toLowerCase());
  await dialogo.getByRole('button', { name: 'Agregar' }).click();
  await expect(dialogo.getByText('Ya existe un tipo de insumo con este nombre.')).toBeVisible();
  expect(errores, errores.join('\n')).toEqual([]);
});

test('alta de un insumo inventariable: el código queda en mayúsculas', async ({ page }) => {
  const errores = vigilarErrores(page);
  await page.goto('/insumos');
  await page.getByRole('button', { name: 'Agregar insumo' }).click();
  const dialogo = page.getByRole('dialog');
  await dialogo.getByLabel('Nombre').fill(INSUMO);
  await dialogo.getByLabel('Código').fill('efb01');
  await expect(dialogo.getByLabel('Código')).toHaveValue('EFB01');
  await elegir(page, 'Tipo', TIPO);
  await elegir(page, 'Unidad de medida', 'Metro');
  await dialogo.getByLabel('Costo unitario ($)').fill('1.5');
  await expect(dialogo.getByText(/Bs\s*60,00/)).toBeVisible(); // tasa del seeder: 40
  await dialogo.getByLabel('Existencia mínima').fill('10');
  await dialogo.getByLabel('Existencia actual').fill('12');
  await dialogo.getByLabel('Existencia máxima').fill('100');
  await dialogo.getByRole('button', { name: 'Agregar insumo' }).click();

  await expect(page.getByText('Insumo creado exitosamente.')).toBeVisible();
  const fila = page.getByRole('row', { name: new RegExp(INSUMO) });
  await expect(fila).toContainText('EFB01');
  await expect(fila).toContainText('Medio'); // 12 ≤ 1,5 × 10
  expect(errores, errores.join('\n')).toEqual([]);
});

test('existencia máxima menor que la mínima se marca en el campo', async ({ page }) => {
  await page.goto('/insumos');
  await page.getByRole('button', { name: 'Agregar insumo' }).click();
  const dialogo = page.getByRole('dialog');
  await dialogo.getByLabel('Nombre').fill('Hilo de prueba');
  await elegir(page, 'Tipo', TIPO);
  await elegir(page, 'Unidad de medida', 'Cono');
  await dialogo.getByLabel('Costo unitario ($)').fill('2');
  await dialogo.getByLabel('Existencia mínima').fill('20');
  await dialogo.getByLabel('Existencia máxima').fill('5');
  await dialogo.getByRole('button', { name: 'Agregar insumo' }).click();

  await expect(dialogo.getByText('La existencia máxima no puede ser menor que la mínima.')).toBeVisible();
});

test('un insumo no inventariable no lleva existencias', async ({ page }) => {
  await page.goto('/insumos');
  await page.getByRole('button', { name: 'Agregar insumo' }).click();
  const dialogo = page.getByRole('dialog');
  await dialogo.getByLabel('Nombre').fill('Etiqueta de talla E2E');
  await elegir(page, 'Tipo', TIPO);
  await elegir(page, 'Unidad de medida', 'Unidad');
  await dialogo.getByLabel('Costo unitario ($)').fill('0.1');
  await dialogo.getByRole('switch', { name: 'Inventariable' }).click();
  await expect(dialogo.getByLabel('Existencia actual')).toHaveCount(0);
  await dialogo.getByRole('button', { name: 'Agregar insumo' }).click();

  await expect(page.getByText('Insumo creado exitosamente.')).toBeVisible();
  await expect(page.getByRole('row', { name: /Etiqueta de talla E2E/ })).toContainText('No inventariable');
});

test('al editar, un código ya asignado no se puede cambiar', async ({ page }) => {
  await page.goto('/insumos');
  await page.getByRole('button', { name: `Más acciones para ${INSUMO}` }).click();
  await page.getByRole('menuitem', { name: 'Editar' }).click();
  const dialogo = page.getByRole('dialog');
  await expect(dialogo.getByLabel('Código')).toHaveAttribute('readonly', '');
  await dialogo.getByLabel('Costo unitario ($)').fill('1.75');
  await dialogo.getByRole('button', { name: 'Guardar cambios' }).click();

  await expect(page.getByText('Insumo actualizado exitosamente.')).toBeVisible();
  await expect(page.getByRole('row', { name: new RegExp(INSUMO) })).toContainText('$1,75');
});

test('un tipo con insumos no se inhabilita; renombrarlo se propaga', async ({ page }) => {
  await page.goto('/insumos');
  await page.getByRole('button', { name: 'Tipos de insumo' }).click();
  const dialogo = page.getByRole('dialog');
  await expect(dialogo.getByRole('button', { name: `Inhabilitar ${TIPO}` })).toBeDisabled();
  await dialogo.getByRole('button', { name: `Renombrar ${TIPO}` }).click();
  await dialogo.getByLabel(`Nuevo nombre de ${TIPO}`).fill(`${TIPO} 2`);
  await dialogo.getByRole('button', { name: 'Guardar nombre' }).click();
  await expect(page.getByText('Tipo de insumo actualizado correctamente.')).toBeVisible();
  await page.keyboard.press('Escape');

  await expect(page.getByRole('row', { name: new RegExp(INSUMO) })).toContainText(`${TIPO} 2`);
});

test('inhabilitar un insumo y volver a habilitarlo', async ({ page }) => {
  await page.goto('/insumos');
  await page.getByRole('button', { name: 'Más acciones para Etiqueta de talla E2E' }).click();
  await page.getByRole('menuitem', { name: 'Inhabilitar' }).click();
  await page.getByRole('alertdialog').getByRole('button', { name: 'Inhabilitar' }).click();
  await expect(page.getByText('Insumo inhabilitado exitosamente.')).toBeVisible();
  await expect(page.getByRole('menu')).toHaveCount(0);

  await page.getByRole('link', { name: 'Inhabilitados' }).click();
  await page.getByRole('button', { name: 'Más acciones para Etiqueta de talla E2E' }).click();
  await page.getByRole('menuitem', { name: 'Habilitar' }).click();
  await expect(page.getByText('Insumo habilitado exitosamente.')).toBeVisible();
});

test('en móvil la página no se desborda', async ({ page }) => {
  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto('/insumos');
  const desborde = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
  expect(desborde).toBeLessThanOrEqual(0);
});
