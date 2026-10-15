import { test, expect, type Page } from '@playwright/test';

/**
 * Cotizaciones migradas a Inertia: asistente Cliente → Productos → Resumen,
 * bordados, «Ver» y acciones de estado. Usa un cliente propio del seeder para no
 * mezclarse con la cotización Aprobada que convierte flujo-cotizacion-pedido.
 */
test.describe.configure({ mode: 'serial' });

const CLIENTE_COT = 'Uniformes Araure QC';

function vigilar(page: Page): string[] {
  const errores: string[] = [];
  page.on('pageerror', (e) => errores.push(`JS: ${e.message}`));
  page.on('response', (r) => { if (r.status() >= 500) errores.push(`HTTP ${r.status()} ${r.url()}`); });
  return errores;
}

test('crear una cotización por el asistente, con bordado', async ({ page }) => {
  const errores = vigilar(page);
  await page.goto('/cotizaciones', { waitUntil: 'networkidle' });
  await page.getByRole('link', { name: 'Nueva cotización' }).click();
  await expect(page.getByRole('heading', { name: 'Nueva cotización' })).toBeVisible();

  // Paso 1: sin cliente no se avanza.
  const formulario = page.locator('#form-cotizacion');
  await formulario.getByRole('button', { name: 'Siguiente' }).click();
  await expect(page.getByText('Elige el cliente.')).toBeVisible();
  await page.getByRole('combobox', { name: 'Buscar cliente' }).fill('Araure');
  await page.getByRole('option', { name: new RegExp(CLIENTE_COT) }).click();
  await page.getByRole('radio', { name: /Alta/ }).check();
  await formulario.getByRole('button', { name: 'Siguiente' }).click();

  // Paso 2: sin productos no se avanza; se agrega desde el catálogo.
  await formulario.getByRole('button', { name: 'Siguiente' }).click();
  await expect(page.getByText('Agrega al menos un producto.')).toBeVisible();
  await page.getByRole('button', { name: 'Abrir catálogo' }).click();
  const dialogo = page.getByRole('dialog', { name: 'Agregar producto' });
  // Elegir el producto ya pasa al paso Variante; el SKU y el precio se resuelven en el servidor.
  await dialogo.getByRole('button', { name: /Franela Cot E2E.*atributos/ }).click();
  await expect(dialogo.getByRole('heading', { name: 'Variante' })).toBeVisible();
  await expect(dialogo.getByText('Variante encontrada')).toBeVisible();
  await dialogo.getByRole('button', { name: 'Siguiente' }).click();
  await expect(dialogo.getByRole('heading', { name: 'Configurar' })).toBeVisible();
  await expect(dialogo.getByText(/^Falta .*el color, al menos una talla con cantidad\.$/)).toBeVisible();
  await dialogo.getByRole('radio', { name: 'Azul Marino' }).click();
  await dialogo.getByLabel(/^Talla M ·/).first().fill('10');
  await expect(dialogo.getByLabel('Precio unitario ($)')).toHaveValue('15'); // precio de confección del tipo
  await dialogo.getByRole('button', { name: 'Agregar a la cotización' }).click();
  await expect(dialogo).toBeHidden();
  const fila = page.getByRole('row', { name: /Franela Cot E2E/ });
  await expect(fila).toContainText('10');
  await expect(fila).toContainText('$15,00');

  // Bordado: una ubicación del catálogo con su precio base.
  await page.getByRole('button', { name: 'Bordados de Franela Cot E2E' }).click();
  const bordados = page.getByRole('dialog', { name: 'Bordados' });
  await bordados.getByRole('checkbox').first().check();
  await expect(bordados.getByText(/1 \/ \d+/)).toBeVisible();
  await bordados.getByRole('button', { name: 'Aplicar bordados' }).click();
  await expect(bordados).toBeHidden();
  await expect(fila).toContainText(/bordado/i);

  // Paso 3: resumen con la tasa y su fecha; guardar.
  await formulario.getByRole('button', { name: 'Siguiente' }).click();
  await expect(page.getByText(/Tasa BCV \(\d{2}\/\d{2}\/\d{4}\)/).first()).toBeVisible();
  await page.getByRole('button', { name: 'Crear cotización' }).click();
  await expect(page.getByText(/Cotización #\d+ creada/)).toBeVisible();
  await expect(page).toHaveURL(/\/cotizaciones\?ver=\d+/);
  const ver = page.getByRole('dialog', { name: /Cotización #\d+ Pendiente/ });
  await expect(ver).toContainText('Alta'); // la prioridad se guardó
  await expect(ver).toContainText(CLIENTE_COT);
  expect(errores, errores.join('\n')).toEqual([]);
});

/** Regresión: con líneas repetidas (misma talla y género), el editor mostraba solo la última y al guardar se perdía el resto. */
test('editar un producto con tallas repetidas muestra la suma y no pierde unidades', async ({ page }) => {
  const editarProducto = async () => {
    await page.goto('/cotizaciones', { waitUntil: 'networkidle' });
    await page.getByRole('row', { name: /Tallas Repetidas/ }).getByRole('button', { name: /Más acciones de la cotización/ }).click();
    await page.getByRole('menuitem', { name: 'Editar' }).click();
    await page.locator('#form-cotizacion').getByRole('button', { name: 'Siguiente' }).click();
    await page.getByRole('button', { name: 'Editar Chemise' }).click();
    return page.getByRole('dialog', { name: 'Editar producto' });
  };
  const guardarYVer = async (dialogo: ReturnType<typeof page.getByRole>) => {
    await dialogo.getByRole('button', { name: 'Guardar cambios' }).click();
    await expect(dialogo).toBeHidden();
    await page.locator('#form-cotizacion').getByRole('button', { name: 'Siguiente' }).click();
    await page.getByRole('button', { name: 'Guardar cambios' }).click();
    await expect(page.getByText(/Cotización #\d+ actualizada\./)).toBeVisible();
    const ver = page.getByRole('dialog', { name: /Cotización #\d+/ });
    await ver.getByRole('button', { name: 'Siguiente' }).click();
    return ver;
  };

  // Sembrada con dos líneas M (3 «Ana» + 2 «Luis»): la celda muestra la suma, no solo la última.
  let dialogo = await editarProducto();
  await expect(dialogo.getByLabel(/^Talla M ·/).first()).toHaveValue('5');
  await dialogo.getByRole('radio', { name: 'Azul Marino' }).click(); // la línea sembrada no tiene color
  // Sin tocar la cantidad, las dos líneas se conservan tal cual.
  let ver = await guardarYVer(dialogo);
  await expect(ver).toContainText('$50,00'); // 5 × $10
  await expect(ver.getByText(/^M.*×\s*3$/)).toBeVisible();
  await expect(ver.getByText(/^M.*×\s*2$/)).toBeVisible();

  // Si cambia la cantidad, queda una sola línea.
  dialogo = await editarProducto();
  await dialogo.getByLabel(/^Talla M ·/).first().fill('6');
  ver = await guardarYVer(dialogo);
  await expect(ver).toContainText('$60,00');
  await expect(ver.getByText(/^M.*×\s*6$/)).toBeVisible();
  await expect(ver.getByText(/^M.*×\s*[32]$/)).toHaveCount(0);
});

/** Regresión: al reabrir un producto con precio negociado, «Precio base» mostraba el negociado y «Restaurar» no volvía al de catálogo. */
test('editar un producto con precio negociado muestra el precio de catálogo y Restaurar vuelve a él', async ({ page }) => {
  await page.goto('/cotizaciones/crear', { waitUntil: 'networkidle' });
  await page.getByRole('combobox', { name: 'Buscar cliente' }).fill('Araure');
  await page.getByRole('option', { name: new RegExp(CLIENTE_COT) }).click();
  await page.locator('#form-cotizacion').getByRole('button', { name: 'Siguiente' }).click();

  await page.getByRole('button', { name: 'Abrir catálogo' }).click();
  let dialogo = page.getByRole('dialog', { name: 'Agregar producto' });
  await dialogo.getByRole('button', { name: /Franela Cot E2E.*atributos/ }).click();
  await expect(dialogo.getByText('Variante encontrada')).toBeVisible();
  await dialogo.getByRole('button', { name: 'Siguiente' }).click();
  await dialogo.getByRole('radio', { name: 'Azul Marino' }).click();
  await dialogo.getByLabel(/^Talla M ·/).first().fill('4');
  await dialogo.getByLabel('Precio unitario ($)').fill('12'); // negociado con el cliente
  await dialogo.getByRole('button', { name: 'Agregar a la cotización' }).click();
  await expect(page.getByRole('row', { name: /Franela Cot E2E/ })).toContainText('$12,00');

  await page.getByRole('button', { name: 'Editar Franela Cot E2E' }).click();
  dialogo = page.getByRole('dialog', { name: 'Editar producto' });
  await expect(dialogo.getByRole('heading', { name: 'Configurar' })).toBeVisible(); // editar abre en Configurar
  await expect(dialogo.getByLabel('Precio unitario ($)')).toHaveValue('12'); // el negociado se conserva
  await expect(dialogo.getByText('Precio base: $15,00', { exact: false })).toBeVisible(); // el de catálogo
  await dialogo.getByRole('button', { name: 'Restaurar' }).click();
  await expect(dialogo.getByLabel('Precio unitario ($)')).toHaveValue('15');
});

/** Regresión: las altas rápidas del configurador (color, tela, logo) no guardaban con Enter. */
test('Enter en el alta rápida de color la guarda y la deja elegida, sin salir del configurador', async ({ page }) => {
  await page.goto('/cotizaciones/crear', { waitUntil: 'networkidle' });
  await page.getByRole('combobox', { name: 'Buscar cliente' }).fill('Araure');
  await page.getByRole('option', { name: new RegExp(CLIENTE_COT) }).click();
  await page.locator('#form-cotizacion').getByRole('button', { name: 'Siguiente' }).click();

  await page.getByRole('button', { name: 'Abrir catálogo' }).click();
  const dialogo = page.getByRole('dialog', { name: 'Agregar producto' });
  await dialogo.getByRole('button', { name: /Franela Cot E2E.*atributos/ }).click();
  await expect(dialogo.getByText('Variante encontrada')).toBeVisible();
  await dialogo.getByRole('button', { name: 'Siguiente' }).click();
  await dialogo.getByRole('button', { name: 'Nuevo color' }).click();

  const alta = page.getByRole('dialog', { name: 'Nuevo color' });
  const nombre = `Verde Enter ${Date.now() % 100000}`;
  await alta.getByLabel('Nombre').fill(nombre);
  await alta.getByLabel('Nombre').press('Enter');

  await expect(alta).toBeHidden();
  await expect(page.getByText(`Color «${nombre}» creado.`)).toBeVisible();
  await expect(dialogo.getByRole('radio', { name: nombre })).toBeChecked();
  await expect(dialogo.getByRole('heading', { name: 'Configurar' })).toBeVisible(); // el asistente no se movió
});

/** Regresión: Enter dentro del alta rápida de cliente (diálogo abierto desde el paso 1) debe guardar el cliente, no mover el asistente. */
test('dar de alta un cliente con Enter desde el paso Cliente', async ({ page }) => {
  await page.goto('/cotizaciones/crear', { waitUntil: 'networkidle' });
  await page.getByRole('combobox', { name: 'Buscar cliente' }).fill('Textiles Enter');
  await page.getByRole('button', { name: 'Nuevo cliente' }).click();
  const alta = page.getByRole('dialog');
  // Cédula única por corrida (el test se puede repetir sin chocar con la anterior).
  await alta.getByRole('textbox', { name: 'Cédula' }).fill(String(19_000_000 + (Date.now() % 900_000)));
  await alta.getByLabel('Nombre y apellido').fill('Textiles Enter');
  await alta.getByLabel('Número del teléfono 1').fill('5559876');
  await alta.getByRole('combobox', { name: 'Estado' }).click();
  await page.getByRole('option', { name: 'Portuguesa', exact: true }).click();
  await expect(alta.getByRole('combobox', { name: 'Municipio' })).toBeEnabled();
  await alta.getByRole('combobox', { name: 'Municipio' }).click();
  await page.getByRole('option', { name: 'Páez', exact: true }).click();
  await alta.getByLabel('Nombre y apellido').press('Enter');

  await expect(alta).toBeHidden();
  await expect(page.getByText('Textiles Enter').first()).toBeVisible();
  await expect(page.getByText('Elige el cliente.')).toHaveCount(0); // el asistente no intentó avanzar
  await expect(page.getByRole('heading', { name: 'Nueva cotización' })).toBeVisible(); // ni se envió la cotización
});

test('editar conserva los productos y aprobar cambia el estado', async ({ page }) => {
  await page.goto('/cotizaciones', { waitUntil: 'networkidle' });
  const fila = page.getByRole('row', { name: new RegExp(CLIENTE_COT) }).first();
  await expect(fila).toContainText('Pendiente');
  await fila.getByRole('button', { name: /Más acciones de la cotización/ }).click();
  await page.getByRole('menuitem', { name: 'Editar' }).click();
  await expect(page.getByRole('heading', { name: /Editar cotización #\d+/ })).toBeVisible();
  const formulario = page.locator('#form-cotizacion');
  await formulario.getByRole('button', { name: 'Siguiente' }).click();
  await expect(page.getByRole('row', { name: /Franela Cot E2E/ })).toContainText('10');
  await formulario.getByRole('button', { name: 'Siguiente' }).click();
  await page.getByLabel('Notas internas').fill('Entregar en la sede de Araure');
  await page.getByRole('button', { name: 'Guardar cambios' }).click();
  await expect(page.getByText(/actualizada/i)).toBeVisible();

  await page.goto('/cotizaciones', { waitUntil: 'networkidle' });
  const otra = page.getByRole('row', { name: new RegExp(CLIENTE_COT) }).first();
  await otra.getByRole('button', { name: /Más acciones de la cotización/ }).click();
  await page.getByRole('menuitem', { name: /Aprobar/ }).click();
  await page.getByRole('alertdialog').getByRole('button', { name: /Aprobar/ }).click();
  await expect(otra).toContainText('Aprobada');
});

/** Regresión: un producto sin bordado no se podía volver a guardar (la lista vacía chocaba con min:1). */
test('editar una cotización sin bordado se guarda', async ({ page }) => {
  await page.goto('/cotizaciones', { waitUntil: 'networkidle' });
  const fila = page.getByRole('row', { name: /Confecciones Portuguesa/ }).filter({ hasText: 'Aprobada' });
  await fila.getByRole('button', { name: /Más acciones de la cotización/ }).click();
  await page.getByRole('menuitem', { name: 'Editar' }).click();
  const formulario = page.locator('#form-cotizacion');
  await formulario.getByRole('button', { name: 'Siguiente' }).click();
  await expect(page.getByRole('row', { name: /Chemise/ })).toContainText('Sin bordado');
  await formulario.getByRole('button', { name: 'Siguiente' }).click();
  await page.getByLabel('Notas internas').fill('Sin bordado: solo confección');
  await page.getByRole('button', { name: 'Guardar cambios' }).click();
  await expect(page.getByText(/Cotización #\d+ actualizada\./)).toBeVisible();
});

test('en móvil las páginas no se desbordan', async ({ page }) => {
  await page.setViewportSize({ width: 375, height: 800 });
  for (const url of ['/cotizaciones', '/cotizaciones/crear']) {
    await page.goto(url, { waitUntil: 'networkidle' });
    const ancho = await page.evaluate(() => document.documentElement.scrollWidth);
    expect(ancho, url).toBeLessThanOrEqual(375);
  }
});
