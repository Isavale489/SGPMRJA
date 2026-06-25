{{-- Bloque de totales compartido (factura de cotización / pedido).
     Requiere: $subtotal, $descuento, $iva, $totalPagar, $tasaValor (?float), $tasaFecha (?date).
     Opcional: $totalBordadoUsd. --}}
@php($totalBordadoUsd = $totalBordadoUsd ?? 0)
<table class="totals-block">
    <tr>
        <td>&nbsp;</td>
        <td style="width: 260px;">
            <table class="totals-inner">
                <tr>
                    <td class="t-label">Subtotal:</td>
                    <td class="t-value">$ {{ number_format($subtotal, 2) }}</td>
                </tr>
                @if($totalBordadoUsd > 0)
                <tr>
                    <td class="t-label">Servicio de bordado:</td>
                    <td class="t-value">$ {{ number_format($totalBordadoUsd, 2) }}</td>
                </tr>
                @endif
                <tr>
                    <td class="t-label">Descuento:</td>
                    <td class="t-value">$ {{ number_format($descuento, 2) }}</td>
                </tr>
                <tr>
                    <td class="t-label">IVA (16%):</td>
                    <td class="t-value">$ {{ number_format($iva, 2) }}</td>
                </tr>
                <tr>
                    <td class="t-label t-grand">Total:</td>
                    <td class="t-value t-grand">$ {{ number_format($totalPagar, 2) }}</td>
                </tr>
                @if($tasaValor)
                <tr>
                    <td class="t-label" style="padding-top:5px;">Tasa aplicada{{ $tasaFecha ? ' (' . \Carbon\Carbon::parse($tasaFecha)->format('d/m/Y') . ')' : '' }}:</td>
                    <td class="t-value" style="padding-top:5px; font-weight:normal;">Bs {{ number_format($tasaValor, 4, ',', '.') }} / USD</td>
                </tr>
                <tr>
                    <td class="t-label">Equivalente Bs:</td>
                    <td class="t-value">Bs {{ number_format($totalPagar * $tasaValor, 2, ',', '.') }}</td>
                </tr>
                @endif
            </table>
        </td>
    </tr>
</table>
