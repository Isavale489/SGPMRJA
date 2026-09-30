import { test, expect, type Page } from '@playwright/test';

/**
 * Piloto (paso 3): el módulo Proveedores migrado a Inertia + React, de punta a
 * punta en un navegador real. Crea sus propios datos (RIF únicos).
 */
test.describe.configure({ mode: 'serial' });

const RIF = '41987654';
const RAZON = 'Tejidos Guanare C.A.';

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

test('alta de un proveedor jurídico desde el diálogo', async ({ page }) => {
  const errores = vigilarErrores(page);
  await page.goto('/proveedores');
  await expect(page.getByRole('heading', { name: 'Proveedores', exact: true })).toBeVisible();

  await page.getByRole('button', { name: 'Agregar proveedor' }).click();
  const dialogo = page.getByRole('dialog');
  await dialogo.getByRole('textbox', { name: 'RIF' }).fill(RIF);
  await dialogo.getByLabel('Razón social').fill(RAZON);
  await dialogo.getByLabel('Correo electrónico').fill('ventas@tejidosguanare.com');
  await dialogo.getByLabel('Dirección').fill('Zona Industrial, Galpón 12');
  await dialogo.getByLabel('Número del teléfono 1').fill('5551234');
  await elegir(page, 'Estado', 'Portuguesa');
  await elegir(page, 'Municipio', 'Guanare');
  await dialogo.getByLabel('Persona de contacto').fill('Rosa Linares');
  await dialogo.getByRole('button', { name: 'Agregar proveedor' }).click();

  await expect(page.getByText('Proveedor creado exitosamente.')).toBeVisible();
  await expect(dialogo).toBeHidden();
  const fila = page.getByRole('row', { name: new RegExp(RAZON) });
  await expect(fila).toContainText(`J-${RIF}`);
  await expect(fila).toContainText('0424-5551234');
  expect(errores, errores.join('\n')).toEqual([]);
});

test('un RIF repetido se marca en el campo, sin error del servidor', async ({ page }) => {
  const errores = vigilarErrores(page);
  await page.goto('/proveedores');
  await page.getByRole('button', { name: 'Agregar proveedor' }).click();
  const dialogo = page.getByRole('dialog');

  await dialogo.getByRole('textbox', { name: 'RIF' }).fill(RIF);
  await dialogo.getByLabel('Razón social').fill('Otra Empresa');
  await dialogo.getByLabel('Correo electrónico').fill('otra@empresa.com');
  await dialogo.getByLabel('Dirección').fill('Calle 1, Guanare');
  await dialogo.getByLabel('Número del teléfono 1').fill('5559999');
  await dialogo.getByRole('button', { name: 'Agregar proveedor' }).click();

  await expect(dialogo.getByText('Este documento ya está registrado.')).toBeVisible();
  await expect(dialogo).toBeVisible();
  expect(errores, errores.join('\n')).toEqual([]);
});

test('la búsqueda filtra en el servidor y queda en la URL', async ({ page }) => {
  await page.goto('/proveedores');
  await page.getByRole('searchbox', { name: 'Buscar proveedor' }).fill('guanare');

  await expect(page).toHaveURL(/buscar=guanare/);
  await expect(page.getByRole('row', { name: new RegExp(RAZON) })).toBeVisible();

  // Recargar conserva el filtro (la URL es el estado de la vista).
  await page.reload();
  await expect(page.getByRole('searchbox', { name: 'Buscar proveedor' })).toHaveValue('guanare');

  await page.getByRole('searchbox', { name: 'Buscar proveedor' }).fill('zzz-no-existe');
  await expect(page.getByText('Ningún proveedor coincide con los filtros.')).toBeVisible();
});

