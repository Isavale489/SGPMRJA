import { test, expect, type Page } from '@playwright/test';

/**
 * Plataforma Inertia + React: la página del catálogo carga sin errores y las
 * piezas de fondo (portales, tema compartido, navegación) funcionan en un
 * navegador real.
 */
function vigilarErrores(page: Page): string[] {
  const errores: string[] = [];
  page.on('pageerror', (e) => errores.push(`JS: ${e.message}`));
  page.on('console', (m) => { if (m.type() === 'error') errores.push(`console: ${m.text()}`); });
  page.on('response', (r) => { if (r.status() >= 400) errores.push(`HTTP ${r.status()} ${r.url()}`); });
  return errores;
}

test('el catálogo de componentes carga sin errores y sin Bootstrap', async ({ page }) => {
  const errores = vigilarErrores(page);
  await page.goto('/plataforma/componentes');

  await expect(page.getByRole('heading', { name: 'Componentes de la plataforma' })).toBeVisible();
  await expect(page.locator('[data-bcv-pill]')).toContainText('Tasa BCV (');
  await expect(page.getByRole('navigation', { name: 'Principal' })).toContainText('Gestión Operativa');
  expect(await page.locator('link[href*="bootstrap"]').count()).toBe(0);
  expect(errores, errores.join('\n')).toEqual([]);
});

test('un select dentro de un diálogo abre completo, sin recortarse', async ({ page }) => {
  await page.goto('/plataforma/componentes');
  await page.getByRole('button', { name: 'Abrir diálogo' }).click();
  const dialogo = page.getByRole('dialog');
  await expect(dialogo).toBeVisible();

  await dialogo.getByRole('combobox').click();
  const opcion = page.getByRole('option', { name: 'BNC' }); // la última de la lista
  await expect(opcion).toBeVisible();
  // El menú vive en un portal fuera del diálogo: la última opción es clicable.
  await opcion.click();
  await expect(dialogo.getByRole('combobox')).toContainText('BNC');
});

test('el tema se guarda con la misma clave que las páginas Blade', async ({ page }) => {
  await page.goto('/plataforma/componentes');
  const html = page.locator('html');
  const oscuroAntes = await html.evaluate((e) => e.classList.contains('dark'));

  await page.getByRole('button', { name: /Usar tema (claro|oscuro)/ }).click();

  await expect(html).toHaveClass(oscuroAntes ? /^(?!.*dark)/ : /dark/);
  const guardado = await page.evaluate(() => localStorage.getItem('sgpmrja-theme'));
  expect(guardado).toBe(oscuroAntes ? 'light' : 'dark');

  // Persiste al recargar (lo aplica la plantilla antes del primer pintado).
  await page.reload();
  await expect(html).toHaveClass(oscuroAntes ? /^(?!.*dark)/ : /dark/);
});

test('un enlace del menú a un módulo Blade navega con recarga completa', async ({ page }) => {
  const errores = vigilarErrores(page);
  await page.goto('/plataforma/componentes');
  await page.getByRole('button', { name: 'Gestión Operativa' }).click();
  await page.getByRole('link', { name: 'Pedidos' }).click();

  await expect(page).toHaveURL(/\/pedidos$/);
  await expect(page.locator('table.dataTable')).toBeVisible(); // página Blade real
  expect(errores, errores.join('\n')).toEqual([]);
});

test('en móvil no hay desborde horizontal y el menú abre en un panel', async ({ page }) => {
  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto('/plataforma/componentes');

  const desborde = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
  expect(desborde, 'la página es más ancha que la pantalla').toBeLessThanOrEqual(0);

  await page.getByRole('button', { name: 'Abrir menú' }).click();
  await expect(page.getByRole('dialog').getByRole('navigation', { name: 'Principal' })).toBeVisible();
});
