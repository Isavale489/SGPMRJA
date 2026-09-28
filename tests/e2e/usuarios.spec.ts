import { test, expect, type Page } from '@playwright/test';

import { ADMIN } from './datos';

/** Usuarios migrado a Inertia + React. Los usuarios nunca se borran: se inhabilitan. */
test.describe.configure({ mode: 'serial' });

const USUARIO = { nombre: 'Carmen Silva', email: 'carmen.silva@atlantico.test' };
// PNG de 1×1 para probar la foto de perfil sin archivos en el repo.
const PNG = Buffer.from('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==', 'base64');

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

test('alta de un usuario con foto: la política de contraseña se exige', async ({ page }) => {
  const errores = vigilarErrores(page);
  await page.goto('/users');
  await page.getByRole('button', { name: 'Agregar usuario' }).click();
  const dialogo = page.getByRole('dialog');
  await dialogo.getByLabel('Foto de perfil').setInputFiles({ name: 'carmen.png', mimeType: 'image/png', buffer: PNG });
  await expect(dialogo.getByText('carmen.png')).toBeVisible();
  await dialogo.getByLabel('Nombre').fill(USUARIO.nombre);
  await dialogo.getByLabel('Correo electrónico').fill(USUARIO.email);
  await elegir(page, 'Rol', 'Supervisor');
  await dialogo.getByLabel('Contraseña *', { exact: true }).fill('debil123');
  await dialogo.getByLabel('Confirmar contraseña').fill('debil123');
  await dialogo.getByRole('button', { name: 'Agregar usuario' }).click();
  await expect(dialogo.getByText('La contraseña debe incluir al menos una mayúscula, un número y un carácter especial.')).toBeVisible();

  await dialogo.getByLabel('Contraseña *', { exact: true }).fill('Clave-Segura1');
  await dialogo.getByLabel('Confirmar contraseña').fill('Clave-Segura1');
  await dialogo.getByRole('button', { name: 'Agregar usuario' }).click();
  await expect(page.getByText('Usuario creado exitosamente.')).toBeVisible();
  const fila = page.getByRole('row', { name: new RegExp(USUARIO.nombre) });
  await expect(fila).toContainText('Supervisor');
  await expect(fila.locator('img')).toHaveAttribute('src', /\/storage\/avatars\//);
  expect(errores, errores.join('\n')).toEqual([]);
});

test('editar no pide contraseña y guarda los cambios', async ({ page }) => {
  await page.goto('/users');
  await page.getByRole('button', { name: `Más acciones para ${USUARIO.nombre}` }).click();
  await page.getByRole('menuitem', { name: 'Editar' }).click();
  const dialogo = page.getByRole('dialog');
  await expect(dialogo.getByLabel('Contraseña *', { exact: true })).toHaveCount(0);
  await dialogo.getByLabel('Nombre').fill(`${USUARIO.nombre} R.`);
  await dialogo.getByRole('button', { name: 'Guardar cambios' }).click();

  await expect(page.getByText('Usuario actualizado exitosamente.')).toBeVisible();
  await expect(page.getByRole('row', { name: new RegExp(`${USUARIO.nombre} R\\.`) })).toBeVisible();
});

test('resetear la contraseña deja una clave temporal', async ({ page }) => {
  await page.goto('/users');
  await page.getByRole('button', { name: `Más acciones para ${USUARIO.nombre} R.` }).click();
  await page.getByRole('menuitem', { name: 'Resetear contraseña' }).click();
  const dialogo = page.getByRole('dialog');
  await dialogo.getByLabel('Contraseña temporal *', { exact: true }).fill('Temporal-2026');
  await dialogo.getByLabel('Confirmar contraseña temporal').fill('Otra-2026');
  await dialogo.getByRole('button', { name: 'Resetear contraseña' }).click();
  await expect(dialogo.getByText('Las contraseñas no coinciden.')).toBeVisible();

  await dialogo.getByLabel('Confirmar contraseña temporal').fill('Temporal-2026');
  await dialogo.getByRole('button', { name: 'Resetear contraseña' }).click();
  await expect(page.getByText(/Contraseña reseteada/)).toBeVisible();
  await expect(page.getByRole('menu')).toHaveCount(0);
  await expect(page.getByRole('row', { name: new RegExp(USUARIO.nombre) })).toContainText('Clave temporal');
});

test('la propia cuenta no ofrece inhabilitar ni resetear', async ({ page }) => {
  await page.goto('/users');
  const propia = page.getByRole('row', { name: /\(tú\)/ });
  await expect(propia).toContainText(ADMIN.email);
  await propia.getByRole('button', { name: /Más acciones para/ }).click();
  await expect(page.getByRole('menuitem', { name: 'Editar' })).toBeVisible();
  await expect(page.getByRole('menuitem', { name: 'Inhabilitar' })).toHaveCount(0);
  await expect(page.getByRole('menuitem', { name: 'Resetear contraseña' })).toHaveCount(0);
});

test('inhabilitar no borra al usuario y se puede habilitar', async ({ page }) => {
  await page.goto('/users');
  await page.getByRole('button', { name: `Más acciones para ${USUARIO.nombre} R.` }).click();
  await page.getByRole('menuitem', { name: 'Inhabilitar' }).click();
  await page.getByRole('alertdialog').getByRole('button', { name: 'Inhabilitar' }).click();
  await expect(page.getByText('Usuario inhabilitado exitosamente.')).toBeVisible();

  await page.getByRole('link', { name: 'Inhabilitados' }).click();
  await page.getByRole('button', { name: `Más acciones para ${USUARIO.nombre} R.` }).click();
  await page.getByRole('menuitem', { name: 'Habilitar' }).click();
  await expect(page.getByText('Usuario habilitado exitosamente.')).toBeVisible();
});

test('en móvil la página no se desborda', async ({ page }) => {
  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto('/users');
  const desborde = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
  expect(desborde).toBeLessThanOrEqual(0);
});
