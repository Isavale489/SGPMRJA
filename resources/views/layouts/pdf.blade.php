<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>@yield('page-title', 'Reporte PDF')</title>
    <style>
        /* ── Reset ── */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* ── Base ── */
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #2d3436;
            padding: 15px 30px;
        }

        /* ── Document Header ── */
        .doc-header {
            width: 100%;
            border-bottom: 3px solid #1e3c72;
            padding-bottom: 6px;
            margin-bottom: 10px;
        }

        .doc-header table {
            width: 100%;
            border: none;
        }

        .doc-header td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }

        .doc-header .logo-cell {
            width: 90px;
        }

        .doc-header .logo-cell img {
            width: 80px;
            height: auto;
        }

        .doc-header .info-cell {
            text-align: center;
        }

        .doc-header .meta-cell {
            width: 130px;
            text-align: right;
        }

        .company-name {
            font-size: 16px;
            font-weight: bold;
            color: #1e3c72;
            letter-spacing: 0.5px;
        }

        .company-detail {
            font-size: 9px;
            color: #3d4852;
            line-height: 1.5;
        }

        .report-title {
            font-size: 13px;
            font-weight: bold;
            color: #1e3c72;
            text-align: center;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .report-meta {
            font-size: 9px;
            color: #3d4852;
            text-align: right;
            line-height: 1.6;
            font-weight: 600;
        }

        /* ── Filter Bar (parámetros aplicados al listado) ── */
        .filter-bar {
            background-color: #eef2f9;
            border: 1px solid #dfe6f0;
            border-left: 3px solid #1e3c72;
            padding: 5px 12px;
            margin: 0 2px 10px 2px;
            font-size: 8.5px;
            color: #2d3436;
            line-height: 1.7;
        }

        .filter-bar .filter-bar-label {
            color: #1e3c72;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-right: 6px;
        }

        .filter-bar .filter-chip {
            display: inline-block;
            margin-right: 14px;
        }

        .filter-bar .filter-chip strong {
            color: #3d4852;
        }

        /* ── Summary Bar ── */
        .summary-bar {
            background-color: #f1f3f8;
            border: 1px solid #dfe6f0;
            padding: 6px 14px;
            margin: 0 2px 12px 2px;
        }

        .summary-bar table {
            width: 100%;
            border: none;
        }

        .summary-bar td {
            border: none;
            padding: 0 18px 0 0;
            font-size: 9px;
            color: #2d3436;
        }

        .summary-bar .label {
            color: #3d4852;
            font-weight: 600;
        }

        .summary-bar .value {
            font-weight: bold;
            color: #1e3c72;
        }

        /* ── Data Table ── */
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table thead tr {
            background-color: #1e3c72;
        }

        .data-table thead th {
            color: #ffffff;
            font-weight: 600;
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 8px 6px;
            text-align: left;
            border: none;
        }

        .data-table tbody tr {}

        .data-table tbody tr.zebra {
            background-color: #f8f9fc;
        }

        .data-table tbody td {
            padding: 6px;
            font-size: 9px;
            vertical-align: top;
            border: none;
            border-bottom: 1px solid #dde2e8;
            color: #2d3436;
        }

        /* ── Row # column ── */
        .col-num {
            width: 3%;
            text-align: center;
            color: #a0a8b4;
        }

        /* ── Status badges ── */
        .badge-activo {
            background-color: #d4edda;
            color: #155724;
            padding: 2px 6px;
            font-size: 8px;
            font-weight: 600;
        }

        .badge-inactivo {
            background-color: #f8d7da;
            color: #721c24;
            padding: 2px 6px;
            font-size: 8px;
            font-weight: 600;
        }

        /* ── Stock badges ── */
        .stock-bajo {
            background-color: #f8d7da;
            color: #721c24;
            padding: 2px 6px;
            font-size: 8px;
            font-weight: 600;
        }

        .stock-medio {
            background-color: #fff3cd;
            color: #856404;
            padding: 2px 6px;
            font-size: 8px;
            font-weight: 600;
        }

        .stock-normal {
            background-color: #d4edda;
            color: #155724;
            padding: 2px 6px;
            font-size: 8px;
            font-weight: 600;
        }

        /* ── Type badges ── */
        .badge-natural {
            background-color: #d1ecf1;
            color: #0c5460;
            padding: 2px 6px;
            font-size: 8px;
            font-weight: 600;
        }

        .badge-juridico {
            background-color: #cce5ff;
            color: #004085;
            padding: 2px 6px;
            font-size: 8px;
            font-weight: 600;
        }

        /* ── Footer (fijo: se repite en cada página) ── */
        .doc-footer {
            position: fixed;
            left: 0;
            right: 0;
            bottom: -30px;
            border-top: 1px solid #dfe6f0;
            padding-top: 6px;
            font-size: 8px;
            color: #777777;
            font-weight: 600;
        }

        .doc-footer table {
            width: 100%;
            border: none;
        }

        .doc-footer td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }

        .doc-footer .ft-center {
            text-align: center;
            color: #555555;
            font-weight: normal;
        }

        .doc-footer .ft-right {
            text-align: right;
            white-space: nowrap;
        }

        /* Paginación: contadores nativos de DomPDF (requieren position:fixed) */
        .doc-footer .pg-cur:after { content: counter(page); }
        .doc-footer .pg-tot:after { content: counter(pages); }

        /* ── Page break helper ── (margen inferior extra reservado para el footer fijo) */
        @page {
            margin: 35px 45px 58px 45px;
        }

        /* ── Estilos extra inyectados por la vista hija ── */
        @yield('extra-styles')
    </style>
