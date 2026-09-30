import { test, expect, type Page } from '@playwright/test';

/**
 * Identidad por sección (config/secciones.php → <html data-seccion> → tokens de
 * plataforma.css). Se verifica el color REAL que pinta el navegador, no la clase:
 * un token mal escrito deja la clase puesta y el color de marca por defecto.
 * Con expect.poll: los enlaces tienen transición de color y se lee el valor final.
 */
const NAVY = 'rgb(30, 60, 114)'; // #1e3c72
const ESMERALDA = 'rgb(4, 120, 87)'; // #047857
const ESMERALDA_FONDO = 'rgb(6, 78, 59)'; // #064e3b
const SKY_FONDO = 'rgb(12, 74, 110)'; // #0c4a6e
const ESMERALDA_OSCURO = 'rgb(52, 211, 153)'; // #34d399

const seccion = (page: Page) => page.evaluate(() => document.documentElement.dataset.seccion ?? null);
const fondo = (page: Page, selector: string) => page.locator(selector).first().evaluate((el) => getComputedStyle(el).backgroundColor);

test('cada página toma el color de su sección, también al navegar sin recargar', async ({ page }) => {
  await page.goto('/clientes');
  await expect.poll(() => seccion(page)).toBe('maestros');
  await expect(page.getByRole('main').getByText('Gestión General', { exact: true })).toBeVisible();
  await expect.poll(() => fondo(page, 'main thead')).toBe(NAVY);

  // Navegación Inertia (sin recarga) a una página de otra sección.
  await page.getByRole('navigation', { name: 'Principal' }).getByRole('button', { name: 'Gestión Operativa' }).click();
  await page.getByRole('link', { name: 'Pedidos' }).click();
  await expect(page).toHaveURL(/\/pedidos$/);
  await expect.poll(() => seccion(page)).toBe('operativa');
  await expect.poll(() => fondo(page, 'main thead')).toBe(ESMERALDA_FONDO);
  // El botón principal sigue a la sección (--primary).
  await expect.poll(() => page.getByRole('link', { name: 'Nuevo pedido' }).evaluate((el) => getComputedStyle(el).backgroundColor)).toBe(ESMERALDA);

  await page.goto('/reportes/produccion');
  await expect.poll(() => seccion(page)).toBe('reportes');
  await expect.poll(() => fondo(page, 'main thead')).toBe(SKY_FONDO);

  // El inicio no es de ninguna sección: identidad de marca.
  await page.getByRole('link', { name: 'Inicio' }).click();
  await expect(page).toHaveURL(/\/dashboard$/);
  await expect.poll(() => seccion(page)).toBeNull();
});

test('el menú pinta cada sección con su color, sea cual sea la página', async ({ page }) => {
  await page.goto('/pedidos');
  const menu = page.getByRole('navigation', { name: 'Principal' });
  await expect(menu.getByRole('link', { name: 'Pedidos' })).toHaveAttribute('aria-current', 'page');
  await expect.poll(() => menu.getByRole('link', { name: 'Pedidos' }).evaluate((el) => getComputedStyle(el).color)).toBe(ESMERALDA);
  // Gestión General sigue en navy aunque la página sea de Operativa.
  await expect.poll(() => menu.getByRole('button', { name: 'Gestión General' }).locator('svg').first().evaluate((el) => getComputedStyle(el).color)).toBe(NAVY);
});

test('los diálogos llevan el encabezado en degradado de la sección', async ({ page }) => {
  await page.goto('/proveedores');
  await page.getByRole('button', { name: /^Más acciones para/ }).first().click();
  await page.getByRole('menuitem', { name: 'Editar' }).click();
  const encabezado = page.getByRole('dialog').locator('[data-slot="dialog-header"]');
  await expect.poll(() => encabezado.evaluate((el) => getComputedStyle(el).backgroundImage)).toContain('linear-gradient');
  await expect.poll(() => page.getByRole('dialog').getByRole('heading').evaluate((el) => getComputedStyle(el).color)).toBe('rgb(255, 255, 255)');
});

