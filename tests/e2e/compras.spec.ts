import { test, expect, type Page } from '@playwright/test';

/**
 * Compras migradas a Inertia. Usa «Hilos Compra E2E C.A.» (J-41000555) y
 * «Hilo Compra E2E» (20 conos, costo $2 → Bs 80 con la tasa 40) del E2eSeeder.
 */
test.describe.configure({ mode: 'serial' });

const PROVEEDOR = 'Hilos Compra E2E C.A.';
const INSUMO = 'Hilo Compra E2E';

function vigilarErrores(page: Page): string[] {
  const errores: string[] = [];
  page.on('pageerror', (e) => errores.push(`JS: ${e.message}`));
  page.on('response', (r) => { if (r.status() >= 500) errores.push(`HTTP ${r.status()} ${r.url()}`); });
  return errores;
}

async function elegirProveedor(page: Page) {
  await page.getByRole('combobox', { name: 'Buscar proveedor' }).fill('Hilos Compra');
  await page.getByRole('option', { name: new RegExp(PROVEEDOR) }).click();
  await expect(page.getByText('J-41000555')).toBeVisible();
}

test('salir con cambios sin guardar pregunta con el diálogo del sistema, no con el del navegador', async ({ page }) => {
  const nativos: string[] = [];
  page.on('dialog', (d) => { nativos.push(d.message()); void d.dismiss(); });
  await page.goto('/compras/crear');
  await elegirProveedor(page);
  const aviso = page.getByRole('alertdialog', { name: 'Cambios sin guardar' });

  // «Seguir editando»: no se sale y el proveedor sigue elegido.
  await page.getByRole('main').getByRole('link', { name: 'Compras', exact: true }).click();
  await aviso.getByRole('button', { name: 'Seguir editando' }).click();
  await expect(aviso).toBeHidden();
  await expect(page).toHaveURL(/\/compras\/crear$/);
  await expect(page.getByText('J-41000555')).toBeVisible();

  // «Descartar»: se va a donde se pidió, sin volver a preguntar.
  await page.getByRole('main').getByRole('link', { name: 'Compras', exact: true }).click();
  await aviso.getByRole('button', { name: 'Descartar' }).click();
  await expect(page).toHaveURL(/\/compras$/);
  await expect(aviso).toBeHidden();
  expect(nativos).toEqual([]);
});