</head>

<body>

    {{-- ═══════ DOCUMENT HEADER ═══════ --}}
    <div class="doc-header">
        <table>
            <tr>
                <td class="logo-cell">
                    <img src="{{ public_path('logo.jpg') }}" alt="Logo">
                </td>
                <td class="info-cell">
                    <div class="company-name">Manufacturas R.J. Atlántico C.A.</div>
                    <div class="company-detail">
                        RIF: J-40391423-0 &nbsp;&middot;&nbsp; Telf.: 0414-3558537 / 0255-6640625 &nbsp;&middot;&nbsp;
                        rjatlantico@gmail.com
                    </div>
                    <div class="company-detail">
                        Av. Esquina Calle 35, Locales 1 y 2, Sector Centro — Acarigua, Edo. Portuguesa
                    </div>
                </td>
                <td class="meta-cell">
                    <div class="report-meta">
                        {{ now()->format('d/m/Y') }}<br>
                        {{ now()->format('h:i A') }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ═══════ REPORT TITLE ═══════ --}}
    <div class="report-title">@yield('report-title')</div>

    {{-- ═══════ FILTROS APLICADOS (listados) ═══════ --}}
    @if(!empty($filtros ?? []))
        <div class="filter-bar">
            <span class="filter-bar-label">Filtros aplicados</span>
            @foreach($filtros as $etiqueta => $valor)
                <span class="filter-chip"><strong>{{ $etiqueta }}:</strong> {{ $valor }}</span>
            @endforeach
        </div>
    @endif

    {{-- ═══════ SUMMARY BAR ═══════ --}}
    @hasSection('summary-bar')
        <div class="summary-bar">
            <table>
                <tr>
                    @yield('summary-bar')
                </tr>
            </table>
        </div>
    @endif

    {{-- ═══════ CONTENT ═══════ --}}
    @yield('content')

    {{-- ═══════ FOOTER (estandarizado · se repite en cada página) ═══════ --}}
    <div class="doc-footer">
        <table>
            <tr>
                <td>Manufacturas R.J. Atlántico C.A. &middot; RIF J-40391423-0</td>
                <td class="ft-center">
                    Emitido por {{ optional(auth()->user())->name ?? 'Sistema' }}
                    &middot; {{ now()->format('d/m/Y h:i A') }}
                </td>
                <td class="ft-right">Página <span class="pg-cur"></span> de <span class="pg-tot"></span></td>
            </tr>
        </table>
    </div>

</body>

</html>
