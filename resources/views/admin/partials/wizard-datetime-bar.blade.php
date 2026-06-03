{{--
    Píldora de fecha + hora en vivo. Reutilizable en cualquier modal/footer.
    Params:
      - $prefix  (req.) prefijo único para los IDs (p. ej. 'ped', 'ord')
      - $modalId (opc.) id del modal para arrancar/detener el reloj con su ciclo de vida
    Estilos: .wiz-body-datetime en custom.css (compartido).
--}}
@php($wdtPrefix = $prefix ?? 'wiz')
@php($wdtClass = ($sm ?? false) ? 'wiz-body-datetime wiz-body-datetime--sm' : 'wiz-body-datetime')
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

    var interval = null;
    @if(!empty($modalId))
    var modalEl = document.getElementById(@json($modalId));
    if (modalEl) {
        modalEl.addEventListener('show.bs.modal', function () {
            tick();
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
