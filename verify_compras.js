const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  try {
    // 1. Login
    console.log('\n=== PASO 1: Login ===');
    await page.goto('http://localhost:8000/login');
    await page.fill('input[name="email"]', 'earroyo@trocglobal.com');
    await page.fill('input[name="password"]', 'Admin1234*');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard', { timeout: 8000 });
    console.log('OK Login:', page.url());

    // 2. Listado /compras
    console.log('\n=== PASO 2: GET /compras ===');
    await page.goto('http://localhost:8000/compras');
    await page.waitForLoadState('networkidle');
    const title = await page.title();
    const btnNueva = await page.locator('button:has-text("Nueva Compra")').count();
    console.log('OK Titulo:', title);
    console.log('OK Boton Nueva Compra visible:', btnNueva > 0);
    await page.screenshot({ path: 'C:\\SGPMRJA\\ss1_index.png', fullPage: true });

    // 3. Abrir modal
    console.log('\n=== PASO 3: Abrir modal ===');
    await page.click('button:has-text("Nueva Compra")');
    await page.waitForSelector('#createCompraModal.show', { timeout: 5000 });
    console.log('OK Modal abierto');
    await page.screenshot({ path: 'C:\\SGPMRJA\\ss2_modal.png', fullPage: true });

    // 4. Cabecera del formulario
    console.log('\n=== PASO 4: Llenar cabecera ===');
    await page.click('#createCompraModal .select2-selection');
    await page.waitForSelector('.select2-dropdown', { timeout: 3000 });
    await page.click('.select2-results__option:first-child');
    const provVal = await page.locator('#c-proveedor').inputValue();
    console.log('OK Proveedor seleccionado, ID:', provVal);
    await page.fill('#c-factura', 'TEST-001');
    await page.fill('#c-fecha', '2026-06-02');

    // 5. Agregar item 1
    console.log('\n=== PASO 5: Agregar 2 items ===');
    await page.click('#c-add-item-btn');
    await page.waitForSelector('#c-items-tbody tr', { timeout: 2000 });
    await page.locator('#c-items-tbody tr:nth-child(1) .c-insumo').selectOption({ index: 1 });
    await page.waitForTimeout(400);
    const costo1 = await page.locator('#c-items-tbody tr:nth-child(1) .c-costo').inputValue();
    console.log('OK Item1 — costo auto:', costo1);
    await page.fill('#c-items-tbody tr:nth-child(1) .c-cantidad', '10');
    await page.dispatchEvent('#c-items-tbody tr:nth-child(1) .c-cantidad', 'input');

    // Agregar item 2
    await page.click('#c-add-item-btn');
    await page.locator('#c-items-tbody tr:nth-child(2) .c-insumo').selectOption({ index: 2 });
    await page.waitForTimeout(400);
    const costo2 = await page.locator('#c-items-tbody tr:nth-child(2) .c-costo').inputValue();
    console.log('OK Item2 — costo auto:', costo2);
    await page.fill('#c-items-tbody tr:nth-child(2) .c-cantidad', '5');
    await page.dispatchEvent('#c-items-tbody tr:nth-child(2) .c-cantidad', 'input');
    await page.waitForTimeout(300);

    const subtotal = await page.locator('#c-resumen-subtotal').textContent();
    const total    = await page.locator('#c-resumen-total').textContent();
    console.log('OK Subtotal:', subtotal.trim(), '| Total:', total.trim());

    const ins1 = await page.locator('#c-items-tbody tr:nth-child(1) .c-insumo').inputValue();
    const ins2 = await page.locator('#c-items-tbody tr:nth-child(2) .c-insumo').inputValue();
    console.log('Insumos IDs:', ins1, ins2);
    await page.screenshot({ path: 'C:\\SGPMRJA\\ss3_form.png', fullPage: true });

    // 6. Capturar stock antes via tinker (resultado se ve en el report final)
    console.log('\n=== PASO 6: Enviar compra ===');
    let ajaxResp = null;
    page.on('response', async (r) => {
      if (r.url().includes('/compras') && r.request().method() === 'POST') {
        try { ajaxResp = await r.json(); console.log('AJAX:', JSON.stringify(ajaxResp)); } catch(e) {}
      }
    });

    await page.click('#c-submit-btn');
    await page.waitForSelector('.swal2-popup', { timeout: 10000 });
    const swalT = await page.locator('.swal2-title').textContent();
    const swalM = await page.locator('.swal2-html-container').textContent().catch(() => '');
    console.log('OK SweetAlert titulo:', swalT.trim());
    console.log('OK SweetAlert msg:', swalM.trim());
    await page.screenshot({ path: 'C:\\SGPMRJA\\ss4_swal.png', fullPage: true });

    // 7. Ver detalle
    await page.click('.swal2-confirm');
    await page.waitForURL('**/compras/**', { timeout: 8000 });
    await page.waitForLoadState('networkidle');
    console.log('\n=== PASO 7: Vista show ===');
    console.log('OK URL:', page.url());
    const h4 = await page.locator('h4').first().textContent();
    const filas = await page.locator('tbody tr').count();
    const totalShow = await page.locator('tfoot tr:last-child td:last-child').textContent().catch(() => 'n/a');
    console.log('OK Titulo:', h4.trim());
    console.log('OK Filas items:', filas);
    console.log('OK Total:', totalShow.trim());
    const btnAnular = await page.locator('#btn-anular').count();
    console.log('OK Boton Anular visible:', btnAnular > 0);
    await page.screenshot({ path: 'C:\\SGPMRJA\\ss5_show.png', fullPage: true });

    // 8. Volver al listado
    console.log('\n=== PASO 8: Listado actualizado ===');
    await page.goto('http://localhost:8000/compras');
    await page.waitForLoadState('networkidle');
    await page.waitForSelector('#compras-table tbody tr:not(.dataTables_empty)', { timeout: 8000 });
    const filasDT = await page.locator('#compras-table tbody tr').count();
    const fila1   = await page.locator('#compras-table tbody tr:first-child').textContent();
    console.log('OK Filas DataTable:', filasDT);
    console.log('OK Primera fila:', fila1.trim().substring(0, 100));
    await page.screenshot({ path: 'C:\\SGPMRJA\\ss6_listado.png', fullPage: true });

    // PROBE 1: submit sin proveedor
    console.log('\n=== PROBE 1: Sin proveedor ni items ===');
    await page.click('button:has-text("Nueva Compra")');
    await page.waitForSelector('#createCompraModal.show', { timeout: 4000 });
    await page.click('#c-submit-btn');
    await page.waitForSelector('.swal2-popup', { timeout: 4000 });
    const p1 = await page.locator('.swal2-title').textContent();
    console.log('PROBE1 ->', p1.trim());
    await page.click('.swal2-confirm');

    // PROBE 2: con proveedor, sin items
    console.log('\n=== PROBE 2: Con proveedor, sin items ===');
    await page.click('#createCompraModal .select2-selection');
    await page.waitForSelector('.select2-dropdown', { timeout: 3000 });
    await page.click('.select2-results__option:first-child');
    await page.click('#c-submit-btn');
    await page.waitForSelector('.swal2-popup', { timeout: 4000 });
    const p2 = await page.locator('.swal2-title').textContent();
    console.log('PROBE2 ->', p2.trim());
    await page.click('.swal2-confirm');
    await page.keyboard.press('Escape');
    await page.waitForTimeout(600);

    // PROBE 3: modal reseteado tras cerrar
    console.log('\n=== PROBE 3: Reset al reabrir modal ===');
    await page.click('button:has-text("Nueva Compra")');
    await page.waitForSelector('#createCompraModal.show', { timeout: 4000 });
    const factPost = await page.locator('#c-factura').inputValue();
    const itemsPost = await page.locator('#c-items-tbody tr').count();
    console.log('PROBE3 factura limpia:', factPost === '', '| items limpios:', itemsPost === 0);
    await page.screenshot({ path: 'C:\\SGPMRJA\\ss7_reset.png', fullPage: true });
    await page.keyboard.press('Escape');

    console.log('\n=== VERIFICACION COMPLETA ===');

  } catch(err) {
    console.error('ERROR:', err.message);
    await page.screenshot({ path: 'C:\\SGPMRJA\\ss_error.png', fullPage: true }).catch(() => {});
    process.exit(1);
  } finally {
    await browser.close();
  }
})();
