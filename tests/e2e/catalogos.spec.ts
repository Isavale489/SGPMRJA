import { test, expect, type Page } from '@playwright/test';

/** Catálogos simples migrados a Inertia: Departamentos, Cargos y Colores. */
test.describe.configure({ mode: 'serial' });

const DEPTO = 'Bordado Industrial';
const CARGO = 'Operador de Bordadora';

function vigilarErrores(page: Page): string[] {
  const errores: string[] = [];
  page.on('pageerror', (e) => errores.push(`JS: ${e.message}`));
  page.on('response', (r) => { if (r.status() >= 500) errores.push(`HTTP ${r.status()} ${r.url()}`); });
  return errores;
}

test('crear un departamento y un cargo dentro de él', async ({ page }) => {
  const errores = vigilarErrores(page);

  await page.goto('/departamentos');
  await page.getByRole('button', { name: 'Agregar departamento' }).click();
  await page.getByRole('dialog').getByLabel('Nombre').fill(DEPTO);
  await page.getByRole('dialog').getByRole('button', { name: 'Agregar departamento' }).click();
  await expect(page.getByText('Departamento creado correctamente.')).toBeVisible();
  await expect(page.getByRole('row', { name: new RegExp(DEPTO) })).toBeVisible();

  await page.goto('/cargos');
  await page.getByRole('button', { name: 'Agregar cargo' }).click();
  const dialogo = page.getByRole('dialog');
  await dialogo.getByRole('combobox', { name: 'Departamento' }).click();
  await page.getByRole('option', { name: DEPTO }).click();
  await dialogo.getByLabel('Nombre').fill(CARGO);
  await dialogo.getByRole('button', { name: 'Agregar cargo' }).click();
  await expect(page.getByText('Cargo creado correctamente.')).toBeVisible();
  await expect(page.getByRole('row', { name: new RegExp(CARGO) })).toContainText(DEPTO);

  expect(errores, errores.join('\n')).toEqual([]);
});

test('un cargo repetido en el mismo departamento se marca en el campo', async ({ page }) => {
  await page.goto('/cargos');
  await page.getByRole('button', { name: 'Agregar cargo' }).click();
  const dialogo = page.getByRole('dialog');
  await dialogo.getByRole('combobox', { name: 'Departamento' }).click();
  await page.getByRole('option', { name: DEPTO }).click();
  await dialogo.getByLabel('Nombre').fill(CARGO.toLowerCase());
  await dialogo.getByRole('button', { name: 'Agregar cargo' }).click();

  await expect(dialogo.getByText('Ya existe un cargo con este nombre en el departamento seleccionado.')).toBeVisible();
});

test('un departamento con cargos no se inhabilita: se avisa y sigue activo', async ({ page }) => {
  await page.goto('/departamentos');
  await page.getByRole('button', { name: `Acciones para ${DEPTO}` }).click();
  await page.getByRole('menuitem', { name: 'Inhabilitar' }).click();
  await page.getByRole('alertdialog').getByRole('button', { name: 'Inhabilitar' }).click();

  await expect(page.getByText('No se puede inhabilitar: el departamento tiene cargos asociados.')).toBeVisible();
  // Regresión: la confirmación vivía dentro del menú ⋮ y lo dejaba abierto (página inaccesible).
  await expect(page.getByRole('menu')).toHaveCount(0);
  await expect(page.getByRole('row', { name: new RegExp(DEPTO) })).toBeVisible();
});

test('inhabilitar y restaurar un cargo', async ({ page }) => {
  await page.goto('/cargos');
  await page.getByRole('button', { name: `Acciones para ${CARGO}` }).click();
  await page.getByRole('menuitem', { name: 'Inhabilitar' }).click();
  await page.getByRole('alertdialog').getByRole('button', { name: 'Inhabilitar' }).click();
  await expect(page.getByText('Cargo inhabilitado correctamente.')).toBeVisible();

  await page.getByRole('link', { name: 'Inhabilitados' }).click();
  await page.getByRole('button', { name: `Acciones para ${CARGO}` }).click();
  await page.getByRole('menuitem', { name: 'Restaurar' }).click();
  await expect(page.getByText('Cargo restaurado correctamente.')).toBeVisible();
});

test('crear un color: el HEX se normaliza y se ve la muestra', async ({ page }) => {
  await page.goto('/colores');
  await page.getByRole('button', { name: 'Agregar color' }).click();
  const dialogo = page.getByRole('dialog');
  await dialogo.getByLabel('Nombre').fill('Verde Llanero');
  await dialogo.getByRole('textbox', { name: 'Color HEX' }).fill('#2e7d32');
  await dialogo.getByLabel('Grupo').fill('Verdes');
  await dialogo.getByRole('button', { name: 'Agregar color' }).click();

  await expect(page.getByText('Color creado correctamente.')).toBeVisible();
  // El catálogo sembrado tiene varias páginas (orden: grupo, nombre): se busca, como haría el usuario.
  await page.getByRole('searchbox', { name: 'Buscar color' }).fill('llanero');
  await expect(page).toHaveURL(/buscar=llanero/);
  const fila = page.getByRole('row', { name: /Verde Llanero/ });
  await expect(fila).toContainText('#2E7D32');
  await expect(fila).toContainText('Verdes');
});
