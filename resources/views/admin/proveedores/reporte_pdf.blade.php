<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Proveedores</title>
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
            font-size: 10px;
            word-wrap: break-word;
            overflow-wrap: break-word;
            word-break: break-all;
        }

        th {
            background-color: #f0f0f0;
        }

        .badge-activo {
            background-color: #28a745;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
        }

        .badge-inactivo {
            background-color: #dc3545;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
        }
    </style>
</head>

<body>
    <div class="container">
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
        <div style="text-align:center; font-size:16px; font-weight:bold; margin: 0 0 8px 0;">
            Reporte General de Proveedores
        </div>
        <div style="text-align:right; font-size:10px; margin-bottom: 5px;">
            Fecha de generación: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}
        </div>
        <table>
            <thead>
                <tr>
                    <th style="width: 8%;">Tipo</th>
                    <th style="width: 12%;">Documento</th>
                    <th style="width: 18%;">Nombre/Razón Social</th>
                    <th style="width: 18%;">Dirección</th>
                    <th style="width: 10%;">Teléfono</th>
                    <th style="width: 15%;">Email</th>
                    <th style="width: 10%;">Contacto</th>
                    <th style="width: 7%;">Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($proveedores as $proveedor)
                    <tr>
                        <td>
                            @if($proveedor->tipo_proveedor === 'natural')
                                <span
                                    style="background:#17a2b8; color:white; padding:2px 4px; border-radius:3px; font-size:8px;">Natural</span>
                            @else
                                <span
                                    style="background:#007bff; color:white; padding:2px 4px; border-radius:3px; font-size:8px;">Jurídico</span>
                            @endif
                        </td>
                        <td>{{ $proveedor->documento }}</td>
                        <td style="text-align: left; padding-left: 5px;">{{ $proveedor->nombre_completo }}</td>
                        <td style="text-align: left; padding-left: 5px;">{{ $proveedor->direccion_unificada }}</td>
                        <td>{{ $proveedor->telefono_unificado }}</td>
                        <td style="font-size: 8px;">{{ $proveedor->email_unificado }}</td>
                        <td>{{ $proveedor->tipo_proveedor === 'natural' ? '-' : $proveedor->contacto }}</td>
                        <td>
                            @if($proveedor->estado)
                                <span class="badge-activo">Activo</span>
                            @else
                                <span class="badge-inactivo">Inactivo</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div style="margin-top: 15px; font-size: 10px; text-align: right;">
            <strong>Total de proveedores: {{ count($proveedores) }}</strong>
        </div>
    </div>
</body>

</html>