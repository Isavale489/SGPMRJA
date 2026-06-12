{{--
    Píldora de fecha + hora en vivo + tasa BCV del día. Reutilizable en cualquier modal/footer.
    Params:
      - $prefix  (req.) prefijo único para los IDs (p. ej. 'ped', 'ord')
      - $modalId (opc.) id del modal para arrancar/detener el reloj con su ciclo de vida
      - $sm      (opc.) variante compacta
      - $bcv     (opc.) muestra la píldora de tasa BCV del día (default: true)
    Estilos: .wiz-body-datetime y .wiz-body-bcv en custom.css (compartidos).
--}}
@php($wdtPrefix = $prefix ?? 'wiz')
@php($wdtClass = ($sm ?? false) ? 'wiz-body-datetime wiz-body-datetime--sm' : 'wiz-body-datetime')
@php($wdtBcv = $bcv ?? true)
<div class="wiz-hdr-pills">
    @if($wdtBcv)
    <div class="{{ $wdtClass }} wiz-body-bcv" id="{{ $wdtPrefix }}-bcv" title="Tasa oficial BCV del día">
        <span class="wiz-body-dt-item">
            <i class="ri-money-dollar-circle-line"></i>
            <span class="wiz-body-bcv-text">
                <span class="wiz-body-bcv-label">BCV <span id="{{ $wdtPrefix }}-bcv-fecha">—</span></span>
                <span class="wiz-body-bcv-val" id="{{ $wdtPrefix }}-bcv-val">—</span>
            </span>
        </span>
    </div>
    @endif
    <div class="{{ $wdtClass }}" title="Fecha y hora">
        <span class="wiz-body-dt-item">
            <i class="ri-calendar-event-line"></i>
            <span class="wiz-body-dt-text" id="{{ $wdtPrefix }}-dt-fecha">—</span>
        </span>
        <span class="wiz-body-dt-divider"></span>
        <span class="wiz-body-dt-item">
            <i class="ri-time-line"></i>
            <span class="wiz-body-dt-text" id="{{ $wdtPrefix }}-dt-hora">—</span>
            <span class="wiz-body-dt-live" aria-hidden="true" title="En vivo"></span>
        </span>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var fechaEl = document.getElementById('{{ $wdtPrefix }}-dt-fecha');
    var horaEl  = document.getElementById('{{ $wdtPrefix }}-dt-hora');
    if (!fechaEl || !horaEl) return;

    function tick() {
        var now = new Date();
        var dd = String(now.getDate()).padStart(2, '0');
        var mo = String(now.getMonth() + 1).padStart(2, '0');
        fechaEl.textContent = dd + '/' + mo + '/' + now.getFullYear();
        var h = now.getHours();
        var ampm = h >= 12 ? 'p.m.' : 'a.m.';
        h = h % 12; if (h === 0) h = 12;
        var mm = String(now.getMinutes()).padStart(2, '0');
        horaEl.textContent = h + ':' + mm + ' ' + ampm;
    }
    tick();

    @if($wdtBcv)
    // Refleja la tasa BCV del día (formato del header global): "Bs. valor" + "BCV dd/mm"
    var bcvValEl = document.getElementById('{{ $wdtPrefix }}-bcv-val');
    var bcvFechaEl = document.getElementById('{{ $wdtPrefix }}-bcv-fecha');
    function bcvTick() {
        if (!bcvValEl) return;
        if (window.tasaBcv && window.tasaBcv.valor) {
            bcvValEl.textContent = 'Bs. ' + Number(window.tasaBcv.valor)
                .toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            if (bcvFechaEl && window.tasaBcv.fecha) {
                var p = String(window.tasaBcv.fecha).split('-');
                bcvFechaEl.textContent = (p.length === 3) ? (p[2] + '/' + p[1]) : '';
            }
        } else {
            bcvValEl.textContent = 'N/D';
            if (bcvFechaEl) bcvFechaEl.textContent = '';
        }
    }
    bcvTick();
    @endif

    var interval = null;
    @if(!empty($modalId))
    var modalEl = document.getElementById(@json($modalId));
    if (modalEl) {
        modalEl.addEventListener('show.bs.modal', function () {
            tick();
            @if($wdtBcv) bcvTick(); @endif
            clearInterval(interval);
            interval = setInterval(tick, 1000);
        });
        modalEl.addEventListener('hidden.bs.modal', function () {
            clearInterval(interval);
            interval = null;
        });
    } else {
        interval = setInterval(tick, 1000);
    }
    @else
    interval = setInterval(tick, 1000);
    @endif
})();
</script>
@endpush
