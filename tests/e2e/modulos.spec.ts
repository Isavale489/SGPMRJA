import { test, expect, type Page } from '@playwright/test';
import { execFileSync } from 'node:child_process';

/**
 * Cada módulo del registry (config/modulos.php) carga sin 5xx, sin errores
 * de JavaScript y con su tabla (DataTables server-side) respondiendo.
 * Es la red que detecta una página rota al migrar un módulo a Inertia.
 */
type Pagina = { modulo: string; path: string };

const registry: Pagina[] = JSON.parse(execFileSync('php', ['tests/e2e/modulos.php']).toString());
const paginas: Pagina[] = [
  { modulo: 'dashboard', path: '/dashboard' },
  ...registry,
  { modulo: 'reportes', path: '/reportes/general' },
  { modulo: 'seguridad', path: '/configuracion/seguridad' },
];

function vigilarErrores(page: Page): string[] {
  const errores: string[] = [];
  page.on('pageerror', (e) => errores.push(`JS: ${e.message}`));
  page.on('console', (m) => { if (m.type() === 'error') errores.push(`console: ${m.text()}`); });
  page.on('response', (r) => { if (r.status() >= 500) errores.push(`HTTP ${r.status()} ${r.url()}`); });
  return errores;
}

for (const { modulo, path } of paginas) {
  test(`el módulo ${modulo} carga sin errores (${path})`, async ({ page }) => {
    const errores = vigilarErrores(page);

    const resp = await page.goto(path);
    expect(resp?.status(), `HTTP de ${path}`).toBeLessThan(400);
    await expect(page).not.toHaveURL(/\/login|\/profile/);
    await page.waitForLoadState('networkidle');

    // Si la página tiene una tabla server-side, su petición de datos debe cerrar.
    const tablas = page.locator('table.dataTable');
    if (await tablas.count()) {
      await expect(page.locator('.dataTables_processing:visible')).toHaveCount(0, { timeout: 15_000 });
    }

    expect(errores, errores.join('\n')).toEqual([]);
  });
}
