import { test, expect, type Page } from '@playwright/test';

/**
 * Control de Calidad migrado a Inertia. Usa la orden finalizada del E2eSeeder
 * (10 unidades, equipo Marta Colmenares 6 / Julio Arráiz 4).
 */
test.describe.configure({ mode: 'serial' });

function vigilarErrores(page: Page): string[] {
  const errores: string[] = [];
  page.on('pageerror', (e) => errores.push(`JS: ${e.message}`));
  page.on('response', (r) => { if (r.status() >= 500) errores.push(`HTTP ${r.status()} ${r.url()}`); });
  return errores;
}

test('abrir y cerrar la inspección sin tocar nada no avisa de cambios sin guardar; en móvil se lee', async ({ page }) => {
  const avisos: string[] = [];
  page.on('dialog', (d) => { avisos.push(d.message()); void d.dismiss(); });
  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto('/calidad', { waitUntil: 'networkidle' });
  await page.getByRole('row', { name: /Uniformes Araure QC/ }).getByRole('button', { name: /Ver órdenes/ }).click();
  await page.getByRole('dialog').getByRole('button', { name: /Inspeccionar orden/ }).click();

  const form = page.getByRole('dialog', { name: 'Registrar inspección' });
  await expect(form.getByLabel('Defectuosas de Marta Colmenares')).toBeHidden(); // sin defectuosas no se reparte
  // El campo Defectuosas (− número +) tiene espacio para leerse en un teléfono.
  const ancho = await form.getByLabel('Defectuosas', { exact: true }).evaluate((el) => el.getBoundingClientRect().width);
  expect(ancho).toBeGreaterThan(80);
  // No se puede escribir más de lo producido.
  // Al teclear no se recorta (camino de «8» se pasa por «18»); al salir del campo, sí.
  await form.getByLabel('Inspeccionadas').fill('25');
  await expect(form.getByLabel('Inspeccionadas')).toHaveValue('25');
  await form.getByLabel('Inspeccionadas').blur();
  await expect(form.getByLabel('Inspeccionadas')).toHaveValue('10');

  await form.getByRole('button', { name: 'Cancelar' }).click();
  await expect(form).toBeHidden();
  expect(avisos).toEqual([]);
});

test('rechazar parte de una orden: se reparte entre el equipo y vuelve a producción', async ({ page }) => {
  const errores = vigilarErrores(page);
  await page.goto('/calidad');
  await expect(page.getByRole('heading', { name: 'Control de calidad' })).toBeVisible();

  const fila = page.getByRole('row', { name: /Uniformes Araure QC/ });
  await expect(fila).toContainText('1 orden');
  await fila.getByRole('button', { name: 'Ver órdenes' }).click();
  await expect(page).toHaveURL(/pedido=\d+/);

  const cola = page.getByRole('dialog');
  await expect(cola).toContainText('10 de 10 producidas');
  await cola.getByRole('button', { name: 'Inspeccionar' }).click();

  const form = page.getByRole('dialog', { name: 'Registrar inspección' });
  await expect(form.getByLabel('Inspeccionadas')).toHaveValue('10');
  for (let i = 0; i < 3; i++) await form.getByRole('button', { name: 'Sumar una defectuosa' }).click();
  await expect(form.getByText('Rechazo: la orden vuelve a producción')).toBeVisible();

  // Reparto automático entre el equipo, sin pasar lo que produjo cada uno.
  const marta = form.getByLabel('Defectuosas de Marta Colmenares');
  const julio = form.getByLabel('Defectuosas de Julio Arráiz');
  expect(Number(await marta.inputValue()) + Number(await julio.inputValue())).toBe(3);
  await expect(form.getByText('Atribuidas 3 de 3')).toBeVisible();

  // Sin motivo, el servidor lo exige.
  await form.getByRole('button', { name: 'Registrar rechazo' }).click();
  await expect(form.getByText('El motivo es obligatorio cuando hay unidades rechazadas u observadas.')).toBeVisible();

  await form.getByLabel('Motivo del rechazo').fill('Costura torcida en el hombro');
  await form.getByRole('button', { name: 'Registrar rechazo' }).click();

  await expect(page.getByText('Inspección registrada correctamente.')).toBeVisible();
  // La orden volvió a producción: sale de la cola.
  await expect(page.getByRole('dialog')).toContainText('Este pedido ya no tiene órdenes pendientes.');
  expect(errores, errores.join('\n')).toEqual([]);
});

test('sin órdenes pendientes, la tabla lo dice', async ({ page }) => {
  await page.goto('/calidad');
  await expect(page.getByText('No hay órdenes pendientes de inspección.')).toBeVisible();
});

test('en móvil la página no se desborda', async ({ page }) => {
  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto('/calidad');
  const desborde = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
  expect(desborde).toBeLessThanOrEqual(0);
});
