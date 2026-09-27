<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Direccion;
use App\Models\Persona;
use App\Models\Telefono;
use Illuminate\Database\Seeder;

class ClienteFakeSeeder extends Seeder
{
    public function run(): void
    {
        $clientes = [
            [
                'nombre' => 'Carlos',
                'apellido' => 'Ramírez',
                'tipo_doc' => 'V-',
                'doc' => '18456321',
                'email' => 'carlos.ramirez@gmail.com',
                'telefono' => '0414-7821345',
                'tipo_cliente' => 'natural',
                'direccion' => 'Calle 15 con Av. Bolívar, Casa #12',
                'estado' => 'Portuguesa',
                'ciudad' => 'Acarigua',
            ],
            [
                'nombre' => 'María',
                'apellido' => 'González',
                'tipo_doc' => 'V-',
                'doc' => '20134567',
                'email' => 'maria.gonzalez@hotmail.com',
                'telefono' => '0424-5567890',
                'tipo_cliente' => 'natural',
                'direccion' => 'Urb. Las Acacias, Calle 3, Casa #8',
                'estado' => 'Portuguesa',
                'ciudad' => 'Araure',
            ],
            [
                'nombre' => 'Inversiones Textilera del Centro',
                'apellido' => '',
                'tipo_doc' => 'J-',
                'doc' => '41234567',
                'email' => 'admin@textileracentro.com',
                'telefono' => '0255-2514789',
                'tipo_cliente' => 'juridico',
                'direccion' => 'Zona Industrial II, Galpón 5',
                'estado' => 'Carabobo',
                'ciudad' => 'Valencia',
            ],
            [
                'nombre' => 'Luis',
                'apellido' => 'Hernández',
                'tipo_doc' => 'V-',
                'doc' => '15678234',
                'email' => 'luis.hernandez@outlook.com',
                'telefono' => '0412-8903456',
                'tipo_cliente' => 'natural',
                'direccion' => 'Av. Principal de Páez, Edificio Don Luis, Piso 2',
                'estado' => 'Portuguesa',
                'ciudad' => 'Acarigua',
            ],
            [
                'nombre' => 'Confecciones El Llano C.A.',
                'apellido' => '',
                'tipo_doc' => 'J-',
                'doc' => '40987654',
                'email' => 'ventas@confeccionesellano.com',
                'telefono' => '0255-6618900',
                'tipo_cliente' => 'juridico',
                'direccion' => 'Calle 30 entre Av. 27 y 28, Local 4',
                'estado' => 'Portuguesa',
                'ciudad' => 'Acarigua',
            ],
            [
                'nombre' => 'Ana',
                'apellido' => 'Martínez',
                'tipo_doc' => 'V-',
                'doc' => '22345678',
                'email' => 'ana.martinez@gmail.com',
                'telefono' => '0416-4567123',
                'tipo_cliente' => 'natural',
                'direccion' => 'Urb. Villa del Pilar, Calle 10, Casa 25',
                'estado' => 'Portuguesa',
                'ciudad' => 'Araure',
            ],
            [
                'nombre' => 'José',
                'apellido' => 'Pérez',
                'tipo_doc' => 'V-',
                'doc' => '17890456',
                'email' => 'jose.perez@yahoo.com',
                'telefono' => '0424-3345678',
                'tipo_cliente' => 'natural',
                'direccion' => 'Barrio Sucre, Calle Principal, Casa S/N',
                'estado' => 'Barinas',
                'ciudad' => 'Barinas',
            ],
            [
                'nombre' => 'Uniformes Profesionales VE C.A.',
                'apellido' => '',
                'tipo_doc' => 'J-',
                'doc' => '42567890',
                'email' => 'contacto@uniprovzla.com',
                'telefono' => '0212-5551234',
                'tipo_cliente' => 'juridico',
                'direccion' => 'Centro Empresarial La Paz, Oficina 302',
                'estado' => 'Miranda',
                'ciudad' => 'Caracas',
            ],
            [
                'nombre' => 'Rosa',
                'apellido' => 'Castillo',
                'tipo_doc' => 'V-',
                'doc' => '19567890',
                'email' => 'rosa.castillo@gmail.com',
                'telefono' => '0414-9012345',
                'tipo_cliente' => 'natural',
                'direccion' => 'Urb. Prados del Sol, Calle 5, Casa 14-B',
                'estado' => 'Portuguesa',
                'ciudad' => 'Araure',
            ],
            [
                'nombre' => 'Pedro',
                'apellido' => 'Morales',
                'tipo_doc' => 'V-',
                'doc' => '16789012',
                'email' => 'pedro.morales@gmail.com',
                'telefono' => '0412-6781234',
                'tipo_cliente' => 'natural',
                'direccion' => 'Sector Los Cortijos, Vereda 3, Casa 7',
                'estado' => 'Portuguesa',
                'ciudad' => 'Páez',
            ],
        ];

        foreach ($clientes as $data) {
            // 1. Crear Persona
            $persona = Persona::create([
                // Identidad consolidada en `nombre` (natural: nombre+apellido; jurídico: razón social)
                'nombre' => trim($data['nombre'] . ' ' . ($data['apellido'] ?? '')),
                'tipo_documento' => $data['tipo_doc'],
                'documento_identidad' => $data['doc'],
                'email' => $data['email'],
            ]);

            // 2. Crear Teléfono principal
            Telefono::create([
                'persona_id' => $persona->id,
                'numero' => $data['telefono'],
                'tipo' => 'movil',
                'es_principal' => true,
            ]);

            // 3. Crear Dirección principal
            Direccion::create([
                'persona_id' => $persona->id,
                'direccion' => $data['direccion'],
                'estado' => $data['estado'],
                'ciudad' => $data['ciudad'],
                'tipo' => 'casa',
                'es_principal' => true,
            ]);

            // 4. Crear Cliente
            Cliente::create([
                'persona_id' => $persona->id,
                'tipo_cliente' => $data['tipo_cliente'],
                'estatus' => 1,
            ]);
        }

        $this->command->info('✅ 10 clientes ficticios creados exitosamente.');
    }
}