test('editar conserva el documento y guarda los cambios', async ({ page }) => {
  await page.goto('/proveedores?buscar=guanare');
  await page.getByRole('button', { name: `Más acciones para ${RAZON}` }).click();
  await page.getByRole('menuitem', { name: 'Editar' }).click();

  const dialogo = page.getByRole('dialog');
  await expect(dialogo.getByRole('textbox', { name: 'RIF' })).toBeDisabled();
  await dialogo.getByLabel('Dirección').fill('Zona Industrial, Galpón 14');
  await dialogo.getByRole('button', { name: 'Guardar cambios' }).click();

  await expect(page.getByText('Proveedor actualizado exitosamente.')).toBeVisible();
  await page.getByRole('button', { name: `Ver ${RAZON}` }).click();
  await expect(page.getByRole('dialog')).toContainText('Zona Industrial, Galpón 14');
});

test('cerrar con cambios sin guardar pide confirmación', async ({ page }) => {
  await page.goto('/proveedores?buscar=guanare');
  await page.getByRole('button', { name: `Más acciones para ${RAZON}` }).click();
  await page.getByRole('menuitem', { name: 'Editar' }).click();
  const dialogo = page.getByRole('dialog');
  await dialogo.getByLabel('Dirección').fill('Cambio que no se guarda');

  // Diálogo del sistema, no el confirm del navegador.
  const nativos: string[] = [];
  page.on('dialog', (d) => { nativos.push(d.message()); void d.dismiss(); });
  const aviso = page.getByRole('alertdialog', { name: 'Cambios sin guardar' });

  // Primero «Seguir editando»: el diálogo sigue abierto con el cambio.
  await dialogo.getByRole('button', { name: 'Cancelar' }).click();
  await aviso.getByRole('button', { name: 'Seguir editando' }).click();
  await expect(aviso).toBeHidden();
  await expect(dialogo.getByLabel('Dirección')).toHaveValue('Cambio que no se guarda');

  // Escape en el aviso también es seguir editando.
  await dialogo.getByRole('button', { name: 'Cancelar' }).click();
  await expect(aviso).toBeVisible();
  await page.keyboard.press('Escape');
  await expect(aviso).toBeHidden();
  await expect(dialogo).toBeVisible();

  // Atrás del navegador con el diálogo abierto también pregunta y no lo cierra.
  await page.goBack();
  await aviso.getByRole('button', { name: 'Seguir editando' }).click();
  await expect(aviso).toBeHidden();
  await expect(dialogo.getByLabel('Dirección')).toHaveValue('Cambio que no se guarda');
  await expect(page).toHaveURL(/\/proveedores\?buscar=guanare/);

  // Luego «Descartar»: se cierra.
  await dialogo.getByRole('button', { name: 'Cancelar' }).click();
  await aviso.getByRole('button', { name: 'Descartar' }).click();
  await expect(dialogo).toBeHidden();
  expect(nativos).toEqual([]);
});

test('inhabilitar lo pasa al historial y se puede restaurar', async ({ page }) => {
  await page.goto('/proveedores?buscar=guanare');
  await page.getByRole('button', { name: `Más acciones para ${RAZON}` }).click();
  await page.getByRole('menuitem', { name: 'Inhabilitar' }).click();
  await page.getByRole('alertdialog').getByRole('button', { name: 'Inhabilitar' }).click();

  await expect(page.getByText('Proveedor inhabilitado exitosamente.')).toBeVisible();
  await expect(page.getByRole('menu')).toHaveCount(0); // el menú ⋮ se cierra al confirmar
  await expect(page.getByRole('row', { name: new RegExp(RAZON) })).toHaveCount(0);

  await page.getByRole('link', { name: 'Inhabilitados' }).click();
  await expect(page.getByRole('heading', { name: 'Proveedores inhabilitados' })).toBeVisible();
  await page.getByRole('button', { name: `Más acciones para ${RAZON}` }).click();
  await page.getByRole('menuitem', { name: 'Restaurar' }).click();

  await expect(page.getByText('Proveedor restaurado exitosamente.')).toBeVisible();
  await page.getByRole('link', { name: 'Solo activos' }).click();
  await expect(page.getByRole('row', { name: new RegExp(RAZON) })).toBeVisible();
});

test('en móvil la página no se desborda', async ({ page }) => {
  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto('/proveedores');
  const desborde = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
  expect(desborde).toBeLessThanOrEqual(0);
});
