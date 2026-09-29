<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * La web pública (Blade, fuera del panel) carga sin errores y ya no pide
 * los assets de Breeze que se eliminaron.
 */
class WebPublicaTest extends TestCase
{
    public function test_las_paginas_publicas_cargan(): void
    {
        foreach (['home', 'about', 'contact', 'faq', 'portfolio'] as $ruta) {
            $this->get(route($ruta))
                ->assertOk()
                ->assertDontSee('css/app.css', false)
                ->assertDontSee('js/app.js', false);
        }
    }

    /** Toda imagen, hoja o script local que piden las páginas públicas existe en public/. */
    public function test_los_archivos_locales_que_piden_existen(): void
    {
        $base = rtrim(url('/'), '/');
        foreach (['home', 'about', 'contact', 'faq', 'portfolio'] as $ruta) {
            $html = $this->get(route($ruta))->getContent();
            preg_match_all('/(?:src|href|content)="'.preg_quote($base, '/').'\/([^"?#]+\.(?:jpe?g|png|gif|webp|svg|ico|css|js))"/i', $html, $m);
            foreach (array_unique($m[1]) as $archivo) {
                $this->assertFileExists(public_path($archivo), "La página «{$ruta}» pide {$archivo}, que no existe.");
            }
        }
    }
}
