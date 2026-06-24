<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * FEAT-006 — Otorga los permisos del módulo Control de Calidad al rol Supervisor.
 *
 * El registry (config/modulos.php) ya mapea las rutas calidad.* a 'calidad.ver'
 * y 'calidad.inspeccionar'; aquí se materializan esas claves en `permiso_rol`
 * para el Supervisor (el Administrador entra por Gate::before, no necesita fila).
 * Mismo patrón que 2026_06_15_000003_migrate_user_role_enum_to_role_id.
 */
return new class extends Migration
{
    private const PERMISOS_CALIDAD = [
        'calidad.ver',
        'calidad.inspeccionar',
    ];

    public function up(): void
    {
        $supervisorId = DB::table('rol')->where('nombre', 'Supervisor')->value('id');
        if (!$supervisorId) {
            return;
        }

        foreach (self::PERMISOS_CALIDAD as $permiso) {
            DB::table('permiso_rol')->updateOrInsert(
                ['rol_id' => $supervisorId, 'permiso' => $permiso],
                []
            );
        }
    }

    public function down(): void
    {
        $supervisorId = DB::table('rol')->where('nombre', 'Supervisor')->value('id');
        if (!$supervisorId) {
            return;
        }

        DB::table('permiso_rol')
            ->where('rol_id', $supervisorId)
            ->whereIn('permiso', self::PERMISOS_CALIDAD)
            ->delete();
    }
};
