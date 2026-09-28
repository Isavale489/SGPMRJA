import { test, expect, type Page } from '@playwright/test';

/**
 * Órdenes de Producción migradas a Inertia. Usa el pedido de «Taller Guanare OP»
 * (1 línea de 12, abono completo), Rosa Pineda y Pedro Unda (Producción) y el
 * «Hilo OP E2E» (30 conos, 0,5 por unidad) del E2eSeeder.
 */
test.describe.configure({ mode: 'serial' });

function vigilarErrores(page: Page): string[] {
  const errores: string[] = [];
  page.on('pageerror', (e) => errores.push(`JS: ${e.message}`));
  page.on('response', (r) => { if (r.status() >= 500) errores.push(`HTTP ${r.status()} ${r.url()}`); });
  return errores;
}

const tarjeta = (page: Page, titulo: string) => page.locator('[data-slot="card"]').filter({ hasText: titulo });

async function accion(page: Page, orden: number, nombre: string) {
  await page.getByRole('button', { name: `Más acciones de la orden #${orden}` }).click();
  await page.getByRole('menuitem', { name: nombre }).click();
}

let primera = 0;

test('crear dos órdenes de una línea: reparto, insumos por unidad y existencia', async ({ page }) => {
  const errores = vigilarErrores(page);
  await page.goto('/ordenes');
  await page.getByRole('link', { name: 'Nueva orden' }).click();
  await page.getByRole('button', { name: /Taller Guanare OP/ }).click();

  // La línea entra entera en una orden, con el insumo escalado (0,5 × 12).
  const orden1 = tarjeta(page, 'Orden 1:');
  await expect(orden1.getByLabel('Unidades')).toHaveValue('12');
  await expect(orden1.getByLabel(/Cantidad de Hilo OP E2E/)).toHaveValue('6');

  // Dividir: la segunda orden se lleva la mitad y el insumo se recalcula en ambas.
  await orden1.getByRole('button', { name: 'Dividir' }).click();
  const orden2 = tarjeta(page, 'Orden 2:');
  await expect(orden1.getByLabel('Unidades')).toHaveValue('6');
  await expect(orden2.getByLabel('Unidades')).toHaveValue('6');
  await expect(orden2.getByLabel(/Cantidad de Hilo OP E2E/)).toHaveValue('3');

  await orden1.getByRole('checkbox', { name: 'Rosa Pineda' }).click();
  await orden2.getByRole('checkbox', { name: 'Pedro Unda' }).click();
  await expect(page.getByText(/Necesita 6 Cono · hay 30/)).toBeVisible();

  await page.getByRole('button', { name: 'Crear 2 órdenes' }).last().click();
  await expect(page.getByText('2 órdenes creadas correctamente.')).toBeVisible();
  const dialogo = page.getByRole('dialog');
  await expect(dialogo).toContainText('2 órdenes de producción');
  primera = Number((await dialogo.getByRole('button', { name: /^Ver orden #/ }).last().getAttribute('aria-label'))?.replace(/\D/g, ''));
  expect(primera).toBeGreaterThan(0);
  expect(errores, errores.join('\n')).toEqual([]);
});

test('avance, etapas, edición y cancelación con merma', async ({ page }) => {
  const errores = vigilarErrores(page);
  const segunda = primera + 1;
  await page.goto('/ordenes');
  await page.getByRole('row', { name: /Taller Guanare OP/ }).getByRole('button', { name: /Ver órdenes/ }).click();

  await accion(page, primera, 'Registrar avance');
  const avance = page.getByRole('dialog', { name: 'Registrar avance' });
  await expect(avance.getByLabel('Unidades producidas')).toHaveValue('6');
  await avance.getByLabel('Unidades producidas').fill('4');
  await avance.getByRole('button', { name: 'Registrar avance' }).click();
  await expect(page.getByText('Avance registrado correctamente.')).toBeVisible();

  await accion(page, segunda, 'Etapas');
  const etapas = page.getByRole('dialog', { name: /Etapas de la orden/ });
  await etapas.getByRole('button', { name: 'Agregar etapa' }).click();
  await etapas.getByRole('textbox', { name: /^Etapa/ }).fill('Corte');
  await etapas.getByRole('button', { name: 'Crear etapa' }).click();
  await expect(page.getByText('Sub-orden creada y empleados asignados.')).toBeVisible();
  await etapas.getByRole('combobox', { name: 'Estado de Corte' }).click();
  await page.getByRole('option', { name: 'En Proceso' }).click();
  await expect(page.getByText('Estado de la sub-orden actualizado.')).toBeVisible();
  await page.keyboard.press('Escape');

  await accion(page, segunda, 'Editar');
  await expect(page.getByRole('heading', { name: `Editar orden #${segunda}` })).toBeVisible();
  await page.getByLabel('Notas').fill('Costura reforzada');
  await page.getByRole('button', { name: 'Guardar cambios' }).first().click();
  await expect(page.getByText('Orden de producción actualizada exitosamente.')).toBeVisible();

  // En proceso: el material ya se cortó, la cancelación exige motivo.
  await accion(page, segunda, 'Cancelar orden');
  const cancelar = page.getByRole('dialog', { name: `¿Cancelar la orden #${segunda}?` });
  await cancelar.getByRole('button', { name: 'Cancelar orden' }).click();
  await expect(cancelar.getByText(/indica el motivo/)).toBeVisible();
  await cancelar.getByLabel('Motivo').fill('Cliente cambió la tela');
  await cancelar.getByRole('button', { name: 'Cancelar orden' }).click();
  await expect(page.getByText('Orden cancelada. El material se registró como merma (sin reposición de stock).')).toBeVisible();
  expect(errores, errores.join('\n')).toEqual([]);
});

test('órdenes por empleado muestran su parte del reparto', async ({ page }) => {
  await page.goto('/ordenes');
  await page.getByRole('button', { name: 'Órdenes por empleado' }).click();
  await page.getByRole('dialog').getByRole('combobox', { name: 'Empleado' }).click();
  await page.getByRole('option', { name: 'Rosa Pineda' }).click();
  await expect(page.getByRole('dialog')).toContainText('Su parte: 4 de 6');
});

test('en móvil las páginas no se desbordan', async ({ page }) => {
  await page.setViewportSize({ width: 390, height: 844 });
  for (const url of ['/ordenes', '/ordenes/crear']) {
    await page.goto(url);
    const desborde = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
    expect(desborde, url).toBeLessThanOrEqual(0);
  }
});
