import { test, expect, type Page } from '@playwright/test';

/**
 * Consultas y Reportes migrados a Inertia (AG Charts en React). Además de los
 * errores de JS, se vigilan los avisos de AG Charts: una opción inválida solo
 * avisa en la consola y el gráfico la ignora en silencio.
 */
function vigilar(page: Page): string[] {
  const problemas: string[] = [];
  page.on('pageerror', (e) => problemas.push(`JS: ${e.message}`));
  page.on('response', (r) => { if (r.status() >= 500) problemas.push(`HTTP ${r.status()} ${r.url()}`); });
  page.on('console', (m) => { if ((m.type() === 'warning' || m.type() === 'error') && /AG Charts/i.test(m.text())) problemas.push(`AG: ${m.text()}`); });
  return problemas;
}

test('el hub lleva a los reportes y cada uno dibuja sus gráficos sin avisos', async ({ page }) => {
  const problemas = vigilar(page);
  await page.goto('/reportes/general');
  await expect(page.getByText('Órdenes activas')).toBeVisible();
  await page.getByRole('link', { name: /Producción/ }).first().click();
  await expect(page.getByRole('heading', { name: 'Reporte de producción' })).toBeVisible();
  await expect(page.locator('canvas').first()).toBeVisible();

  for (const [url, titulo] of [['/reportes/eficiencia', 'Eficiencia de producción'], ['/reportes/insumos', 'Consumo de insumos'], ['/reportes/empleados', 'Rendimiento de empleados']] as const) {
    await page.goto(url);
    await expect(page.getByRole('heading', { name: titulo })).toBeVisible();
  }
  await expect(page.locator('canvas').first()).toBeVisible();
  // Los datos del seeder: la orden de calidad (Marta 6 / Julio 4).
  await expect(page.getByRole('row', { name: /Marta Colmenares/ })).toBeVisible();
  expect(problemas, problemas.join('\n')).toEqual([]);
});

test('descargar un gráfico como imagen', async ({ page }) => {
  await page.goto('/reportes/produccion');
  const descarga = page.waitForEvent('download');
  await page.getByRole('button', { name: 'Descargar «Órdenes por estado» como imagen' }).click();
  expect((await descarga).suggestedFilename()).toMatch(/ordenes-por-estado/);
});

test('si el chunk de AG Charts no llega, la página sigue en pie con un aviso', async ({ page }) => {
  await page.route(/lienzo-grafico-.*\.js/, (r) => r.abort());
  await page.goto('/reportes/produccion');
  await expect(page.getByRole('alert').filter({ hasText: 'No se pudo cargar el gráfico' }).first()).toBeVisible();
  await expect(page.getByText('Órdenes por estado', { exact: true }).first()).toBeVisible();
  await expect(page.getByRole('button', { name: 'Descargar «Órdenes por estado» como imagen' })).toBeDisabled();
});

test('el orden de las tarjetas se guarda', async ({ page }) => {
  await page.goto('/reportes/produccion');
  const primera = () => page.locator('[data-widget]').first();
  await expect(primera()).toHaveAttribute('data-widget', 'estados');
  await page.getByRole('button', { name: 'Bajar «Órdenes por estado»' }).focus();
  await page.keyboard.press('Enter');
  await expect(primera()).toHaveAttribute('data-widget', 'mensual');
  // El foco sigue a la tarjeta movida (su «Bajar» quedó deshabilitado: pasa a «Subir»).
  await expect(page.getByRole('button', { name: 'Subir «Órdenes por estado»' })).toBeFocused();
  await page.reload();
  await expect(primera()).toHaveAttribute('data-widget', 'mensual');
});

test('las tablas se ordenan por columna', async ({ page }) => {
  await page.goto('/reportes/empleados');
  const producido = page.getByRole('columnheader', { name: /Producido/ });
  await expect(producido).toHaveAttribute('aria-sort', 'none');
  await producido.getByRole('button').click();
  await expect(producido).toHaveAttribute('aria-sort', 'ascending');
  await producido.getByRole('button').click();
  await expect(producido).toHaveAttribute('aria-sort', 'descending');

  await page.goto('/reportes/eficiencia');
  await expect(page.getByRole('columnheader', { name: /Eficiencia/ })).toHaveAttribute('aria-sort', 'ascending');
});

test('eficiencia: el detalle del pedido muestra sus órdenes', async ({ page }) => {
  await page.goto('/reportes/eficiencia');
  await page.getByRole('button', { name: /Ver órdenes de Pedido #/ }).first().click();
  await expect(page.getByRole('dialog')).toContainText(/(Orden|Órdenes) de producción/);
});

test('el cambio de tema redibuja sin errores', async ({ page }) => {
  const problemas = vigilar(page);
  await page.goto('/reportes/empleados');
  await page.getByRole('button', { name: /tema|oscuro|claro/i }).first().click();
  await expect(page.locator('html')).toHaveClass(/dark/);
  await expect(page.locator('canvas').first()).toBeVisible();
  expect(problemas, problemas.join('\n')).toEqual([]);
});

test('en móvil las páginas no se desbordan', async ({ page }) => {
  await page.setViewportSize({ width: 390, height: 844 });
  for (const url of ['/reportes/general', '/reportes/produccion', '/reportes/eficiencia', '/reportes/insumos', '/reportes/empleados']) {
    await page.goto(url);
    const desborde = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
    expect(desborde, url).toBeLessThanOrEqual(0);
  }
});
