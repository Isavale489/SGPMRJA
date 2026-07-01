<?php

namespace App\Services;

use App\Models\ControlCalidad;
use App\Models\OrdenProduccion;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * FEAT-006 — Lógica de Control de Calidad.
 *
 * Registra la inspección de una Orden de Producción finalizada y, si hay
 * unidades defectuosas, dispara el reproceso (la orden vuelve a producción).
 *
 * Invariante: NO toca stock. El consumo extra por reproceso ocurre por la vía
 * normal de producción (registrarAvance → DetalleOrdenInsumo). Ver
 * docs/conventions/business-flows.md.
 */
class ControlCalidadService
{
    /**
     * Registra una inspección sobre una orden finalizada.
     *
     * @param array $data ['cantidad_inspeccionada','cantidad_aprobada','cantidad_rechazada','resultado','observaciones']
     */
    public function inspeccionar(OrdenProduccion $orden, array $data, int $inspectorId): ControlCalidad
    {
        return DB::transaction(function () use ($orden, $data, $inspectorId) {
            // Lock + re-chequeo de estado dentro de la transacción (evita carrera
            // con producción o doble inspección simultánea).
            $orden = OrdenProduccion::with('empleadosAsignados.persona')
                ->lockForUpdate()->findOrFail($orden->id);

            if ($orden->estado !== 'Finalizado') {
                throw ValidationException::withMessages([
                    'orden' => "La orden está en estado \"{$orden->estado}\": solo se inspeccionan órdenes finalizadas.",
                ]);
            }

            $inspeccionada = (int) $data['cantidad_inspeccionada'];
            $aprobada      = (int) $data['cantidad_aprobada'];
            $rechazada     = (int) $data['cantidad_rechazada'];

            // Defensa en profundidad (el Request ya valida; el service no confía ciegamente).
            if ($aprobada + $rechazada !== $inspeccionada) {
                throw ValidationException::withMessages([
                    'cantidad_inspeccionada' => 'Las cantidades no cuadran: aprobadas + rechazadas debe igualar inspeccionadas.',
                ]);
            }
            if ($inspeccionada > $orden->cantidad_producida) {
                throw ValidationException::withMessages([
                    'cantidad_inspeccionada' => 'No puedes inspeccionar más unidades de las producidas.',
                ]);
            }

            // El veredicto: si hay defectuosas es 'rechazado'; si no, se respeta
            // 'aprobado' u 'observado' (conforme con notas) que venga del form.
            $resultado = $rechazada > 0
                ? 'rechazado'
                : (($data['resultado'] ?? 'aprobado') === 'observado' ? 'observado' : 'aprobado');

            $inspeccion = ControlCalidad::create([
                'orden_produccion_id'    => $orden->id,
                'inspector_id'           => $inspectorId,
                'cantidad_inspeccionada' => $inspeccionada,
                'cantidad_aprobada'      => $aprobada,
                'cantidad_rechazada'     => $rechazada,
                'resultado'              => $resultado,
                'observaciones'          => $data['observaciones'] ?? null,
                'fecha_inspeccion'       => now(),
            ]);

            // cantidad_defectuosa acumula histórico (decisión #3).
            $orden->cantidad_defectuosa += $rechazada;

            if ($rechazada > 0) {
                // Atribución del rechazo por empleado (mantiene el invariante
                // orden == suma por empleado). Con un solo empleado es automático.
                $this->atribuirRechazo($orden, $rechazada, $data['rechazos'] ?? []);

                // Reproceso: las N defectuosas deben rehacerse. Bajar lo producido
                // reabre la orden a "En Proceso"; registrarAvance la deja recibir
                // avance hasta volver a completar y re-finalizar.
                $orden->cantidad_producida = max(0, $orden->cantidad_producida - $rechazada);
                $orden->estado = 'En Proceso';
                $orden->fecha_fin_real = null;
            }

            $orden->save();

            return $inspeccion;
        });
    }

    /**
     * Reparte las N unidades rechazadas entre el equipo de la orden, bajando lo
     * producido de cada empleado y subiendo su defectuosa (en el pivot). Con un
     * solo empleado la atribución es automática. Con equipo (2+) exige el detalle
     * `$rechazos` = [{empleado_id, cantidad}] que cuadre y respete topes.
     *
     * @throws ValidationException
     */
    private function atribuirRechazo(OrdenProduccion $orden, int $rechazada, array $rechazos): void
    {
        $equipo = $orden->empleadosAsignados;
        if ($equipo->isEmpty()) {
            return; // orden sin equipo registrado: solo afecta totales de la orden
        }

        // Normaliza a [empleadoId => cantidad].
        $mapa = [];
        if ($equipo->count() === 1) {
            $mapa[$equipo->first()->id] = $rechazada;
        } else {
            foreach ($rechazos as $r) {
                $id = (int) ($r['empleado_id'] ?? 0);
                $cant = (int) ($r['cantidad'] ?? 0);
                if ($cant > 0) {
                    $mapa[$id] = ($mapa[$id] ?? 0) + $cant;
                }
            }
            if (array_sum($mapa) !== $rechazada) {
                throw ValidationException::withMessages([
                    'rechazos' => 'La atribución por empleado debe sumar exactamente las unidades rechazadas.',
                ]);
            }
        }

        foreach ($mapa as $empleadoId => $cant) {
            $miembro = $equipo->firstWhere('id', $empleadoId);
            if (!$miembro) {
                throw ValidationException::withMessages([
                    'rechazos' => 'Un empleado de la atribución no pertenece al equipo de la orden.',
                ]);
            }
            if ($cant > (int) $miembro->pivot->cantidad_producida) {
                $nombre = $miembro->persona->nombre ?? ('empleado #' . $miembro->id);
                throw ValidationException::withMessages([
                    'rechazos' => "No puedes rechazar más unidades de las que {$nombre} produjo.",
                ]);
            }
            $orden->empleadosAsignados()->updateExistingPivot($empleadoId, [
                'cantidad_producida'  => (int) $miembro->pivot->cantidad_producida - $cant,
                'cantidad_defectuosa' => (int) $miembro->pivot->cantidad_defectuosa + $cant,
            ]);
        }
    }
}
