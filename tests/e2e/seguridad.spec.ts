import { test, expect } from '@playwright/test';

/** Roles y permisos migrados a Inertia. Crea su propio rol. */
test.describe.configure({ mode: 'serial' });

const ROL = 'Taller E2E';

test('crear un rol y asignarle permisos: «ver» se marca solo', async ({ page }) => {
  const errores: string[] = [];
  page.on('pageerror', (e) => errores.push(e.message));
  await page.goto('/configuracion/seguridad');
  await page.getByRole('button', { name: 'Nuevo rol' }).click();
  const d = page.getByRole('dialog', { name: 'Nuevo rol' });
  await d.getByLabel('Nombre del rol').fill(ROL);
  await d.getByRole('button', { name: 'Crear rol' }).click();
  await expect(page.getByText('Rol creado correctamente.')).toBeVisible();

  // Pasa a Permisos con el rol nuevo elegido.
  await expect(page.getByRole('tab', { name: 'Permisos' })).toHaveAttribute('aria-selected', 'true');
  await expect(page.getByText(`${ROL} tiene 0 permisos`)).toBeVisible();

  await page.getByRole('checkbox', { name: /^Clientes: Crear/ }).check();
  await expect(page.getByRole('checkbox', { name: /^Clientes: Ver/ })).toBeChecked();
  await expect(page.getByText('Cambios sin guardar')).toBeVisible();
  await page.getByRole('button', { name: 'Guardar permisos' }).click();
  await expect(page.getByText('Permisos actualizados correctamente.')).toBeVisible();
  await expect(page.getByText('Cambios sin guardar')).toHaveCount(0);

  // Quitar «Ver» quita todo el módulo.
  await page.getByRole('checkbox', { name: /^Clientes: Ver/ }).uncheck();
  await expect(page.getByRole('checkbox', { name: /^Clientes: Crear/ })).not.toBeChecked();
  await page.getByRole('button', { name: 'Descartar' }).click();
  await expect(page.getByRole('checkbox', { name: /^Clientes: Crear/ })).toBeChecked();
  expect(errores).toEqual([]);
});

test('el Administrador no se configura y un rol sin usuarios se elimina', async ({ page }) => {
  await page.goto('/configuracion/seguridad');
  const admin = page.getByRole('listitem').filter({ hasText: 'Administrador' }).first();
  await expect(admin).toContainText('Acceso total');
  await expect(admin.getByRole('button', { name: /Eliminar/ })).toBeDisabled();

  await page.getByRole('button', { name: `Eliminar el rol ${ROL}` }).click();
  await page.getByRole('alertdialog').getByRole('button', { name: 'Eliminar' }).click();
  await expect(page.getByText('Rol eliminado correctamente.')).toBeVisible();
  await expect(page.getByRole('button', { name: `Eliminar el rol ${ROL}` })).toHaveCount(0);
});

test('se llega desde Configuración y en móvil no se desborda', async ({ page }) => {
  await page.goto('/configuracion');
  await page.getByRole('link', { name: 'Roles y permisos' }).click();
  await expect(page.getByRole('heading', { name: 'Roles y permisos' })).toBeVisible();
  await page.setViewportSize({ width: 390, height: 844 });
  await page.getByRole('tab', { name: 'Permisos' }).click();
  expect(await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth)).toBeLessThanOrEqual(0);
});
