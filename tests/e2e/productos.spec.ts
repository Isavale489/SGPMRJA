import { test, expect, type Page } from '@playwright/test';

/**
 * Catálogo de productos (/productos = tipos de producto) migrado a Inertia.
 * Usa el atributo «Bolsillo» y la tela «Oxford E2E» del E2eSeeder.
 */
test.describe.configure({ mode: 'serial' });

const TIPO = 'Camisa Guayabera';

function vigilarErrores(page: Page): string[] {
  const errores: string[] = [];
  page.on('pageerror', (e) => errores.push(`JS: ${e.message}`));
  page.on('response', (r) => { if (r.status() >= 500) errores.push(`HTTP ${r.status()} ${r.url()}`); });
  return errores;
}

test('alta de un tipo con tela permitida y atributo', async ({ page }) => {
  const errores = vigilarErrores(page);
  await page.goto('/productos');
  await expect(page.getByRole('heading', { name: 'Catálogo de productos', exact: true })).toBeVisible();

  await page.getByRole('button', { name: 'Agregar tipo de producto' }).click();
  const d = page.getByRole('dialog');
  await d.getByLabel('Nombre').fill(TIPO);
  await d.getByLabel('Prefijo').fill('gy1'); // se filtra a letras y mayúsculas
  await expect(d.getByLabel('Prefijo')).toHaveValue('GY');
  await d.getByLabel('Precio de confección (USD)').fill('8.5');
  await d.getByLabel('Consumo de tela por unidad').fill('1.8');
  await d.getByRole('checkbox', { name: /Oxford E2E/ }).check();
  await d.getByRole('combobox', { name: 'Agregar atributo' }).click();
  await page.getByRole('option', { name: /Bolsillo/ }).click();
  await expect(d.getByText('1.', { exact: true })).toBeVisible();
  await d.getByRole('button', { name: 'Agregar tipo' }).click();

  await expect(page.getByText('Tipo de producto creado correctamente.')).toBeVisible();
  const fila = page.getByRole('row', { name: new RegExp(TIPO) });
  await expect(fila).toContainText('GY');
  await expect(fila).toContainText('1 tela');
  await expect(fila).toContainText('Bolsillo');
  await expect(fila).toContainText('$8,50');
  expect(errores, errores.join('\n')).toEqual([]);
});

test('un prefijo repetido se marca en el campo', async ({ page }) => {
  await page.goto('/productos');
  await page.getByRole('button', { name: 'Agregar tipo de producto' }).click();
  const d = page.getByRole('dialog');
  await d.getByLabel('Nombre').fill('Otra camisa');
  await d.getByLabel('Prefijo').fill('GY');
  await d.getByRole('button', { name: 'Agregar tipo' }).click();

  await expect(d.getByText('Ya existe un tipo con este prefijo.')).toBeVisible();
});

test('editar: el prefijo es de solo lectura y los cambios se guardan', async ({ page }) => {
  await page.goto('/productos?buscar=guayabera');
  await page.getByRole('button', { name: `Acciones para ${TIPO}` }).click();
  await page.getByRole('menuitem', { name: 'Editar' }).click();

  const d = page.getByRole('dialog');
  await expect(d.getByLabel('Prefijo')).toHaveAttribute('readonly', '');
  await expect(d.getByRole('checkbox', { name: /Oxford E2E/ })).toBeChecked();
  await d.getByLabel('Precio de confección (USD)').fill('9');
  await d.getByRole('button', { name: 'Guardar cambios' }).click();

  await expect(page.getByText('Tipo de producto actualizado correctamente.')).toBeVisible();
  await expect(page.getByRole('row', { name: new RegExp(TIPO) })).toContainText('$9,00');
  await expect(page.getByRole('row', { name: new RegExp(TIPO) })).toContainText('GY');
});

test('inhabilitar y restaurar un tipo', async ({ page }) => {
  await page.goto('/productos?buscar=guayabera');
  await page.getByRole('button', { name: `Acciones para ${TIPO}` }).click();
  await page.getByRole('menuitem', { name: 'Inhabilitar' }).click();
  await page.getByRole('alertdialog').getByRole('button', { name: 'Inhabilitar' }).click();
  await expect(page.getByText('Tipo de producto inhabilitado correctamente.')).toBeVisible();
  await expect(page.getByRole('menu')).toHaveCount(0);

  await page.getByRole('link', { name: 'Inhabilitados' }).click();
  await page.getByRole('button', { name: `Acciones para ${TIPO}` }).click();
  await page.getByRole('menuitem', { name: 'Restaurar' }).click();
  await expect(page.getByText('Tipo de producto restaurado correctamente.')).toBeVisible();
});

test('en móvil la página no se desborda', async ({ page }) => {
  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto('/productos');
  const desborde = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
  expect(desborde).toBeLessThanOrEqual(0);
});
