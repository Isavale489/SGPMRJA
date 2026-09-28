import { test, expect, type Page } from '@playwright/test';

import { CLIENTE } from './datos';

/** Empleados migrado a Inertia + React. Crea sus propios datos. */
test.describe.configure({ mode: 'serial' });

const EMPLEADO = { cedula: '17654321', nombre: 'Rosa Linares' };
const DEPTO = 'Corte E2E';
const CARGO = 'Cortador E2E';

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

test('alta de un empleado creando su departamento y cargo sin salir del formulario', async ({ page }) => {
  const errores = vigilarErrores(page);
  await page.goto('/empleados');
  await page.getByRole('button', { name: 'Agregar empleado' }).click();
  const dialogo = page.getByRole('dialog');
  await dialogo.getByRole('textbox', { name: 'Documento de identidad' }).fill(EMPLEADO.cedula);
  await dialogo.getByLabel('Nombre y apellido').fill(EMPLEADO.nombre);
  await dialogo.getByLabel('Número del teléfono 1').fill('5551234');

  await dialogo.getByRole('button', { name: 'Nuevo departamento' }).click();
  await dialogo.getByLabel('Nombre del nuevo departamento').fill(DEPTO);
  await dialogo.getByRole('button', { name: 'Guardar departamento' }).click();
  await expect(dialogo.getByRole('combobox', { name: 'Departamento' })).toHaveText(DEPTO);

  await dialogo.getByRole('button', { name: 'Nuevo cargo' }).click();
  await dialogo.getByLabel('Nombre del nuevo cargo').fill(CARGO);
  await dialogo.getByRole('button', { name: 'Guardar cargo' }).click();
  await expect(dialogo.getByRole('combobox', { name: 'Cargo' })).toHaveText(CARGO);
  await expect(dialogo.getByLabel('Nombre y apellido')).toHaveValue(EMPLEADO.nombre); // el formulario no se perdió

  await dialogo.getByRole('button', { name: 'Agregar empleado' }).click();
  await expect(page.getByText('Empleado creado exitosamente.')).toBeVisible();
  const fila = page.getByRole('row', { name: new RegExp(EMPLEADO.nombre) });
  await expect(fila).toContainText(/EMP-\d{3}/);
  await expect(fila).toContainText(CARGO);
  expect(errores, errores.join('\n')).toEqual([]);
});

test('un menor de edad se marca en el campo', async ({ page }) => {
  await page.goto('/empleados');
  await page.getByRole('button', { name: 'Agregar empleado' }).click();
  const dialogo = page.getByRole('dialog');
  await dialogo.getByRole('textbox', { name: 'Documento de identidad' }).fill('30111222');
  await dialogo.getByLabel('Nombre y apellido').fill('Pedro Joven');
  await dialogo.getByLabel('Fecha de nacimiento').fill('2015-01-01');
  await dialogo.getByLabel('Número del teléfono 1').fill('5559999');
  await elegir(page, 'Departamento', DEPTO);
  await elegir(page, 'Cargo', CARGO);
  await dialogo.getByRole('button', { name: 'Agregar empleado' }).click();

  await expect(dialogo.getByText('El empleado debe ser mayor de 18 años')).toBeVisible();
});

test('una persona que ya es cliente se reutiliza con sus datos', async ({ page }) => {
  const errores = vigilarErrores(page);
  await page.goto('/empleados');
  await page.getByRole('button', { name: 'Agregar empleado' }).click();
  const dialogo = page.getByRole('dialog');
  await dialogo.getByRole('textbox', { name: 'Documento de identidad' }).fill('12345678'); // CLIENTE del seeder
  await dialogo.getByLabel('Nombre y apellido').click();

  await expect(dialogo.getByText(/ya está registrada como cliente/)).toBeVisible();
  await expect(dialogo.getByRole('button', { name: 'Agregar empleado' })).toBeDisabled();
  await dialogo.getByRole('button', { name: 'Usar sus datos' }).click();
  await expect(dialogo.getByLabel('Nombre y apellido')).toHaveValue(CLIENTE);
  await dialogo.getByLabel('Número del teléfono 1').fill('5557777');
  await elegir(page, 'Departamento', DEPTO);
  await elegir(page, 'Cargo', CARGO);
  await dialogo.getByRole('button', { name: 'Agregar empleado' }).click();

  await expect(page.getByText('Empleado creado exitosamente.')).toBeVisible();
  await page.getByRole('button', { name: `Ver ${CLIENTE}` }).click();
  await expect(page.getByRole('dialog')).toContainText('También registrado como');
  expect(errores, errores.join('\n')).toEqual([]);
});

test('editar conserva documento y código y no duplica el apellido', async ({ page }) => {
  await page.goto('/empleados');
  await page.getByRole('button', { name: `Más acciones para ${EMPLEADO.nombre}` }).click();
  await page.getByRole('menuitem', { name: 'Editar' }).click();
  const dialogo = page.getByRole('dialog');
  await expect(dialogo.getByRole('textbox', { name: 'Documento de identidad' })).toBeDisabled();
  await dialogo.getByLabel('Correo electrónico').fill('rosa.linares@atlantico.test');
  await dialogo.getByRole('button', { name: 'Guardar cambios' }).click();

  await expect(page.getByText('Empleado actualizado exitosamente.')).toBeVisible();
  await expect(page.getByRole('row', { name: new RegExp(EMPLEADO.nombre) })).toContainText(`V-${EMPLEADO.cedula}`);
  await expect(page.getByText(`${EMPLEADO.nombre} Linares`)).toHaveCount(0);
});

test('inhabilitar lo pasa al historial y se puede restaurar', async ({ page }) => {
  await page.goto('/empleados');
  await page.getByRole('button', { name: `Más acciones para ${EMPLEADO.nombre}` }).click();
  await page.getByRole('menuitem', { name: 'Inhabilitar' }).click();
  await page.getByRole('alertdialog').getByRole('button', { name: 'Inhabilitar' }).click();
  await expect(page.getByText('Empleado inhabilitado exitosamente.')).toBeVisible();
  await expect(page.getByRole('menu')).toHaveCount(0);

  await page.getByRole('link', { name: 'Inhabilitados' }).click();
  await page.getByRole('button', { name: `Más acciones para ${EMPLEADO.nombre}` }).click();
  await page.getByRole('menuitem', { name: 'Restaurar' }).click();
  await expect(page.getByText('Empleado restaurado exitosamente.')).toBeVisible();
});

test('en móvil la página no se desborda', async ({ page }) => {
  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto('/empleados');
  const desborde = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
  expect(desborde).toBeLessThanOrEqual(0);
});
