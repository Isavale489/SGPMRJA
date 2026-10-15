<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Tests\Concerns\CreaDatosBase;
use Tests\TestCase;

/**
 * Barrido: toda ruta GET sin parámetros responde sin 5xx para el administrador.
 * Atrapa lo que el smoke E2E no visita (create, reportes, PDFs, endpoints de
 * datos) — p. ej. el route('x.show', '') que Laravel 13 dejó de aceptar.
 */
class RutasSinParametrosTest extends TestCase
{
    use RefreshDatabase, CreaDatosBase;

    /** Rutas que no se pueden barrer a ciegas (efectos o dependencias externas). */
    private const EXCLUIDAS = ['logout', '_ignition', 'sanctum', 'storage', 'up'];

    public function test_toda_ruta_apunta_a_un_metodo_existente(): void
    {
        $muertas = [];
        foreach (Route::getRoutes() as $ruta) {
            $accion = $ruta->getAction('uses');
            if (! is_string($accion) || ! str_contains($accion, '@')) {
                continue; // closures y controllers invocables
            }
            [$clase, $metodo] = explode('@', $accion);
            if (! method_exists($clase, $metodo)) {
                $muertas[] = implode('|', $ruta->methods())." /{$ruta->uri()} → {$accion}";
            }
        }

        $this->assertSame([], $muertas, "Rutas que apuntan a métodos inexistentes:\n".implode("\n", $muertas));
    }

    public function test_ninguna_ruta_get_sin_parametros_devuelve_5xx(): void
    {
        Http::fake(); // sin llamadas reales (p. ej. API del BCV)
        $admin = $this->admin();
        $fallas = [];
        $barridas = 0;

        foreach (Route::getRoutes() as $ruta) {
            $uri = $ruta->uri();
            if (! in_array('GET', $ruta->methods()) || str_contains($uri, '{')
                || collect(self::EXCLUIDAS)->contains(fn ($x) => str_starts_with(ltrim($uri, '/'), $x))) {
                continue;
            }
            $barridas++;
            $resp = $this->actingAs($admin)->get('/'.ltrim($uri, '/'));
            $status = $resp->getStatusCode();
            if ($status >= 500) {
                $e = $resp->exception;
                $fallas[] = "{$status} /{$uri}".($e ? ' — '.class_basename($e).': '.mb_substr($e->getMessage(), 0, 160) : '');
            }
        }

        $this->assertGreaterThan(50, $barridas, 'El barrido encontró sospechosamente pocas rutas.');
        $this->assertSame([], $fallas, "Rutas con error del servidor:\n".implode("\n", $fallas));
    }
}
