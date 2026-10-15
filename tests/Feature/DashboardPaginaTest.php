<?php

namespace Tests\Feature;

use App\Models\RecoveryAttempt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreaDatosBase;
use Tests\TestCase;

/** Inicio (Inertia): KPIs, gráficos, maestros y el aviso de recuperación de contraseña. */
class DashboardPaginaTest extends TestCase
{
    use RefreshDatabase, CreaDatosBase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake();
    }

    private function clavesTs(): array
    {
        $ts = file_get_contents(resource_path('js/pages/Dashboard.tsx'));
        preg_match('/export interface PaginaDashboard \{(.*?)\n\}/s', $ts, $m);
        preg_match_all('/^    (\w+)\??:/m', $m[1] ?? '', $claves);
        $this->assertNotEmpty($claves[1]);

        return $claves[1];
    }

    public function test_el_tablero_cumple_el_contrato_y_cuenta_bien(): void
    {
        $admin = $this->admin();
        $this->pedidoConLinea(5);
        $this->cliente();
        $this->insumo(['stock_actual' => 1, 'stock_minimo' => 5]);
        // Un servicio (no inventariable) no maneja stock: no es una alerta.
        $this->insumo(['stock_actual' => 0, 'stock_minimo' => 5, 'is_inventoriable' => 0]);

        $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $p) => $p->component('Dashboard')
                ->where('kpis.insumos_alerta', 1)
                ->where('maestros.clientes', 2)
                ->where('pedidos.0', ['estado' => 'Pendiente', 'total' => 1])
                ->has('tendencia', 6)
                ->where('tendencia.5.pedidos', 1)
                ->where('alertaRecuperacion', null)
                ->etc());

        $props = $this->actingAs($admin)->get(route('dashboard'))->viewData('page')['props'];
        foreach ($this->clavesTs() as $clave) {
            $this->assertArrayHasKey($clave, $props);
        }
    }

    public function test_el_aviso_de_recuperacion_se_muestra_una_sola_vez(): void
    {
        $admin = $this->admin();
        RecoveryAttempt::create(['user_id' => $admin->id, 'email' => $admin->email, 'ip' => '10.0.0.7', 'tipo' => 'email', 'resultado' => 'fallo']);

        $this->actingAs($admin)->get(route('dashboard'))
            ->assertInertia(fn (Assert $p) => $p->where('alertaRecuperacion.ip', '10.0.0.7')->where('alertaRecuperacion.resultado', 'fallo'));
        $this->actingAs($admin)->get(route('dashboard'))
            ->assertInertia(fn (Assert $p) => $p->where('alertaRecuperacion', null));
    }

    public function test_cualquier_usuario_autenticado_entra(): void
    {
        $this->actingAs($this->usuarioSinPermisos())->get(route('dashboard'))->assertOk();
    }
}
