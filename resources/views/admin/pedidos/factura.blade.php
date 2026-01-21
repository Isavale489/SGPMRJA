<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Pedido #{{ $pedido->id }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 0;
        }

        /* Ajuste de ancho para que no se corte en A4 (595pt ≈ 794px) */
        .container {
            max-width: 750px;
            width: 100%;
            margin: 0 auto;
            padding-left: 0px;
            padding-right: 30px;
            padding-top: 10px;
            padding-bottom: 10px;
        }

        .header-table {
            width: 100%;
        }

        .header-table td {
            vertical-align: top;
        }

        .logo {
            width: 100px;
        }

        .company-name {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
        }

        .company-info {
            text-align: center;
            font-size: 11px;
        }

        .info-table,
        .items-table,
        .totals-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        /* Forzar mismas dimensiones */
        .info-table,
        .items-table {
            table-layout: fixed;
        }

        /* Definir proporciones de columnas en items-table */
        .items-table th:nth-child(1),
        .items-table td:nth-child(1) {
            width: 7%;
        }

        .items-table th:nth-child(2),
        .items-table td:nth-child(2) {
            width: 35%;
        }

        /* Concepto o Descripción del Producto */
        .items-table th:nth-child(3),
        .items-table td:nth-child(3) {
            width: 10%;
        }

        /* Modelo */
        .items-table th:nth-child(4),
        .items-table td:nth-child(4) {
            width: 7%;
        }

        /* Color */
        .items-table th:nth-child(5),
        .items-table td:nth-child(5) {
            width: 7%;
        }

        /* Talla */
        .items-table th:nth-child(6),
        .items-table td:nth-child(6) {
            width: 15%;
        }

        /* Logo */
        .items-table th:nth-child(7),
        .items-table td:nth-child(7) {
            width: 9%;
        }

        /* Costo Unitario */
        .items-table th:nth-child(8),
        .items-table td:nth-child(8) {
            width: 10%;
        }

        /* Monto */
        .info-table td {
            border: 1px solid #000;
            padding: 4px;
            font-size: 11px;
            word-break: break-all;
            /* evita desbordes largos como emails */
        }

        .items-table th,
        .items-table td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            font-size: 11px;
        }

        .items-table th {
            background-color: #eaeaea;
        }

        .totals-table {
            margin-left: auto;
        }

        /* Alinea totales a la derecha dentro del contenedor */
        .totals-table td {
            padding: 4px;
            font-size: 11px;
        }

        .totals-table .label {
            text-align: right;
        }

        .totals-table .value {
            text-align: right;
            width: 80px;
        }

        .note {
            background-color: #ffff00;
            margin-top: 12px;
            padding: 8px;
            font-size: 11px;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Encabezado con logo y datos de la empresa -->
        <div style="position: relative; min-height: 100px; margin-bottom: 8px;">
            <img src="{{ public_path('logo.jpg') }}" alt="Logo"
                style="width: 100px; position: absolute; left: 0; top: 0;">
            <div style="text-align: center; padding-left: 30px;">
                <div class="company-name" style="font-size: 18px; font-weight: bold;">Manufacturas R.J. ATLANTICO C.A.
                </div>
                <div class="company-info" style="font-size: 11px;">
                    Rif: J-40391423-0 &nbsp;&nbsp; Telf.: 0414-3558537 - 0255-6640625 &nbsp;&nbsp; Email:
                    rjatlantico@gmail.com
                </div>
                <div class="company-info" style="font-size: 11px;">
                    Av. Esquina calle 35 locales 1 y 2 sector centro Acarigua - Edo. Portuguesa
                </div>
            </div>
        </div>

        <!-- N° de Pedido y Elaborado por -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin: 10px 0 5px 0;">
            <div style="flex: 1; text-align: center; font-size: 15px; font-weight: bold;">N° de Pedido:
                {{ $pedido->id }}</div>
            <div style="flex: 1; text-align: right; font-size: 13px; font-weight: bold;">Elaborado por:
                {{ $pedido->user->name ?? '-' }}</div>
        </div>

        <!-- Tabla de información del cliente -->
        <table class="info-table">
            <tr>
                <td style="width: 100px; font-weight:bold;">Cliente:</td>
                <td style="width: 220px;">{{ $pedido->cliente_nombre_completo }}</td>
                <td style="width: 60px; font-weight:bold;">
                    @if(str_starts_with($pedido->cliente_documento, 'V-'))
                        C.I.:
                    @else
                        Rif:
                    @endif
                </td>
                <td>{{ $pedido->cliente_documento }}</td>
                <td style="width: 60px; font-weight:bold;">Fecha del Pedido:</td>
                <td>{{ \Carbon\Carbon::parse($pedido->fecha_pedido)->format('d/m/Y') }}</td>
                <td style="width: 60px; font-weight:bold;">Fecha Entrega Est.:</td>
                <td>{{ $pedido->fecha_entrega_estimada ? \Carbon\Carbon::parse($pedido->fecha_entrega_estimada)->format('d/m/Y') : '-' }}
                </td>
            </tr>
            <tr>
                <td style="font-weight:bold;">Teléfono Contacto:</td>
                <td>{{ $pedido->cliente_telefono_normalizado }}</td>
                <td style="font-weight:bold;">e-mail:</td>
                <td colspan="5" style="word-break: break-all; word-wrap: break-word; max-width: 100%;">
                    {{ $pedido->cliente_email_normalizado }}</td>
            </tr>
        </table>

        <!-- Tabla de items -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 7%;">Cant.</th>
                    <th style="width: 35%;">Concepto o Descripción del Producto</th>
                    <th style="width: 10%;">Modelo</th>
                    <th style="width: 7%;">Color</th>
                    <th style="width: 7%;">Talla</th>
                    <th style="width: 15%;">Logo</th>
                    <th style="width: 9%;">Costo Unitario</th>
                    <th style="width: 10%;">Monto</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pedido->productos as $detalle)
                    <tr>
                        <td>{{ $detalle->cantidad }}</td>
                        <td>
                            {{ $detalle->producto->nombre }}
                            @if($detalle->descripcion)
                                - {{ $detalle->descripcion }}
                            @endif
                        </td>
                        <td>{{ $detalle->producto->modelo ?? '-' }}</td>
                        <td>{{ $detalle->color ?? '-' }}</td>
                        <td>{{ $detalle->talla ?? '-' }}</td>
                        <td>
                            @if($detalle->lleva_bordado && $detalle->nombre_logo)
                                {{ $detalle->nombre_logo }}
                                <br><small>Ubicación: {{ $detalle->ubicacion_logo ?? '-' }}</small>
                                <br><small>Cantidad: {{ $detalle->cantidad_logo ?? '-' }}</small>
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ number_format($detalle->precio_unitario, 2) }}</td>
                        <td>{{ number_format($detalle->precio_unitario * $detalle->cantidad, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totales -->
        <table class="totals-table" align="right" style="margin-top: 6px; width: 300px;">
            <tr>
                <td class="label">Total</td>
                <td class="value">{{ number_format($subtotal, 2) }}</td>
            </tr>
            <tr>
                <td class="label">Descuento</td>
                <td class="value">{{ number_format($descuento, 2) }}</td>
            </tr>
            <tr>
                <td class="label">IVA (16%)</td>
                <td class="value">{{ number_format($iva, 2) }}</td>
            </tr>
            <tr>
                <td class="label" style="font-weight:bold;">Total a Pagar</td>
                <td class="value" style="font-weight:bold;">{{ number_format($totalPagar, 2) }}</td>
            </tr>
        </table>

        <div style="clear: both;"></div>

        <!-- Nota -->
        <div class="note">
            Tiempo de Ejecución del Trabajo 30 días hábiles<br>
            70% del costo total para la formalización del pedido, 30% a la entrega<br>
            El plazo de entrega comienza a transcurrir una vez realizado el pago<br>
            <strong>No se modifican pedidos ya formalizados (ni tallas ni cantidades)</strong>
        </div>
    </div>
</body>

</html>