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

test('un enlace del menú navega sin recargar la página (Inertia)', async ({ page }) => {
  const errores = vigilarErrores(page);
  await page.goto('/plataforma/componentes');
  await page.evaluate(() => { (window as unknown as { __sinRecarga: boolean }).__sinRecarga = true; });
  await page.getByRole('button', { name: 'Gestión Operativa' }).click();
  await page.getByRole('link', { name: 'Pedidos' }).click();

  await expect(page).toHaveURL(/\/pedidos$/);
  await expect(page.getByRole('heading', { name: 'Pedidos' })).toBeVisible();
  expect(await page.evaluate(() => (window as unknown as { __sinRecarga?: boolean }).__sinRecarga)).toBe(true);
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

/** Tras quitar las librerías sin uso de public/assets: las vistas Blade que quedan no piden archivos borrados. */
test('las páginas Blade que quedan cargan todos sus archivos', async ({ page, browser }) => {
  const faltantes: string[] = [];
  const vigilar = (p: typeof page) => p.on('response', (r) => { if (r.status() >= 400 && /\/assets\//.test(r.url())) faltantes.push(`${r.status()} ${r.url()}`); });
  vigilar(page);
  await page.goto('/esta-pagina-no-existe', { waitUntil: 'networkidle' });
  await expect(page.getByText(/404|no existe|no encontrada/i).first()).toBeVisible();

  // Login, sin sesión.
  const anonimo = await browser.newContext({ storageState: { cookies: [], origins: [] } });
  const login = await anonimo.newPage();
  vigilar(login);
  await login.goto('/login', { waitUntil: 'networkidle' });
  await expect(login.getByRole('button', { name: /iniciar sesión|entrar|ingresar/i })).toBeVisible();
  await anonimo.close();

  expect(faltantes, faltantes.join('\n')).toEqual([]);
});

/** Lo que tenía el layout anterior: nombre de la empresa, reloj, notificaciones y pie. */
test('el header trae notificaciones y reloj, y el pie de página', async ({ page }) => {
  const errores = vigilarErrores(page);
  await page.setViewportSize({ width: 1366, height: 800 }); // laptop común: el nombre de la empresa se ve desde xl
  await page.goto('/dashboard');

  const header = page.locator('header').first();
  await expect(header).toContainText('Manufacturas R.J. Atlántico');
  await expect(header.locator('time')).toContainText(/\d{2}\/\d{2}\/\d{4}/);
  await expect(page.locator('footer')).toContainText('Grupo Textil 636 Informática');

  // Hilo Mov E2E está bajo su mínimo (E2eSeeder).
  const campana = page.getByRole('button', { name: /^Notificaciones/ });
  await expect(campana).toHaveAccessibleName(/sin revisar/);
  await page.screenshot({ path: 'test-results/layout-header.png' });
  await campana.click();
  const menu = page.getByRole('menu');
  await expect(menu).toContainText('Hilo Mov E2E');
  await page.screenshot({ path: 'test-results/layout-notificaciones.png' });

  // Ocultar vale para la sesión y se puede deshacer; los dos son ítems del menú (se llega con las flechas).
  await menu.getByRole('menuitem', { name: /Ocultar en esta sesión: .*Hilo Mov E2E/ }).click();
  await expect(menu).not.toContainText('Hilo Mov E2E');
  const restaurar = menu.getByRole('menuitem', { name: 'Mostrar las notificaciones ocultas' });
  await restaurar.focus();
  await page.keyboard.press('Enter');
  await expect(menu).toContainText('Hilo Mov E2E');

  await menu.getByRole('menuitem', { name: /Ver todas las alertas/ }).click();
  await expect(page).toHaveURL(/\/movimiento-insumo\/alertas/);
  expect(errores, errores.join('\n')).toEqual([]);
});
