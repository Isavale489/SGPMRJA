<?php

namespace Tests\Unit;

use App\Models\Cliente;
use App\Models\Persona;
use App\Models\Telefono;
use App\Models\Direccion;
use App\Models\Estado;
use App\Models\Municipio;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ClienteTest extends TestCase
{
    private function crearClienteConPersona(array $personaData = [], array $telefonos = [], ?array $direccion = null): Cliente
    {
        // `nombre` consolida nombre + apellido (la columna apellido se eliminó).
        $persona = new Persona(array_merge([
            'nombre' => 'María González',
            'tipo_documento' => 'V-',
            'documento_identidad' => '98765432',
            'email' => 'maria@test.com',
        ], $personaData));

        $persona->setRelation('telefonos', collect(array_map(
            fn($t) => new Telefono($t),
            $telefonos
        )));
        // Dirección 1:1 (hasOne); estado/ciudad se resuelven del catálogo geográfico.
        $persona->setRelation('direccion', $direccion === null ? null : tap(new Direccion($direccion), function ($d) use ($direccion) {
            $d->setRelation('estadoRel', isset($direccion['estado']) ? new Estado(['nombre' => $direccion['estado']]) : null);
            $d->setRelation('municipioRel', isset($direccion['ciudad']) ? new Municipio(['nombre' => $direccion['ciudad']]) : null);
        }));

        $cliente = new Cliente(['tipo_cliente' => 'natural', 'estatus' => 1]);
        $cliente->setRelation('persona', $persona);

        return $cliente;
    }

    #[Test]
    public function accessors_delegan_a_persona_correctamente()
    {
        $cliente = $this->crearClienteConPersona();

        $this->assertEquals('María González', $cliente->nombre);
        $this->assertEquals('maria@test.com', $cliente->email);
        $this->assertEquals('V-98765432', $cliente->documento);
    }

    #[Test]
    public function telefono_delega_a_persona_telefono_principal()
    {
        $cliente = $this->crearClienteConPersona([], [
            ['numero' => '0424-7777777', 'es_principal' => true],
        ]);

        $this->assertEquals('0424-7777777', $cliente->telefono);
    }

    #[Test]
    public function direccion_delega_a_la_direccion_de_persona()
    {
        $cliente = $this->crearClienteConPersona([], [], [
            'direccion' => 'Av. Bolívar 123', 'ciudad' => 'Araure', 'estado' => 'Portuguesa',
        ]);

        $this->assertEquals('Av. Bolívar 123', $cliente->direccion);
        $this->assertEquals('Araure', $cliente->ciudad);
        $this->assertEquals('Portuguesa', $cliente->estado_territorial);
    }

    #[Test]
    public function accessors_retornan_null_sin_persona()
    {
        $cliente = new Cliente(['tipo_cliente' => 'natural', 'estatus' => 1]);
        $cliente->setRelation('persona', null);

        $this->assertNull($cliente->nombre);
        $this->assertNull($cliente->email);
        $this->assertNull($cliente->telefono);
        $this->assertNull($cliente->documento);
        $this->assertNull($cliente->direccion);
    }
}
