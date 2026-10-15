<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreaDatosBase;
use Tests\TestCase;

/** Campana del header: insumos en o bajo su mínimo, solo para quien puede ver insumos. */
class NotificacionesTest extends TestCase
{
    use RefreshDatabase, CreaDatosBase;

    public function test_lista_los_insumos_bajo_minimo_y_no_los_demas(): void
    {
        $this->insumo(['nombre' => 'Hilo bajo', 'codigo' => 'HBJ', 'stock_actual' => 2, 'stock_minimo' => 5]);
        $this->insumo(['nombre' => 'Hilo sobrado', 'codigo' => 'HSB', 'stock_actual' => 50, 'stock_minimo' => 5]);

        $this->actingAs($this->admin())->getJson(route('notificaciones.sistema'))
            ->assertOk()
            ->assertJsonPath('count', 1)
            ->assertJsonPath('items.0.titulo', 'Stock bajo')
            ->assertJsonFragment(['url' => route('movimiento-insumo.alertas')])
            ->assertSee('Hilo bajo')
            ->assertDontSee('Hilo sobrado');
    }

    public function test_sin_permiso_de_insumos_no_revela_nada(): void
    {
        $this->insumo(['nombre' => 'Hilo bajo', 'codigo' => 'HBJ', 'stock_actual' => 2, 'stock_minimo' => 5]);

        $this->actingAs($this->usuarioSinPermisos())->getJson(route('notificaciones.sistema'))
            ->assertOk()
            ->assertExactJson(['count' => 0, 'items' => []]);
    }
}
