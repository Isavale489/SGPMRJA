<?php

namespace Database\Seeders;

use App\Models\Atributo;
use App\Models\Insumo;
use App\Models\Cliente;
use App\Models\Cotizacion;
use App\Models\Empleado;
use App\Models\Genero;
use App\Models\Pedido;
use App\Models\DetallePedido;
use App\Models\OrdenProduccion;
use App\Models\Persona;
use App\Models\Rol;
use App\Models\Talla;
use App\Models\TasaCambio;
use App\Models\TipoProducto;
use App\Models\User;
use App\Models\UserRecoveryQuestion;
use App\Services\CotizacionService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Datos conocidos para el smoke E2E (tests/e2e). SOLO para la BD
 * sistema_atlantico_e2e: lo ejecuta tests/e2e/servidor.sh tras migrate:fresh.
 *
 * Credenciales fijas en tests/e2e/datos.ts — si cambias algo aquí, cámbialo allá.
 */
class E2eSeeder extends Seeder
{
    public const EMAIL = 'e2e@atlantico.test';
    public const PASSWORD = 'E2e-Clave1!';

    public function run(): void
    {
        $this->call([
            ImpuestoSeeder::class,
            ColorSeeder::class,
            TallaSeeder::class,
            GeneroSeeder::class,
            BordadoUbicacionSeeder::class,
        ]);

        $admin = User::forceCreate([
            'name' => 'Admin E2E',
            'email' => self::EMAIL,
            'password' => Hash::make(self::PASSWORD),
            'email_verified_at' => now(),
            'role_id' => Rol::where('nombre', 'Administrador')->value('id'),
            'estado' => 1,
        ]);
        foreach ([1, 2, 3] as $orden) {
            UserRecoveryQuestion::create([
                'user_id' => $admin->id,
                'pregunta_id' => $orden,
                'respuesta' => Hash::make('respuesta'),
                'orden' => $orden,
            ]);
        }

        // Tasa del día: evita que el layout consulte la API del BCV.
        TasaCambio::create(['moneda' => 'USD', 'valor' => 40, 'fecha_bcv' => today(), 'fuente' => 'e2e']);

        $persona = Persona::create([
            'nombre' => 'Confecciones Portuguesa',
            'tipo_documento' => 'V-',
            'documento_identidad' => '12345678',
        ]);
        $cliente = Cliente::forceCreate(['persona_id' => $persona->id, 'tipo_cliente' => 'natural', 'estatus' => 1]);
        $tipo = TipoProducto::forceCreate(['nombre' => 'Chemise', 'prefijo' => 'CHE']);

        // Catálogo para el formulario de tipos (tests/e2e/productos.spec.ts).
        // Nombres distintos de los que crea tests/e2e/atributos.spec.ts (Manga/MNG).
        $bolsillo = Atributo::create(['nombre' => 'Bolsillo', 'codigo' => 'BLS']);
        $bolsillo->valores()->create(['nombre' => 'Con bolsillo', 'codigo' => 'CB', 'orden' => 1]);
        $bolsillo->valores()->create(['nombre' => 'Sin bolsillo', 'codigo' => 'SB', 'orden' => 2]);
        Insumo::create(['nombre' => 'Oxford E2E', 'codigo' => 'OXE', 'tipo' => 'Tela', 'unidad_medida' => 'Metro', 'is_inventoriable' => 1, 'costo_unitario' => 4, 'stock_actual' => 100, 'stock_minimo' => 5, 'estado' => 1]);

        // Persona que ya es empleado (y aún no cliente): alta de cliente reutilizando la persona.
        $personaEmpleado = Persona::create(['nombre' => 'Ana Pérez', 'tipo_documento' => 'V-', 'documento_identidad' => '15000001', 'email' => 'ana.perez@atlantico.test']);
        Empleado::forceCreate(['persona_id' => $personaEmpleado->id, 'codigo_empleado' => 'EMP-E2E', 'fecha_ingreso' => today()->subYear()->toDateString()]);

        // Orden finalizada con equipo de 2, pendiente de calidad (tests/e2e/calidad.spec.ts).
        // Cliente propio: el pedido no debe aparecer en las filas que buscan otros E2E.
        $personaQc = Persona::create(['nombre' => 'Uniformes Araure QC', 'tipo_documento' => 'J-', 'documento_identidad' => '41000001']);
        $clienteQc = Cliente::forceCreate(['persona_id' => $personaQc->id, 'tipo_cliente' => 'juridico', 'estatus' => 1]);
        $pedidoQc = Pedido::forceCreate(['cliente_id' => $clienteQc->id, 'fecha_pedido' => today()->toDateString(), 'total' => 150, 'abono' => 150, 'prioridad' => 'Normal', 'estado' => 'Procesando', 'user_id' => $admin->id]);
        $lineaQc = DetallePedido::forceCreate(['pedido_id' => $pedidoQc->id, 'tipo_producto_id' => $tipo->id, 'cantidad' => 10, 'precio_unitario' => 15, 'genero_id' => Genero::query()->value('id')]);
        $equipoQc = collect([['Marta Colmenares', '16000001', 'EMP-QC1', 6], ['Julio Arráiz', '16000002', 'EMP-QC2', 4]])->map(function ($e) {
            $p = Persona::create(['nombre' => $e[0], 'tipo_documento' => 'V-', 'documento_identidad' => $e[1]]);
            return [Empleado::forceCreate(['persona_id' => $p->id, 'codigo_empleado' => $e[2], 'fecha_ingreso' => today()->subYear()->toDateString()]), $e[3]];
        });
        $ordenQc = OrdenProduccion::forceCreate([
            'pedido_id' => $pedidoQc->id, 'detalle_pedido_id' => $lineaQc->id, 'empleado_id' => $equipoQc[0][0]->id,
            'cantidad_solicitada' => 10, 'cantidad_producida' => 10, 'estado' => 'Finalizado',
            'fecha_inicio' => today()->subDays(5)->toDateString(), 'fecha_fin_estimada' => today()->toDateString(),
            'fecha_fin_real' => today()->toDateString(), 'created_by' => $admin->id,
        ]);
        foreach ($equipoQc as [$empleado, $cantidad]) {
            $ordenQc->empleadosAsignados()->attach($empleado->id, ['cantidad' => $cantidad, 'cantidad_producida' => $cantidad, 'cantidad_defectuosa' => 0]);
        }
        // OrdenProduccionObserver fija el estado inicial según el pedido (una orden nace
        // 'Pendiente'); se lleva a 'Finalizado' después, como si ya se hubiera producido.
        OrdenProduccion::whereKey($ordenQc->id)->update(['estado' => 'Finalizado']);

        // Cotización Aprobada lista para convertir: pasa por el service real
        // (snapshots, SKU, totales), no por inserts a mano.
        Auth::login($admin);
        $cotizacion = app(CotizacionService::class)->crear([
            'cliente_id' => $cliente->id,
            'fecha_cotizacion' => today()->toDateString(),
            'fecha_validez' => today()->addDays(15)->toDateString(),
            'productos' => [[
                'tipo_producto_id' => $tipo->id,
                'talla_id' => Talla::where('nombre', 'M')->value('id'),
                'genero_id' => Genero::query()->value('id'),
                'cantidad' => 12,
                'precio_unitario' => 15,
                'lleva_bordado' => false,
            ]],
        ]);
        $cotizacion->update(['estado' => 'Aprobada']);
        Auth::logout();
    }
}
