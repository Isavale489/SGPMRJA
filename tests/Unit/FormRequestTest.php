<?php

namespace Tests\Unit;

use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\UpdateClienteRequest;
use App\Http\Requests\StorePedidoRequest;
use App\Http\Requests\UpdatePedidoRequest;
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
    public function store_pedido_request_valida_productos_como_array()
    {
        $request = new StorePedidoRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('productos', $rules);
        $this->assertStringContainsString('required', $rules['productos']);
        $this->assertStringContainsString('array', $rules['productos']);
        $this->assertStringContainsString('min:1', $rules['productos']);
        $this->assertArrayHasKey('productos.*.producto_id', $rules);
        $this->assertArrayHasKey('productos.*.cantidad', $rules);
    }

    #[Test]
    public function update_pedido_request_incluye_campo_estado()
    {
        $request = new UpdatePedidoRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('estado', $rules);
        $this->assertStringContainsString('Pendiente', $rules['estado']);
        $this->assertStringContainsString('Cancelado', $rules['estado']);
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
    public function store_pedido_request_valida_talla_y_genero_contra_catalogo()
    {
        $rules = (new StorePedidoRequest())->rules();

        // Talla es catálogo (tabla talla), no una lista fija de strings.
        $this->assertArrayHasKey('productos.*.talla_id', $rules);
        $this->assertArrayHasKey('productos.*.genero_id', $rules);
        $this->assertContains('required', $rules['productos.*.genero_id']);
    }

    #[Test]
    public function all_form_requests_authorize_returns_true()
    {
        $requests = [
            new StoreClienteRequest(),
            new UpdateClienteRequest(),
            new StorePedidoRequest(),
            new UpdatePedidoRequest(),
            new StoreUserRequest(),
            new UpdateUserRequest(),
        ];

        foreach ($requests as $request) {
            $this->assertTrue($request->authorize(), get_class($request) . '::authorize() debería retornar true');
        }
    }
}
