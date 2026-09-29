<?php

namespace Tests\Unit;

use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\UpdateClienteRequest;
use App\Http\Requests\GuardarPedidoRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FormRequestTest extends TestCase
{
    // Algunas rules() consultan la BD (p. ej. UpdateClienteRequest busca el
    // cliente de la ruta): sin esto el test depende del orden de ejecución.
    use RefreshDatabase;

    #[Test]
    public function store_cliente_request_tiene_reglas_requeridas()
    {
        $request = new StoreClienteRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('nombre', $rules);
        $this->assertArrayHasKey('tipo_cliente', $rules);
        $this->assertArrayHasKey('documento', $rules);
        $this->assertStringContainsString('required', $rules['nombre']);
        // Multi-teléfono: arreglo de 1 a 3 entradas.
        $this->assertStringContainsString('required', $rules['telefonos']);
        $this->assertStringContainsString('max:3', $rules['telefonos']);
        $this->assertArrayHasKey('telefonos.*.numero', $rules);
    }

    #[Test]
    public function store_cliente_request_tiene_mensajes_personalizados()
    {
        $request = new StoreClienteRequest();
        $messages = $request->messages();

        $this->assertNotEmpty($messages);
        $this->assertArrayHasKey('nombre.required', $messages);
        $this->assertArrayHasKey('telefonos.*.numero.regex', $messages);
    }

    #[Test]
    public function update_cliente_request_no_valida_documento()
    {
        $request = new UpdateClienteRequest();
        $rules = $request->rules();

        $this->assertArrayNotHasKey('documento', $rules);
    }

    #[Test]
    public function store_user_request_requiere_password_confirmado()
    {
        $request = new StoreUserRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('password', $rules);
        $this->assertContains('confirmed', $rules['password']);
        $this->assertTrue(collect($rules['password'])->contains(fn ($r) => $r instanceof \App\Rules\ContrasenaSegura));
        $this->assertStringContainsString('exists:rol,id', $rules['role_id']);
    }

    #[Test]
    public function update_user_request_no_requiere_password()
    {
        $request = new UpdateUserRequest();
        $rules = $request->rules();

        $this->assertArrayNotHasKey('password', $rules);
    }

    #[Test]
    public function el_pedido_no_recibe_lineas_y_valida_los_pagos()
    {
        $rules = (new GuardarPedidoRequest())->rules();

        // Las líneas se copian de la cotización en el servidor: el navegador no las envía.
        $this->assertArrayNotHasKey('productos', $rules);
        $this->assertArrayHasKey('cotizacion_id', $rules);
        $this->assertContains('min:0.01', $rules['pagos.*.monto']);
        $this->assertContains('required_unless:pagos.*.metodo,efectivo', $rules['pagos.*.banco_id']);
        $this->assertContains('required_unless:pagos.*.metodo,efectivo', $rules['pagos.*.referencia']);
    }

    #[Test]
    public function all_form_requests_authorize_returns_true()
    {
        $requests = [
            new StoreClienteRequest(),
            new UpdateClienteRequest(),
            new GuardarPedidoRequest(),
            new StoreUserRequest(),
            new UpdateUserRequest(),
        ];

        foreach ($requests as $request) {
            $this->assertTrue($request->authorize(), get_class($request) . '::authorize() debería retornar true');
        }
    }
}
