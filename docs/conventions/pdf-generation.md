# Generación de PDFs

> Cómo el proyecto genera PDFs desde HTML. **No usar `pandoc` ni `wkhtmltopdf`** — no están instalados.

## Herramienta: Google Chrome Headless

```bash
google-chrome --headless --disable-gpu --no-sandbox \
  --print-to-pdf=/ruta/salida.pdf \
  --print-to-pdf-no-header \
  /ruta/entrada.html
```

## Flujo estándar

1. Crear archivo `.html` autocontenido con CSS embebido y `@media print` optimizado.
2. Ejecutar el comando Chrome headless apuntando al `.html`.
3. El `.pdf` se genera en la ruta especificada.

## Convenciones de CSS para impresión

```css
@page {
    margin: 15mm;
}

.card,
.section,
.bordado-row {
    page-break-inside: avoid;   /* evita cortes feos */
}
```

## Convención de archivos

El `.html` intermedio queda en `tareas/` junto al `.pdf` por si se necesita reeditar el documento sin reconstruir el HTML desde cero.

```
tareas/
├── nombre_documento.html      ← fuente editable
└── nombre_documento.pdf       ← salida final
```

## Excepción: facturas de Pedidos

`resources/views/admin/pedidos/factura.blade.php` usa estilos inline porque históricamente se generaba con wkhtmltopdf. Ese archivo está excluido de la regla "no inline styles" precisamente por esta razón. Si se migra a Chrome headless en el futuro, los inline pueden moverse a CSS embebido.
