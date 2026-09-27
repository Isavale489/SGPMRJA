<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Permisos de PARIDAD del Supervisor (FEAT-005).
     *
     * Snapshot del acceso que el Supervisor tiene HOY vía el grupo
     * `role:Administrador,Supervisor` de routes/web.php. Tras retirar ese grupo
     * (TASK-038), estos permisos le mantienen exactamente el mismo acceso.
     *
     * CONTRATO DE VOCABULARIO: estas claves 'modulo.accion' son la fuente de
     * verdad que `config/modulos.php` (TASK-037) DEBE reflejar. Si el registry
     * usa otras claves, la paridad se rompe (QA paso 9 / TASK-041).
     *
     * Granularidad (spec §8): fina en transaccionales (procesar/anular/clonar/
     * pdf/avance...), gruesa (ver/gestionar) en maestros.
     */
    private const PERMISOS_SUPERVISOR = [
        // Transaccionales — pedidos: solo lectura (la escritura es admin-only)
        'pedidos.ver',
        'pedidos.pdf',
        // Cotizaciones: lectura + conversión a pedido (la escritura es admin-only)
        'cotizaciones.ver',
        'cotizaciones.pdf',
        'cotizaciones.convertir',
        // Proveedores: solo lectura (la escritura es admin-only)
        'proveedores.ver',
        // Maestros con CRUD compartido (ver + gestionar)
        'productos.ver',      'productos.gestionar',
        'tipo-productos.ver', 'tipo-productos.gestionar',
        'atributos.ver',      'atributos.gestionar',
        'colores.ver',        'colores.gestionar',
        'insumos.ver',        'insumos.gestionar',
        'tipo-insumos.ver',   'tipo-insumos.gestionar',
        'logos.ver',
        'tallas.ver',
        // Órdenes de producción (transaccional, acciones finas)
        'ordenes.ver',
        'ordenes.gestionar',
        'ordenes.avance',
        'ordenes.cancelar',
        'ordenes.pdf',
        // Compras (ejemplo canónico del spec §2)
        'compras.ver',
        'compras.gestionar',
        'compras.procesar',
        'compras.anular',
        'compras.clonar',
        'compras.pdf',
        // Movimientos de insumo (ver + registrar)
        'movimiento-insumo.ver',
        'movimiento-insumo.gestionar',
        // Reportes — módulo único (spec §8)
        'reportes.ver',
    ];

    public function up(): void
    {
        $now = now();

        // a. Sembrar roles de sistema (idempotente para migrate:fresh y BD existente)
        foreach (['Administrador', 'Supervisor'] as $nombre) {
            DB::table('rol')->updateOrInsert(
                ['nombre' => $nombre],
                ['es_sistema' => 1, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        // b. Añadir role_id (FK nullable a rol)
        if (! Schema::hasColumn('user', 'role_id')) {
            Schema::table('user', function (Blueprint $table) {
                $table->foreignId('role_id')->nullable()->after('role')->constrained('rol');
            });
        }

        // c. Backfill: role_id desde el ENUM role (por nombre)
        DB::statement('UPDATE `user` u JOIN `rol` r ON r.nombre = u.role SET u.role_id = r.id');

        // Salvaguarda: abortar antes de dropear el ENUM si algún usuario no mapeó
        $huerfanos = DB::table('user')->whereNull('role_id')->count();
        if ($huerfanos > 0) {
            throw new \RuntimeException(
                "Backfill incompleto: {$huerfanos} usuario(s) sin role_id. " .
                'Abortando antes de dropear el ENUM role para no perder datos.'
            );
        }

        // d. role_id NOT NULL (la FK creada en (b) se conserva)
        Schema::table('user', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id')->nullable(false)->change();
        });

        // e. Dropear la columna ENUM role
        if (Schema::hasColumn('user', 'role')) {
            Schema::table('user', function (Blueprint $table) {
                $table->dropColumn('role');
            });
        }

        // f. Seed de paridad: permisos del Supervisor = su acceso actual.
        //    Administrador NO recibe filas (bypass por Gate::before).
        $supervisorId = DB::table('rol')->where('nombre', 'Supervisor')->value('id');
        if ($supervisorId) {
            foreach (self::PERMISOS_SUPERVISOR as $permiso) {
                DB::table('permiso_rol')->updateOrInsert(
                    ['rol_id' => $supervisorId, 'permiso' => $permiso],
                    ['created_at' => $now, 'updated_at' => $now]
                );
            }
        }
    }

    public function down(): void
    {
        // Recrear la columna ENUM role (SQL crudo: el schema builder de Laravel
        // degrada enum→varchar en MariaDB al usar change(); ALTER nativo garantiza
        // un ENUM real e idéntico en MySQL 8 y MariaDB).
        if (! Schema::hasColumn('user', 'role')) {
            DB::statement("ALTER TABLE `user` ADD `role` ENUM('Administrador','Supervisor') NULL AFTER `id`");
        }

        // Backfill inverso: role desde role_id (por nombre)
        DB::statement('UPDATE `user` u JOIN `rol` r ON r.id = u.role_id SET u.role = r.nombre');

        // role NOT NULL de vuelta
        DB::statement("ALTER TABLE `user` MODIFY `role` ENUM('Administrador','Supervisor') NOT NULL");

        // Dropear FK + columna role_id
        if (Schema::hasColumn('user', 'role_id')) {
            Schema::table('user', function (Blueprint $table) {
                $table->dropForeign(['role_id']);
                $table->dropColumn('role_id');
            });
        }

        // Limpiar lo sembrado por esta migración (las tablas se dropean en las
        // migraciones 000002/000001 al continuar el rollback)
        $supervisorId = DB::table('rol')->where('nombre', 'Supervisor')->value('id');
        if ($supervisorId) {
            DB::table('permiso_rol')->where('rol_id', $supervisorId)->delete();
        }
        DB::table('rol')->whereIn('nombre', ['Administrador', 'Supervisor'])->delete();
    }
};
