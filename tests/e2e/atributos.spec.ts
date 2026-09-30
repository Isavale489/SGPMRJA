import { test, expect, type Page } from '@playwright/test';

/** Atributos de confección (maestro-detalle) migrado a Inertia. */
test.describe.configure({ mode: 'serial' });

function vigilarErrores(page: Page): string[] {
  const errores: string[] = [];
  page.on('pageerror', (e) => errores.push(`JS: ${e.message}`));
  page.on('response', (r) => { if (r.status() >= 500) errores.push(`HTTP ${r.status()} ${r.url()}`); });
  return errores;
}

const valores = (page: Page) => page.getByRole('region', { name: /Valores de Manga/ }).getByRole('row');

test('crear un atributo con su tipo de producto: el código se escribe en mayúsculas', async ({ page }) => {
  const errores = vigilarErrores(page);
  await page.goto('/atributos');
  await expect(page.getByText('Selecciona un atributo para ver y ordenar sus valores.')).toBeVisible();

  await page.getByRole('button', { name: 'Agregar atributo' }).click();
  const dialogo = page.getByRole('dialog');
  await dialogo.getByLabel('Nombre').fill('Manga');
  await dialogo.getByLabel('Código').fill('mng');
  await expect(dialogo.getByLabel('Código')).toHaveValue('MNG');
  await dialogo.getByRole('button', { name: 'Chemise' }).click();
  await expect(dialogo.getByRole('button', { name: 'Chemise' })).toHaveAttribute('aria-pressed', 'true');
  await dialogo.getByRole('button', { name: 'Agregar atributo' }).click();

  await expect(page.getByText('Atributo creado correctamente.')).toBeVisible();
  // El nuevo queda seleccionado (en la URL) y su panel de valores abierto.
  await expect(page).toHaveURL(/atributo=\d+/);
  await expect(page.getByRole('heading', { name: 'Valores de Manga' })).toBeVisible();
  await expect(page.getByRole('row', { name: /Manga/ })).toContainText('MNG');
  expect(errores, errores.join('\n')).toEqual([]);
});

test('agregar valores, rechazar un código repetido y reordenar con las flechas', async ({ page }) => {
  const errores = vigilarErrores(page);
  await page.goto('/atributos');
  await page.getByRole('button', { name: 'Ver valores de Manga' }).click();
  await expect(page.getByRole('heading', { name: 'Valores de Manga' })).toBeVisible();

  for (const [nombre, codigo] of [['Larga', 'L'], ['Corta', 'C']]) {
    await page.getByRole('button', { name: 'Agregar valor' }).click();
    const dialogo = page.getByRole('dialog');
    await dialogo.getByLabel('Nombre').fill(nombre);
    await dialogo.getByLabel('Código').fill(codigo);
    await dialogo.getByRole('button', { name: 'Agregar valor' }).click();
    await expect(page.getByText('Valor agregado correctamente.').last()).toBeVisible();
    await expect(dialogo).toBeHidden();
  }

  await page.getByRole('button', { name: 'Agregar valor' }).click();
  const dialogo = page.getByRole('dialog');
  await dialogo.getByLabel('Nombre').fill('Larguísima');
  await dialogo.getByLabel('Código').fill('l');
  await dialogo.getByRole('button', { name: 'Agregar valor' }).click();
  await expect(dialogo.getByText('Ya existe un valor con este código en el atributo.')).toBeVisible();
  await dialogo.getByRole('button', { name: 'Cancelar' }).click();
  await page.getByRole('alertdialog', { name: 'Cambios sin guardar' }).getByRole('button', { name: 'Descartar' }).click();
  await expect(dialogo).toBeHidden();

  await expect(valores(page).nth(1)).toContainText('Larga');
  await page.getByRole('button', { name: 'Subir Corta' }).click();
  await expect(valores(page).nth(1)).toContainText('Corta');
  await expect(valores(page).nth(2)).toContainText('Larga');
  await expect(page.getByRole('button', { name: 'Subir Corta' })).toBeDisabled();

  await page.reload(); // el orden quedó guardado
  await expect(valores(page).nth(1)).toContainText('Corta');
  expect(errores, errores.join('\n')).toEqual([]);
});

test('al editar, el código del atributo no se puede cambiar', async ({ page }) => {
  await page.goto('/atributos');
  await page.getByRole('button', { name: 'Acciones para Manga' }).click();
  await page.getByRole('menuitem', { name: 'Editar' }).click();
  const dialogo = page.getByRole('dialog');
  await expect(dialogo.getByLabel('Código')).toHaveAttribute('readonly', '');
  await dialogo.getByLabel('Nombre').fill('Tipo de manga');
  await dialogo.getByRole('button', { name: 'Guardar cambios' }).click();
  await expect(page.getByText('Atributo actualizado correctamente.')).toBeVisible();
  await expect(page.getByRole('row', { name: /Tipo de manga/ })).toContainText('MNG');
});

test('un atributo asignado a un tipo de producto no se elimina: se avisa', async ({ page }) => {
  await page.goto('/atributos');
  await page.getByRole('button', { name: 'Acciones para Tipo de manga' }).click();
  await page.getByRole('menuitem', { name: 'Eliminar' }).click();
  await page.getByRole('alertdialog').getByRole('button', { name: 'Eliminar' }).click();

  await expect(page.getByText('No se puede eliminar: el atributo está asignado a uno o más tipos de producto.')).toBeVisible();
  await expect(page.getByRole('menu')).toHaveCount(0);
  await expect(page.getByRole('row', { name: /Tipo de manga/ })).toBeVisible();
});

test('en móvil se ve un panel a la vez y la página no se desborda', async ({ page }) => {
  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto('/atributos');
  await page.getByRole('button', { name: 'Ver valores de Tipo de manga' }).click();
  await expect(page.getByRole('heading', { name: 'Valores de Tipo de manga' })).toBeVisible();
  await expect(page.getByRole('heading', { name: /^Atributos \(/ })).toBeHidden();
  const desborde = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
  expect(desborde).toBeLessThanOrEqual(0);

  await page.getByRole('button', { name: 'Atributos', exact: true }).click();
  await expect(page.getByRole('heading', { name: /^Atributos \(/ })).toBeVisible();
});
