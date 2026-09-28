<?php

namespace Tests\Feature;

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\Compra;
use App\Models\Persona;
use App\Models\PermisoRol;
use App\Models\Proveedor;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Support\SessionKey;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreaDatosBase;
use Tests\TestCase;

/**
 * Páginas Inertia de Compras: listado (activas, anuladas, existencias) con el
 * detalle por recarga parcial, el formulario en su propia página y el alta
 * rápida de proveedores e insumos que devuelve el registro por flash.
 */
class ComprasPaginaTest extends TestCase
{
    use RefreshDatabase, CreaDatosBase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake();
    }

    private function clavesTs(string $interfaz, string $archivo = 'js/pages/Compras/tipos.ts'): array
    {
        $ts = file_get_contents(resource_path($archivo));
        preg_match("/export interface {$interfaz} \\{(.*?)\\n\\}/s", $ts, $m);
        preg_match_all('/^    (\w+)\??:/m', $m[1] ?? '', $claves);
        $this->assertNotEmpty($claves[1], "No se pudo leer {$interfaz}");

        return $claves[1];
    }

    private function inertia(): array
    {
        return ['X-Inertia' => 'true', 'X-Inertia-Version' => (string) app(HandleInertiaRequests::class)->version(request()), 'X-Requested-With' => 'XMLHttpRequest'];
    }

    private function borrador(User $admin, array $extra = []): Compra
    {
        $this->actingAs($admin)->postJson(route('compras.store'), array_merge([
            'proveedor_id' => $this->proveedor()->id,
            'numero_factura' => '0001-0456',
            'fecha_compra' => now()->toDateString(),
            'tasa_cambio' => 40,
            'items' => [['insumo_id' => $this->insumo(['stock_actual' => 5])->id, 'cantidad' => 10, 'costo_unitario_bs' => 80, 'aplica_iva' => 1]],
        ], $extra));

        return Compra::latest('id')->firstOrFail();
    }

    public function test_las_filas_y_el_detalle_cumplen_el_contrato(): void
    {
        $admin = $this->admin();
        $c = $this->borrador($admin);

        $this->actingAs($admin)->get(route('compras.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $p) => $p
                ->component('Compras/Index')
                ->where('vista', 'activas')
                ->has('compras.data', 1, fn (Assert $f) => $f->hasAll($this->clavesTs('CompraFila')))
                ->where('compras.data.0.total', 23.2) // 10 × $2 + IVA 16 %
                ->where('compras.data.0.total_bs', 928) // 10 × Bs 80 + IVA 16 %
                ->where('detalle', null)
                ->where('existencias', null));

        $this->actingAs($admin)->get(route('compras.index', ['ver' => $c->id]))
            ->assertInertia(fn (Assert $p) => $p
                ->has('detalle', fn (Assert $d) => $d->hasAll($this->clavesTs('CompraDetalle')))
                ->where('detalle.id', $c->id)
                ->where('detalle.items.0.costo_bs', 80)
                ->where('detalle.iva_bs', 128));
    }

    public function test_anuladas_y_existencias_son_vistas_aparte(): void
    {
        $admin = $this->admin();
        $c = $this->borrador($admin);
        $this->actingAs($admin)->patchJson(route('compras.procesar', $c));
        $this->actingAs($admin)->patchJson(route('compras.anular', $c));

        $this->actingAs($admin)->get(route('compras.index'))->assertInertia(fn (Assert $p) => $p->has('compras.data', 0));
        $this->actingAs($admin)->get(route('compras.index', ['vista' => 'anuladas']))
            ->assertInertia(fn (Assert $p) => $p->where('vista', 'anuladas')->has('compras.data', 1)->where('compras.data.0.anulado_por', $admin->name));
        // Enlace viejo de la píldora «Anuladas».
        $this->actingAs($admin)->get('/compras?anuladas=1')->assertInertia(fn (Assert $p) => $p->where('vista', 'anuladas'));

        $this->actingAs($admin)->get(route('compras.index', ['vista' => 'existencias']))
            ->assertInertia(fn (Assert $p) => $p
                ->where('compras', null)
                ->has('existencias.data', 1, fn (Assert $f) => $f->hasAll($this->clavesTs('ExistenciaFila', 'js/components/app/tabla-existencias.tsx'))));
    }

    public function test_filtra_por_estado_proveedor_y_busqueda(): void
    {
        $admin = $this->admin();
        $c = $this->borrador($admin);
        $this->borrador($admin, ['numero_factura' => '0002-0001']);
        $this->actingAs($admin)->patchJson(route('compras.procesar', $c));

        $this->actingAs($admin)->get(route('compras.index', ['estado' => 'recibida']))->assertInertia(fn (Assert $p) => $p->has('compras.data', 1)->where('compras.data.0.id', $c->id));
        $this->actingAs($admin)->get(route('compras.index', ['proveedor' => $c->proveedor_id]))->assertInertia(fn (Assert $p) => $p->has('compras.data', 1));
        $this->actingAs($admin)->get(route('compras.index', ['buscar' => '0002']))->assertInertia(fn (Assert $p) => $p->has('compras.data', 1));
        $this->actingAs($admin)->get(route('compras.index', ['buscar' => 'Textiles del Llano']))->assertInertia(fn (Assert $p) => $p->has('compras.data', 2));
    }

    public function test_el_formulario_de_alta_y_el_de_edicion(): void
    {
        $admin = $this->admin();
        $c = $this->borrador($admin);
        $this->insumo(['nombre' => 'Servicio de corte', 'is_inventoriable' => 0]);

        $this->actingAs($admin)->get(route('compras.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $p) => $p
                ->component('Compras/Formulario')
                ->where('compra', null)
                ->has('insumos', 1, fn (Assert $i) => $i->hasAll($this->clavesTs('InsumoComprable')))
                ->where('iva', 16)
                ->where('urls.guardar', route('compras.store', absolute: false)));

        $this->actingAs($admin)->get(route('compras.edit', $c))
            ->assertInertia(fn (Assert $p) => $p
                ->has('compra', fn (Assert $x) => $x->hasAll($this->clavesTs('CompraEditable')))
                ->where('compra.items.0.costo_unitario_bs', 80)
                ->where('urls.guardar', route('compras.update', $c, absolute: false)));

        // La entrada vieja «Crear compra con faltantes» lleva al formulario.
        $this->actingAs($admin)->get('/compras?prefill=1')->assertRedirect(route('compras.create', ['prefill' => 1]));
    }

    public function test_una_compra_recibida_no_abre_el_formulario(): void
    {
        $admin = $this->admin();
        $c = $this->borrador($admin);
        $this->actingAs($admin)->patchJson(route('compras.procesar', $c));

        $this->actingAs($admin)->get(route('compras.edit', $c))
            ->assertRedirect(route('compras.index'))
            ->assertSessionHas('error', 'Solo se pueden editar borradores.');
    }

    public function test_guardar_desde_inertia_vuelve_al_listado_y_los_errores_van_a_cada_linea(): void
    {
        $admin = $this->admin();
        $proveedor = $this->proveedor();
        $insumo = $this->insumo();
        $cuerpo = ['proveedor_id' => $proveedor->id, 'fecha_compra' => now()->toDateString(), 'tasa_cambio' => 40, 'items' => [['insumo_id' => $insumo->id, 'cantidad' => 2, 'costo_unitario_bs' => 0, 'aplica_iva' => 1]]];

        $this->actingAs($admin)->from(route('compras.create'))->withHeaders($this->inertia())
            ->post(route('compras.store'), $cuerpo)
            ->assertRedirect(route('compras.create'))
            ->assertSessionHasErrors('items.0.costo_unitario_bs');

        $cuerpo['items'][0]['costo_unitario_bs'] = 50;
        $this->actingAs($admin)->from(route('compras.create'))->withHeaders($this->inertia())
            ->post(route('compras.store'), $cuerpo)
            ->assertRedirect(route('compras.index'))
            ->assertSessionHas('success');

        $this->assertSame('borrador', Compra::sole()->estado);
    }

    public function test_las_acciones_desde_inertia_avisan_con_flash(): void
    {
        $admin = $this->admin();
        $c = $this->borrador($admin);
        $volver = route('compras.index', ['ver' => $c->id]);

        $this->actingAs($admin)->from($volver)->withHeaders($this->inertia())->patch(route('compras.procesar', $c))
            ->assertRedirect($volver)->assertSessionHas('success', "Compra #{$c->id} procesada. Stock de insumos actualizado.");

        $c->detalles()->first()->insumo->update(['stock_actual' => 1]); // se consumió
        $this->actingAs($admin)->from($volver)->withHeaders($this->inertia())->patch(route('compras.anular', $c))
            ->assertRedirect($volver)->assertSessionHas('error');
        $this->assertSame('recibida', $c->fresh()->estado);
    }

    public function test_el_alta_rapida_devuelve_el_proveedor_y_el_insumo_por_flash(): void
    {
        $admin = $this->admin();
        $inertia = fn () => $this->actingAs($admin)->from(route('compras.create'))->withHeaders($this->inertia());

        $inertia()->post(route('proveedores.store'), [
            'tipo_proveedor' => 'juridico', 'rif' => 'J-40111222', 'razon_social' => 'Hilos Guanare C.A.',
            'email' => 'ventas@hilosguanare.test', 'direccion' => 'Zona industrial', 'telefonos' => [['numero' => '0414-1234567', 'tipo' => 'movil', 'es_principal' => true]],
        ])->assertRedirect(route('compras.create'))
            ->assertSessionHas(SessionKey::FLASH_DATA, fn ($f) => ($f['proveedor']['nombre'] ?? null) === 'Hilos Guanare C.A.');

        $inertia()->post(route('insumos.store'), [
            'nombre' => 'Botón nácar', 'tipo' => 'Botón', 'unidad_medida' => 'Unidad', 'costo_unitario' => 0.5,
            'aplica_iva' => true, 'is_inventoriable' => true, 'stock_minimo' => 0, 'stock_actual' => 0, 'stock_maximo' => 0,
        ])->assertSessionHas(SessionKey::FLASH_DATA, fn ($f) => ($f['insumo']['nombre'] ?? null) === 'Botón nácar' && $f['insumo']['inventariable'] === true);

        // Persona ya registrada (p. ej. cliente) que pasa a ser proveedor sin duplicarse.
        $persona = Persona::create(['nombre' => 'Confecciones Turén', 'tipo_documento' => 'J-', 'documento_identidad' => '40999888']);
        $inertia()->post(url("/proveedores/from-persona/{$persona->id}"))
            ->assertSessionHas(SessionKey::FLASH_DATA, fn ($f) => ($f['proveedor']['nombre'] ?? null) === 'Confecciones Turén');
        $this->assertSame(1, Proveedor::where('persona_id', $persona->id)->count());
    }

    public function test_ver_no_es_gestionar(): void
    {
        $rol = Rol::create(['nombre' => 'Consulta compras', 'es_sistema' => false]);
        PermisoRol::create(['rol_id' => $rol->id, 'permiso' => 'compras.ver']);
        $u = User::factory()->create(['role_id' => $rol->id]);

        $this->actingAs($u)->get(route('compras.index', ['vista' => 'existencias']))->assertOk();
        $this->actingAs($u)->get(route('compras.create'))->assertForbidden();
    }
}
