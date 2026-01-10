<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Cotización #{{ $cotizacion->id }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 0;
        }

        .container {
            max-width: 750px;
            width: 100%;
            margin: 0 auto;
            padding-left: 0px;
            padding-right: 30px;
            padding-top: 10px;
            padding-bottom: 10px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            font-size: 11px;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        th {
            background-color: #f0f0f0;
        }

        .nota-especial {
            background-color: #ffff99;
            color: #000;
            padding: 8px;
            border: 1px solid #e6e600;
            margin-top: 18px;
            font-size: 11px;
        }

        .datos-section {
            margin-bottom: 10px;
        }

        .label {
            font-weight: bold;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Encabezado -->
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
        <div style="text-align:right; font-size:13px; font-weight:bold; margin-bottom: 8px;">
            COTIZACIÓN: {{ str_pad($cotizacion->id, 5, '0', STR_PAD_LEFT) }}<br>
            FECHA: {{ \Carbon\Carbon::parse($cotizacion->fecha_cotizacion)->format('d/m/Y') }}
        </div>
        <!-- Datos del Cliente y Cotización -->
        <div class="datos-section">
            <table style="width:100%; border:none; margin-bottom: 8px;">
                <tr>
                    <td class="text-left" style="border:none;">
                        <span class="label">Cliente:</span> {{ $cotizacion->cliente->nombre ?? '-' }}
                        {{ $cotizacion->cliente->apellido ?? '' }}<br>
                        <span class="label">Email:</span> {{ $cotizacion->cliente->email ?? '-' }}<br>
                        <span class="label">Teléfono:</span> {{ $cotizacion->cliente->telefono ?? '-' }}<br>
                        <span class="label">Documento de identidad:</span>
                        {{ $cotizacion->cliente->documento ?? '-' }}<br>
                    </td>
                    <td class="text-left" style="border:none;">
                        <span class="label">Fecha Validez:</span>
                        {{ $cotizacion->fecha_validez ? \Carbon\Carbon::parse($cotizacion->fecha_validez)->format('d/m/Y') : '-' }}<br>
                        <span class="label">Estado:</span> {{ $cotizacion->estado }}<br>
                        <span class="label">Elaborado por:</span> {{ $cotizacion->user->name ?? '-' }}<br>
                    </td>
                </tr>
            </table>
        </div>
        <!-- Tabla de Productos -->
        <table style="margin-bottom: 10px;">
            <thead>
                <tr>
                    <th style="width: 50px;">CANT.</th>
                    <th>PRODUCTO</th>
                    <th style="width: 100px;">DESCRIPCIÓN</th>
                    <th style="width: 50px;">TALLA</th>
                    <th>LOGO</th>
                    <th style="width: 70px;">UBICACIÓN</th>
                    <th style="width: 40px;">N° BORD.</th>
                    <th style="width: 60px;">P. UNIT.</th>
                    <th style="width: 60px;">MONTO</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cotizacion->productos as $detalle)
                    <tr>
                        <td>{{ $detalle->cantidad }}</td>
                        <td>{{ $detalle->producto->nombre ?? '-' }}</td>
                        <td class="text-left">{{ $detalle->descripcion ?? '-' }}</td>
                        <td>{{ $detalle->talla ?? '-' }}</td>
                        <td>{{ $detalle->lleva_bordado && $detalle->nombre_logo ? $detalle->nombre_logo : '-' }}</td>
                        <td>{{ $detalle->lleva_bordado ? ($detalle->ubicacion_logo ?? '-') : '-' }}</td>
                        <td>{{ $detalle->lleva_bordado ? ($detalle->cantidad_logo ?? '-') : '-' }}</td>
                        <td>{{ number_format($detalle->precio_unitario, 2) }}</td>
                        <td>{{ number_format($detalle->cantidad * $detalle->precio_unitario, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <!-- Totales -->
        <table style="width: 100%; margin-bottom: 10px;">
            <tr>
                <td class="text-right" style="border:none;"></td>
                <td style="width: 180px; border:none;">
                    <table style="width:100%;">
                        <tr>
                            <td class="label text-right">Subtotal:</td>
                            <td class="text-right">{{ number_format($subtotal, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="label text-right">Descuento:</td>
                            <td class="text-right">{{ number_format($descuento, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="label text-right">IVA (16%):</td>
                            <td class="text-right">{{ number_format($iva, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="label text-right">Total a Pagar:</td>
                            <td class="text-right"><b>{{ number_format($totalPagar, 2) }}</b></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <!-- Nota especial -->
        <div class="nota-especial">
            <b>Tiempo de Ejecución del Trabajo:</b> 30 días hábiles.<br>
            70% del costo total para la formalización del pedido, 30% a la entrega.<br>
            El plazo de entrega comienza a transcurrir una vez realizado el pago.<br>
            <b>No se modifican pedidos ya formalizados (ni tallas ni cantidades).</b>
        </div>
    </div>
</body>

</html>