test('registrar un borrador: tasa del día, costo sugerido e IVA en vivo', async ({ page }) => {
  const errores = vigilarErrores(page);
  await page.goto('/compras');
  await page.getByRole('link', { name: 'Nueva compra' }).click();
  await expect(page.getByRole('heading', { name: 'Nueva compra' })).toBeVisible();

  await expect(page.getByLabel('Tasa (Bs por $)')).toHaveValue('40');
  // Asistente: no se avanza sin proveedor.
  await page.getByRole('button', { name: 'Siguiente' }).click();
  await expect(page.getByText('Elige el proveedor.')).toBeVisible();
  await elegirProveedor(page);
  await page.getByLabel('N° de factura').fill('77-001');
  await page.getByRole('button', { name: 'Siguiente' }).click();
  await page.getByRole('button', { name: 'Siguiente' }).click();
  await expect(page.getByText('Agrega al menos un insumo.')).toBeVisible();

  await page.getByRole('combobox', { name: 'Agregar insumo' }).fill('Hilo Compra');
  await page.getByRole('option', { name: new RegExp(INSUMO) }).click();
  await expect(page.getByLabel(`Cantidad de ${INSUMO}`)).toBeFocused();
  await page.getByLabel(`Cantidad de ${INSUMO}`).fill('10');
  await expect(page.getByLabel(`Costo unitario en bolívares de ${INSUMO}`)).toHaveValue('80');

  // 10 × Bs 80 = 800 + IVA 16 % = 928; exento: 800.
  const total = () => page.locator('form').getByText('Total', { exact: true }).locator('..');
  await page.getByRole('switch', { name: `${INSUMO} paga IVA` }).click();
  await page.getByRole('button', { name: 'Siguiente' }).click();
  await expect(page.getByRole('heading', { name: 'Resumen' })).toBeVisible();
  await expect(total()).toContainText('Bs 800,00');
  await page.getByRole('button', { name: 'Anterior' }).click();
  await page.getByRole('switch', { name: `${INSUMO} paga IVA` }).click();
  await page.getByRole('button', { name: 'Siguiente' }).click();
  await expect(total()).toContainText('Bs 928,00');

  await page.getByRole('button', { name: 'Guardar borrador' }).click();
  await expect(page.getByText(/Borrador de compra #\d+ guardado/)).toBeVisible();
  await expect(page).toHaveURL(/\/compras$/);
  const fila = page.getByRole('row', { name: new RegExp(PROVEEDOR) });
  await expect(fila).toContainText('77-001');
  await expect(fila).toContainText('borrador');
  await expect(fila).toContainText('Bs 928,00');
  expect(errores, errores.join('\n')).toEqual([]);
});

test('procesar suma la existencia; anular la revierte; clonar la deja como borrador', async ({ page }) => {
  const errores = vigilarErrores(page);
  await page.goto('/compras');
  const fila = page.getByRole('row', { name: new RegExp(PROVEEDOR) });
  await fila.getByRole('button', { name: /Ver compra/ }).click();

  const detalle = page.getByRole('dialog');
  await expect(detalle).toContainText('Tasa BCV');
  await expect(detalle).toContainText('Bs 928,00');
  await detalle.getByRole('button', { name: 'Procesar' }).click();
  await page.getByRole('alertdialog').getByRole('button', { name: 'Procesar' }).click();
  await expect(page.getByText(/procesada\. Stock de insumos actualizado/)).toBeVisible();
  await expect(page.getByRole('dialog')).toContainText('recibida');
  await page.keyboard.press('Escape');

  await page.getByRole('tab', { name: 'Existencias' }).click();
  await expect(page.getByRole('row', { name: new RegExp(INSUMO) })).toContainText('30'); // 20 + 10

  await page.getByRole('tab', { name: 'Compras' }).click();
  await fila.getByRole('button', { name: /Más acciones/ }).click();
  await page.getByRole('menuitem', { name: 'Anular' }).click();
  await page.getByRole('alertdialog').getByRole('button', { name: 'Anular' }).click();
  await expect(page.getByText(/anulada\. El stock ha sido revertido/)).toBeVisible();
  await expect(page.getByRole('menu')).toHaveCount(0);

  await page.getByRole('tab', { name: 'Anuladas' }).click();
  const anulada = page.getByRole('row', { name: new RegExp(PROVEEDOR) });
  await expect(anulada).toContainText('por Admin E2E');
  await anulada.getByRole('button', { name: /Más acciones/ }).click();
  await page.getByRole('menuitem', { name: 'Clonar como borrador' }).click();
  await expect(page.getByText(/Compra clonada como borrador #\d+/)).toBeVisible();
  expect(errores, errores.join('\n')).toEqual([]);
});

test('editar el borrador clonado y crear un insumo sin salir de la compra', async ({ page }) => {
  await page.goto('/compras');
  const fila = page.getByRole('row', { name: new RegExp(PROVEEDOR) });
  await expect(fila).toContainText('borrador');
  await fila.getByRole('button', { name: /Más acciones/ }).click();
  await page.getByRole('menuitem', { name: 'Editar' }).click();
  await expect(page.getByRole('heading', { name: /Editar borrador #\d+/ })).toBeVisible();
  await page.getByRole('button', { name: 'Siguiente' }).click();
  await expect(page.getByLabel(`Cantidad de ${INSUMO}`)).toHaveValue('10');

  await page.getByRole('combobox', { name: 'Agregar insumo' }).fill('Cinta raso E2E');
  await page.getByRole('button', { name: 'Crear el insumo «Cinta raso E2E»' }).click();
  const alta = page.getByRole('dialog', { name: 'Agregar insumo' });
  await expect(alta.getByLabel('Nombre')).toHaveValue('Cinta raso E2E');
  // Desde una compra: inventariable por fuerza y sin existencia inicial (entra con la compra).
  await expect(alta.getByLabel('Existencia actual')).toHaveCount(0);
  await expect(alta.getByRole('switch', { name: 'Inventariable' })).toHaveCount(0);
  await alta.getByRole('combobox', { name: 'Tipo' }).click();
  await page.getByRole('option', { name: 'Etiqueta', exact: true }).click();
  await alta.getByRole('combobox', { name: 'Unidad de medida' }).click();
  await page.getByRole('option', { name: 'Metro', exact: true }).click();
  await alta.getByLabel('Costo unitario ($)').fill('0.5');
  await alta.getByRole('button', { name: 'Agregar insumo' }).click();

  // El insumo nuevo entra como línea, sin perder lo que ya estaba cargado.
  await expect(page.getByLabel('Cantidad de Cinta raso E2E')).toBeFocused();
  await expect(page.getByLabel(`Cantidad de ${INSUMO}`)).toHaveValue('10');
  await expect(page.getByLabel('Costo unitario en bolívares de Cinta raso E2E')).toHaveValue('20');
  await page.getByLabel('Cantidad de Cinta raso E2E').fill('4');
  await page.getByRole('button', { name: 'Siguiente' }).click();
  await page.getByRole('button', { name: 'Guardar cambios' }).click();
  await expect(page.getByText(/actualizada correctamente/)).toBeVisible();
});

test('una compra prellenada con faltantes (desde Cotizaciones/Pedidos)', async ({ page }) => {
  // Lo que deja «Comprar lo que falta» (lib/inventario.ts) al pulsarlo en Cotizaciones/Pedidos/Órdenes.
  await page.goto('/compras/crear');
  const insumoId = await page.evaluate((nombre) => {
    const props = (window.history.state?.page?.props ?? {}) as { insumos?: { id: number; nombre: string }[] };
    return props.insumos?.find((i) => i.nombre === nombre)?.id;
  }, INSUMO);
  expect(insumoId).toBeTruthy();
  await page.evaluate((id) => {
    localStorage.setItem('sgpmrja_compra_prefill', JSON.stringify({ origen: 'pedido', ts: Date.now(), insumos: [{ insumo_id: id, nombre: 'Hilo Compra E2E', cantidad: 7 }, { insumo_id: 999999, nombre: 'Insumo retirado', cantidad: 2 }] }));
  }, insumoId);

  await page.goto('/compras?prefill=1'); // entrada vieja: redirige al formulario
  await expect(page).toHaveURL(/\/compras\/crear\?prefill=1$/);
  await expect(page.getByText('Se cargaron 1 insumo faltante')).toBeVisible();
  await expect(page.getByText(/No se agregaron .*Insumo retirado/)).toBeVisible();
  await elegirProveedor(page);
  await page.getByRole('button', { name: 'Siguiente' }).click();
  await expect(page.getByLabel(`Cantidad de ${INSUMO}`)).toHaveValue('7');
  await expect(page.getByLabel(`Costo unitario en bolívares de ${INSUMO}`)).toHaveValue('80');

  // Una tasa escrita a mano (tecla por tecla) no congela el costo sugerido: sigue a la tasa final.
  await page.getByRole('button', { name: 'Anterior' }).click();
  await page.getByLabel('Tasa (Bs por $)').fill('');
  await page.getByLabel('Tasa (Bs por $)').pressSequentially('45');
  await expect(page.getByText('Tasa escrita a mano (sin fecha BCV)')).toBeVisible();
  await page.getByRole('button', { name: 'Siguiente' }).click();
  await expect(page.getByLabel(`Costo unitario en bolívares de ${INSUMO}`)).toHaveValue('90');

  // Recargar no vuelve a aplicar los faltantes.
  await page.reload();
  await elegirProveedor(page);
  await page.getByRole('button', { name: 'Siguiente' }).click();
  await expect(page.getByText('Agrega los insumos de la factura con su cantidad y costo.')).toBeVisible();
});

test('una ficha que no existe se cierra con aviso', async ({ page }) => {
  await page.goto('/compras?ver=999999');
  await expect(page.getByText('La compra #999999 no existe o fue eliminada.')).toBeVisible();
  await expect(page.getByRole('dialog')).toHaveCount(0);
  await expect(page).toHaveURL(/\/compras$/);
});

test('en móvil las páginas no se desbordan', async ({ page }) => {
  await page.setViewportSize({ width: 390, height: 844 });
  for (const url of ['/compras', '/compras/crear']) {
    await page.goto(url);
    const desborde = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
    expect(desborde, url).toBeLessThanOrEqual(0);
  }
});
