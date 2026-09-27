{{-- Estilos compartidos de los documentos transaccionales (factura de cotización / pedido).
     Se incluye dentro de @section('extra-styles'). --}}
/* ── Bloque de datos (cliente / documento) ── */
.info-block { width: 100%; border: none; margin-bottom: 12px; }
.info-block td {
    border: none;
    padding: 0 14px 0 0;
    font-size: 9.5px;
    line-height: 1.7;
    vertical-align: top;
    width: 50%;
    word-break: break-word;
}
.info-block .label { font-weight: bold; color: #1e3c72; }

/* ── Anchos de columnas de la tabla de ítems ── */
.col-num    { width: 4%;    text-align: center; }
.col-codigo { width: 12%; }
.col-prod   { width: 40%; }
.col-talla  { width: 9%;    text-align: center; }
.col-cant   { width: 8%;    text-align: center; }
.col-punit  { width: 13.5%; text-align: right; }
.col-monto  { width: 13.5%; text-align: right; }

.data-table tbody td.text-center { text-align: center; }
.data-table tbody td.text-right  { text-align: right; }

/* Código del producto (SKU) */
.col-codigo .prod-code {
    font-family: 'DejaVu Sans Mono', monospace;
    font-size: 8px;
    color: #1e3c72;
    font-weight: bold;
    word-break: break-all;
}
.col-prod strong { color: #2d3436; }
.col-prod small  { color: #6b7280; }
.col-prod .desc-line { color: #6b7280; font-style: italic; }
.col-prod .bord-line { color: #1e3c72; }
.data-table tbody td .bs-eq { color: #777777; font-size: 8px; font-weight: normal; }

/* ── Bloque anclado al pie de la página (montos + condiciones) ── */
.doc-bottom {
    position: fixed;
    left: 30px;
    right: 30px;
    bottom: 42px;
}

/* ── Bloque de totales ── */
.totals-block { width: 100%; border: none; margin-top: 10px; margin-bottom: 14px; }
.totals-block > tbody > tr > td { border: none; padding: 0; }
.totals-inner { width: 260px; border: none; }
.totals-inner td { border: none; padding: 3px 0; font-size: 10px; }
.totals-inner .t-label { color: #3d4852; }
.totals-inner .t-value { text-align: right; font-weight: bold; color: #1e3c72; }
.totals-inner .t-grand {
    font-size: 12px;
    color: #064e3b;
    border-top: 1.5px solid #1e3c72;
    padding-top: 5px;
}

/* ── Nota / condiciones ── */
.nota-especial {
    clear: both;
    background-color: #fff8e1;
    color: #5d4037;
    padding: 10px 12px;
    border: 1px solid #ffe082;
    border-left: 3px solid #ffb300;
    margin-top: 12px;
    font-size: 9px;
    line-height: 1.55;
}
