<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Brecha B — reparto por empleado dentro de una orden de equipo.
 *
 * Añade al pivot orden_produccion_empleado el desglose por persona:
 *   · cantidad            → unidades ASIGNADAS a ese empleado en la orden
 *   · cantidad_producida  → unidades que ESE empleado ya produjo (avances)
 *   · cantidad_defectuosa → defectuosas atribuidas a ese empleado (avance + CC)
 *
 * Invariante: la suma por empleado iguala los totales de la orden.
 * Backfill: las órdenes previas reparten sus totales en partes iguales entre
 * su equipo (el resto a los primeros), de modo que el invariante se cumple
 * también para el histórico.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orden_produccion_empleado', function (Blueprint $table) {
            $table->unsignedSmallInteger('cantidad')->nullable()->after('empleado_id');
            $table->unsignedSmallInteger('cantidad_producida')->default(0)->after('cantidad');
            $table->unsignedSmallInteger('cantidad_defectuosa')->default(0)->after('cantidad_producida');
        });

        // Backfill: repartir los totales de cada orden entre su equipo (equitativo,
        // el residuo a los primeros) para preservar el invariante en el histórico.
        $ordenes = DB::table('orden_produccion')->get(['id', 'cantidad_solicitada', 'cantidad_producida', 'cantidad_defectuosa']);
        foreach ($ordenes as $orden) {
            $pivotIds = DB::table('orden_produccion_empleado')
                ->where('orden_produccion_id', $orden->id)
                ->orderBy('id')
                ->pluck('id')
                ->all();
            $n = count($pivotIds);
            if ($n === 0) {
                continue;
            }
            $repartos = [
                'cantidad'            => $this->repartir((int) $orden->cantidad_solicitada, $n),
                'cantidad_producida'  => $this->repartir((int) $orden->cantidad_producida, $n),
                'cantidad_defectuosa' => $this->repartir((int) $orden->cantidad_defectuosa, $n),
            ];
            foreach ($pivotIds as $i => $pivotId) {
                DB::table('orden_produccion_empleado')->where('id', $pivotId)->update([
                    'cantidad'            => $repartos['cantidad'][$i],
                    'cantidad_producida'  => $repartos['cantidad_producida'][$i],
                    'cantidad_defectuosa' => $repartos['cantidad_defectuosa'][$i],
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('orden_produccion_empleado', function (Blueprint $table) {
            $table->dropColumn(['cantidad', 'cantidad_producida', 'cantidad_defectuosa']);
        });
    }

    /** Reparte $total en $n partes lo más iguales posible; el residuo a los primeros. */
    private function repartir(int $total, int $n): array
    {
        $base = intdiv($total, $n);
        $resto = $total % $n;
        $out = [];
        for ($i = 0; $i < $n; $i++) {
            $out[$i] = $base + ($i < $resto ? 1 : 0);
        }
        return $out;
    }
};
