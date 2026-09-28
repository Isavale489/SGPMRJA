<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Cotizacion;
use App\Models\Empleado;
use App\Models\Genero;
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

        // Persona que ya es empleado (y aún no cliente): alta de cliente reutilizando la persona.
        $personaEmpleado = Persona::create(['nombre' => 'Ana Pérez', 'tipo_documento' => 'V-', 'documento_identidad' => '15000001', 'email' => 'ana.perez@atlantico.test']);
        Empleado::forceCreate(['persona_id' => $personaEmpleado->id, 'codigo_empleado' => 'EMP-E2E', 'fecha_ingreso' => today()->subYear()->toDateString()]);

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
