{{-- Tabla de ítems compartida (factura de cotización / pedido).
     Requiere: $detalles (colección de DetalleCotizacion|DetallePedido), $tasaValor (?float). --}}
<table class="data-table">
    <thead>
        <tr>
            <th class="col-num">#</th>
            <th class="col-codigo">Código</th>
            <th class="col-prod">Producto</th>
            <th class="col-talla">Talla</th>
            <th class="col-cant">Cant.</th>
            <th class="col-punit">P. Unit.</th>
            <th class="col-monto">Subtotal</th>
        </tr>
    </thead>
    <tbody>
        @foreach($detalles as $index => $detalle)
            @php
                // Bordado (solo si la línea lo lleva)
                $ubicacionesTexto = $detalle->bordados->pluck('nombre_aplicado')->filter()->implode(', ');
                $logosTexto = $detalle->bordados
                    ->map(fn ($b) => trim((string) ($b->nombre_logo_aplicado ?? '')))
                    ->filter()->unique()->implode(', ');
                $cantidadBordados = $detalle->bordados->sum(fn ($b) => (int) ($b->cantidad ?? 1));

                // Variante (color · género · tela · atributos) — snapshot inmutable
                $telaSnap = $detalle->tela_snapshot;
                $atrSnap  = $detalle->atributos_snapshot;
                $variantPartes = [];
                if ($detalle->color)  { $variantPartes[] = 'Color: '  . $detalle->color->nombre; }
                if ($detalle->genero) { $variantPartes[] = 'Género: ' . $detalle->genero->nombre; }
                if (is_array($telaSnap) && !empty($telaSnap['nombre'])) {
                    $variantPartes[] = 'Tela: ' . $telaSnap['nombre'];
                }
                if (is_array($atrSnap)) {
                    foreach ($atrSnap as $atrNombre => $valNombre) {
                        $variantPartes[] = $atrNombre . ': ' . $valNombre;
                    }
                }

                // Código y nombre robustos (línea legacy o variante dinámica)
                $prodCodigo = optional($detalle->producto)->codigo ?? ($detalle->sku_snapshot ?: '—');
                $prodNombre = optional($detalle->producto)->nombre ?? (optional($detalle->tipoProducto)->nombre ?? 'Variante');
            @endphp
            <tr class="{{ $index % 2 === 1 ? 'zebra' : '' }}">
                <td class="col-num">{{ $index + 1 }}</td>
                <td class="col-codigo"><span class="prod-code">{{ $prodCodigo }}</span></td>
                <td class="col-prod">
                    <strong>{{ $prodNombre }}</strong>
                    @if(!empty($variantPartes))<br><small>{{ implode(' · ', $variantPartes) }}</small>@endif
                    @if($detalle->descripcion)<br><small class="desc-line">{{ $detalle->descripcion }}</small>@endif
                    @if($detalle->lleva_bordado)
                        <br><small class="bord-line"><b>Bordado:</b> {{ $logosTexto ?: ($detalle->nombre_logo ?: 's/logo') }}@if($ubicacionesTexto) — {{ $ubicacionesTexto }}@endif @if($cantidadBordados)(x{{ $cantidadBordados }})@endif</small>
                    @endif
                </td>
                <td class="col-talla text-center">{{ $detalle->talla?->etiqueta ?? '—' }}</td>
                <td class="col-cant text-center">{{ $detalle->cantidad }}</td>
                <td class="col-punit text-right">
                    $ {{ number_format($detalle->precio_unitario, 2) }}
                    @if($tasaValor)<br><span class="bs-eq">Bs {{ number_format($detalle->precio_unitario * $tasaValor, 2, ',', '.') }}</span>@endif
                </td>
                <td class="col-monto text-right">
                    $ {{ number_format($detalle->cantidad * $detalle->precio_unitario, 2) }}
                    @if($tasaValor)<br><span class="bs-eq">Bs {{ number_format($detalle->cantidad * $detalle->precio_unitario * $tasaValor, 2, ',', '.') }}</span>@endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
