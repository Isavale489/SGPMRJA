import { test, expect } from '@playwright/test';

/** Inicio migrado a Inertia: KPIs que llevan a su módulo, gráficos y maestros. */
test('el inicio muestra sus indicadores y lleva a las alertas de insumos', async ({ page }) => {
  const problemas: string[] = [];
  page.on('pageerror', (e) => problemas.push(`JS: ${e.message}`));
  page.on('console', (m) => { if ((m.type() === 'warning' || m.type() === 'error') && /AG Charts/i.test(m.text())) problemas.push(`AG: ${m.text()}`); });

  await page.goto('/dashboard');
  await expect(page.getByRole('heading', { name: 'Inicio' })).toBeVisible();
  await expect(page.getByText('Entregas esta semana')).toBeVisible();
  await expect(page.locator('canvas').first()).toBeVisible();

  // «Hilo Mov E2E» arranca bajo su mínimo en el seeder.
  await page.getByRole('link', { name: /Insumos en alerta/ }).click();
  await expect(page.getByRole('heading', { name: 'Alertas de existencia' })).toBeVisible();
  expect(problemas, problemas.join('\n')).toEqual([]);
});

test('en móvil el inicio no se desborda', async ({ page }) => {
  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto('/dashboard');
  const desborde = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
  expect(desborde).toBeLessThanOrEqual(0);
});
