<?php

namespace Tests\Feature;

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\Cotizacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreaDatosBase;
use Tests\TestCase;

/**
 * Páginas Inertia de Cotizaciones: listado con «Ver» por recarga parcial y el
 * asistente (crear/editar) en su propia página. Las claves de cada prop se
 * comparan con las interfaces de resources/js/pages/Cotizaciones/tipos.ts.
 */
class CotizacionesPaginaTest extends TestCase
{
    use RefreshDatabase, CreaDatosBase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake();
    }

    private function clavesTs(string $interfaz): array
    {
        $ts = file_get_contents(resource_path('js/pages/Cotizaciones/tipos.ts'));
        preg_match("/export interface {$interfaz}(?: extends (\\w+))? \\{(.*?)\\n\\}/s", $ts, $m);
        preg_match_all('/^    (\w+)\??:/m', $m[2] ?? '', $claves);
        $this->assertNotEmpty($claves[1], "No se pudo leer {$interfaz}");

        // `extends`: también las claves de la interfaz base.
        return ! empty($m[1]) ? array_merge($this->clavesTs($m[1]), $claves[1]) : $claves[1];
    }

    private function inertia(): array
    {
        return ['X-Inertia' => 'true', 'X-Inertia-Version' => (string) app(HandleInertiaRequests::class)->version(request()), 'X-Requested-With' => 'XMLHttpRequest'];
    }

    private function cotizacion($admin, array $extra = []): Cotizacion
    {
        $this->assertExito($this->actingAs($admin)->postJson(route('cotizaciones.store'), array_merge($this->payloadCotizacion($this->cliente()->id), $extra)));

        return Cotizacion::latest('id')->firstOrFail();
    }

    public function test_el_listado_y_el_ver_cumplen_el_contrato(): void
    {
        $admin = $this->admin();
        $c = $this->cotizacion($admin);

        $this->actingAs($admin)->get(route('cotizaciones.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $p) => $p->component('Cotizaciones/Index')
                ->has('registros.data', 1, fn (Assert $f) => $f->hasAll($this->clavesTs('CotizacionFila')))
                ->where('detalle', null));

        $this->actingAs($admin)->get(route('cotizaciones.index', ['ver' => $c->id]))
            ->assertInertia(fn (Assert $p) => $p
                ->has('detalle', fn (Assert $d) => $d->hasAll($this->clavesTs('CotizacionDetalle'))->etc())
                ->has('detalle.grupos.0', fn (Assert $g) => $g->hasAll($this->clavesTs('GrupoCotizacion')))
                ->has('detalle.cliente_datos', fn (Assert $x) => $x->hasAll($this->clavesTs('ClienteCotizacion')))
                ->where('detalle.id', $c->id));
    }

    public function test_el_asistente_de_alta_y_edicion_cumple_el_contrato(): void
    {
        $admin = $this->admin();
        $c = $this->cotizacion($admin); // deja un tipo de producto en el catálogo
        $this->actingAs($admin)->get(route('cotizaciones.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $p) => $p->component('Cotizaciones/Formulario')
                ->hasAll($this->clavesTs('PaginaFormularioCotizacion'))
                ->where('cotizacion', null)
                ->has('catalogo.0', fn (Assert $t) => $t->hasAll($this->clavesTs('TipoCatalogo'))));

        $this->actingAs($admin)->get(route('cotizaciones.edit', $c))
            ->assertOk()
            ->assertInertia(fn (Assert $p) => $p->component('Cotizaciones/Formulario')
                ->has('cotizacion', fn (Assert $x) => $x->hasAll($this->clavesTs('CotizacionEditable')))
                ->has('cotizacion.grupos', 1)
                ->where('urls.guardar', route('cotizaciones.update', $c, absolute: false)));
    }

    public function test_guardar_desde_inertia_vuelve_al_listado_con_la_ficha_abierta(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->withHeaders($this->inertia())
            ->post(route('cotizaciones.store'), $this->payloadCotizacion($this->cliente()->id))
            ->assertRedirect(route('cotizaciones.index', ['ver' => Cotizacion::sole()->id]))
            ->assertSessionHas('success');
    }

    public function test_una_cotizacion_que_no_se_edita_no_abre_el_asistente(): void
    {
        $admin = $this->admin();
        $c = $this->cotizacion($admin);
        $c->update(['estado' => 'Convertida']);

        $this->actingAs($admin)->get(route('cotizaciones.edit', $c))->assertRedirect();
    }

    /** Un cliente con estatus 0 (no solo borrado) se marca inhabilitado: así la tabla no ofrece convertir. */
    public function test_el_cliente_inhabilitado_por_estatus_se_marca_en_la_tabla(): void
    {
        $admin = $this->admin();
        $c = $this->cotizacion($admin);
        $c->cliente->update(['estatus' => 0]);

        $this->actingAs($admin)->get(route('cotizaciones.index'))
            ->assertInertia(fn (Assert $p) => $p->where('registros.data.0.cliente_inhabilitado', true));
    }

    public function test_sin_permiso_no_se_entra(): void
    {
        $sin = $this->usuarioSinPermisos();
        $this->actingAs($sin)->get(route('cotizaciones.index'))->assertForbidden();
        $this->actingAs($sin)->get(route('cotizaciones.create'))->assertForbidden();
    }
}
