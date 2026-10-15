import { test, expect } from '@playwright/test';

/**
 * Mi perfil migrado a Inertia. No cambia datos del administrador (otros specs
 * los usan): prueba los formularios y los errores del servidor.
 */
test('el perfil muestra sus secciones y el menú de usuario lleva a él', async ({ page }) => {
  const errores: string[] = [];
  page.on('pageerror', (e) => errores.push(e.message));
  await page.goto('/proveedores');
  await page.getByRole('button', { name: /Admin E2E/ }).click();
  await page.getByRole('menuitem', { name: 'Mi perfil' }).click();
  await expect(page.getByRole('heading', { name: 'Mi perfil' })).toBeVisible();
  await expect(page.getByText('Configuradas')).toBeVisible();
  expect(errores).toEqual([]);
});

test('cambiar contraseña con la actual incorrecta muestra el error en su campo', async ({ page }) => {
  await page.goto('/profile');
  await page.getByRole('button', { name: 'Cambiar contraseña' }).click();
  const d = page.getByRole('dialog', { name: 'Cambiar contraseña' });
  await d.getByLabel('Contraseña actual').fill('no-es-esta');
  await d.getByLabel('Contraseña nueva', { exact: false }).first().fill('Otra.Clave99');
  await d.getByLabel('Confirmar contraseña nueva').fill('Otra.Clave99');
  await d.getByRole('button', { name: 'Cambiar contraseña' }).click();
  await expect(d.getByText('La contraseña actual no es correcta.')).toBeVisible();
});

test('editar una pregunta pide la contraseña actual', async ({ page }) => {
  await page.goto('/profile');
  await page.getByRole('button', { name: 'Actualizar preguntas' }).click();
  const d = page.getByRole('dialog', { name: 'Actualizar preguntas de seguridad' });
  await expect(d.getByText('●●●●●●●● · respuesta cifrada')).toHaveCount(3);
  await expect(d.getByLabel('Tu contraseña actual')).toHaveCount(0);
  await d.getByRole('button', { name: 'Cambiar' }).first().click();
  await expect(d.getByLabel('Tu contraseña actual')).toBeVisible();
});

test('en móvil no se desborda', async ({ page }) => {
  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto('/profile');
  expect(await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth)).toBeLessThanOrEqual(0);
});
