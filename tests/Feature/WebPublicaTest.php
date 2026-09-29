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
}
