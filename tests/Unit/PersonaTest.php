<?php

namespace Tests\Unit;

use App\Models\Persona;
use App\Models\Telefono;
use App\Models\Direccion;
use Tests\TestCase;

class PersonaTest extends TestCase
{
    /** @test */
    public function nombre_completo_devuelve_el_nombre_consolidado()
    {
        // `nombre` ya trae nombre + apellido (o razón social); el accessor solo recorta.
        $persona = new Persona(['nombre' => '  Juan Pérez ']);

        $this->assertEquals('Juan Pérez', $persona->nombre_completo);
    }

    /** @test */
    public function documento_completo_concatena_tipo_y_numero()
    {
        $persona = new Persona([
            'tipo_documento' => 'V-',
            'documento_identidad' => '12345678',
        ]);

        $this->assertEquals('V-12345678', $persona->documento_completo);
    }

    /** @test */
    public function telefono_principal_retorna_null_sin_telefonos()
    {
        $persona = new Persona(['nombre' => 'Test']);
        // Simular colección vacía de teléfonos
        $persona->setRelation('telefonos', collect());

        $this->assertNull($persona->telefono_principal);
    }

    /** @test */
    public function telefono_principal_retorna_numero_principal()
    {
        $persona = new Persona(['nombre' => 'Test']);

        $tel1 = new Telefono(['numero' => '0424-1111111', 'es_principal' => false]);
        $tel2 = new Telefono(['numero' => '0414-2222222', 'es_principal' => true]);

        $persona->setRelation('telefonos', collect([$tel1, $tel2]));

        $this->assertEquals('0414-2222222', $persona->telefono_principal);
    }

    /** @test */
    public function direccion_principal_retorna_null_sin_direccion()
    {
        $persona = new Persona(['nombre' => 'Test']);
        $persona->setRelation('direccion', null);

        $this->assertNull($persona->direccion_principal);
    }

    /** @test */
    public function direccion_principal_es_la_direccion_uno_a_uno()
    {
        $persona = new Persona(['nombre' => 'Test']);
        $persona->setRelation('direccion', new Direccion(['direccion' => 'Avenida 2']));

        $this->assertEquals('Avenida 2', $persona->direccion_principal->direccion);
    }
}
