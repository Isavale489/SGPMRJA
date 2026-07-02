<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Los roles de sistema (Administrador, Supervisor) nacieron antes del panel de
 * seguridad y quedaron sin descripción. Se les da una de fábrica SOLO si siguen
 * en NULL (si alguien ya escribió una, se respeta). Desde el panel se puede
 * editar: los roles de sistema admiten cambiar la descripción (no el nombre).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('rol')
            ->where('nombre', 'Administrador')
            ->whereNull('descripcion')
            ->update(['descripcion' => 'Acceso total a todos los módulos del sistema, incluida la configuración y la gestión de usuarios.']);

        DB::table('rol')
            ->where('nombre', 'Supervisor')
            ->whereNull('descripcion')
            ->update(['descripcion' => 'Supervisa la operación diaria: cotizaciones, pedidos, órdenes de producción, control de calidad e inventario.']);
    }

    public function down(): void
    {
        // Texto informativo: no se revierte (podría pisar ediciones manuales).
    }
};