test('el estado de un detalle se lee sobre la franja verde (en claro y en oscuro)', async ({ page }) => {
  await page.goto('/pedidos');
  await page.getByRole('button', { name: /Ver pedido #\d+/ }).first().click();
  const badge = page.getByRole('dialog', { name: /Pedido #\d+/ }).locator('[data-slot="dialog-header"] [data-slot="estado-badge"]');
  // Fondo blanco sólido: el color del estado no se pierde contra el degradado.
  await expect.poll(() => badge.evaluate((el) => getComputedStyle(el).backgroundColor)).toBe('rgb(255, 255, 255)');
  await expect.poll(() => badge.evaluate((el) => getComputedStyle(el).color)).not.toBe('rgb(255, 255, 255)');
  await page.keyboard.press('Escape');
  await page.getByRole('button', { name: 'Usar tema oscuro' }).click();
  await page.getByRole('button', { name: /Ver pedido #\d+/ }).first().click();
  await expect.poll(() => badge.evaluate((el) => getComputedStyle(el).backgroundColor)).toBe('rgb(255, 255, 255)');
  await page.keyboard.press('Escape');
  await page.getByRole('button', { name: 'Usar tema claro' }).click();
});

test('el hover marca también las filas pares de un listado', async ({ page }) => {
  await page.goto('/pedidos');
  const par = page.locator('main tbody tr').nth(1);
  const antes = await par.evaluate((el) => getComputedStyle(el).backgroundColor);
  await par.hover();
  await expect.poll(() => par.evaluate((el) => getComputedStyle(el).backgroundColor)).not.toBe(antes);
});

test('«Inicio» conserva el azul de marca aunque la página sea de otra sección', async ({ page }) => {
  await page.goto('/pedidos');
  const inicio = page.getByRole('navigation', { name: 'Principal' }).getByRole('link', { name: 'Inicio' });
  await inicio.hover();
  await expect.poll(() => inicio.evaluate((el) => getComputedStyle(el).color)).toBe(NAVY);
});

test('en oscuro los acentos suben de tono y la barra superior sigue oscura', async ({ page }) => {
  await page.goto('/pedidos');
  await page.getByRole('button', { name: 'Usar tema oscuro' }).click();
  await expect(page.locator('html')).toHaveClass(/dark/);
  const menu = page.getByRole('navigation', { name: 'Principal' });
  await expect.poll(() => menu.getByRole('link', { name: 'Pedidos' }).evaluate((el) => getComputedStyle(el).color)).toBe(ESMERALDA_OSCURO);
  await page.getByRole('button', { name: 'Usar tema claro' }).click();
  await expect(page.locator('html')).not.toHaveClass(/dark/);
  // En claro la barra superior es navy (#1b2a4e), no el fondo claro de la página.
  await expect.poll(() => fondo(page, 'header')).toBe('rgb(27, 42, 78)');
});

test('el menú ⋮ pinta cada acción con su color, sea cual sea la sección', async ({ page }) => {
  const icono = (nombre: RegExp) => page.getByRole('menuitem', { name: nombre }).locator('svg').first();
  const color = (nombre: RegExp) => icono(nombre).evaluate((el) => getComputedStyle(el).color);

  // Maestros (navy): Editar en verde, Inhabilitar en ámbar.
  await page.goto('/proveedores');
  await page.getByRole('button', { name: /^Más acciones para/ }).first().click();
  await expect.poll(() => color(/^Editar/)).toBe('rgb(31, 122, 77)'); // --success
  await expect.poll(() => color(/^Inhabilitar/)).toBe('rgb(178, 94, 9)'); // --warning
  // El ícono va en una cajita teñida.
  expect(await icono(/^Editar/).evaluate((el) => getComputedStyle(el).backgroundColor)).not.toBe('rgba(0, 0, 0, 0)');
  await page.keyboard.press('Escape');

  // Operativa (esmeralda): el PDF sigue en gris, no toma el color de la sección.
  await page.goto('/pedidos');
  await page.getByRole('button', { name: /Más acciones del pedido/ }).first().click();
  await expect.poll(() => color(/Ver PDF/)).toBe('rgb(91, 102, 120)'); // --muted-foreground
});

test('la barra de filtros lleva el tinte y el riel de la sección', async ({ page }) => {
  await page.goto('/pedidos');
  const barra = page.getByRole('search').first();
  await expect.poll(() => barra.evaluate((el) => getComputedStyle(el).borderLeftColor)).toBe('rgb(16, 185, 129)'); // acento esmeralda
  expect(await barra.evaluate((el) => getComputedStyle(el).backgroundImage)).toContain('linear-gradient');
});

test('el asistente muestra el cliente y el creador en chips, y la orden su resumen', async ({ page }) => {
  await page.goto('/pedidos');
  await page.getByRole('button', { name: /Más acciones del pedido/ }).first().click();
  await page.getByRole('menuitem', { name: /Editar|Registrar pago/ }).click();
  const chips = page.locator('[data-slot="chip-persona"]');
  await expect(chips.filter({ hasText: 'Cliente' })).toHaveCount(1);
  await expect(chips.filter({ hasText: /Creado por/ })).toHaveCount(1);

  await page.goto('/ordenes');
  await page.getByRole('button', { name: /Ver órdenes/ }).first().click();
  await page.getByRole('dialog').getByRole('button', { name: /Ver orden/ }).first().click();
  const resumen = page.getByRole('region', { name: 'Resumen de la orden' });
  await expect(resumen).toBeVisible();
  await expect(resumen.getByRole('progressbar')).toBeVisible();
});
