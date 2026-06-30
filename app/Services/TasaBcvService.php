<?php

namespace App\Services;

use App\Models\TasaCambio;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TasaBcvService
{
    /**
     * URL de la API DolarAPI Venezuela (tasa oficial BCV)
     */
    protected string $apiUrl = 'https://ve.dolarapi.com/v1/dolares';

    /**
     * Actualiza las tasas desde la API
     */
    public function actualizarTasas(): array
    {
        try {
            $response = Http::timeout(15)->get($this->apiUrl);

            if ($response->successful()) {
                $data = $response->json();

                // Buscar la tasa oficial (BCV)
                $tasaOficial = collect($data)->firstWhere('fuente', 'oficial');

                if ($tasaOficial && isset($tasaOficial['promedio'])) {
                    $precio = $tasaOficial['promedio'];
                    $fechaStr = $tasaOficial['fechaActualizacion'] ?? now()->toDateTimeString();

                    // Vigencia = fecha de PUBLICACIÓN + 1 día: la tasa que el BCV
                    // publica una tarde rige a partir de las 00:00 del día siguiente.
                    // Se deriva de la fecha de publicación (no de cuándo corre el
                    // scraper), así es consistente aunque éste corra de mañana.
                    $fecha = Carbon::parse($fechaStr)->addDay()->toDateString();

                    // Guardar o actualizar en BD
                    $tasa = $this->guardarTasa('USD', $precio, $fecha, 'BCV (DolarAPI)');

                    return [
                        'success' => true,
                        'message' => 'Tasa BCV actualizada correctamente',
                        'tasa' => $tasa,
                    ];
                }

                return ['success' => false, 'message' => 'No se encontró la tasa oficial en la respuesta'];
            }

            return ['success' => false, 'message' => 'Error de conexión con la API: ' . $response->status()];

        } catch (\Exception $e) {
            Log::error('Error al actualizar tasas BCV: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al conectar con la API: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Guarda o actualiza la tasa en la base de datos
     */
    protected function guardarTasa(string $moneda, float $valor, string $fecha, string $fuente): TasaCambio
    {
        return TasaCambio::updateOrCreate(
            [
                'moneda' => $moneda,
                'fecha_bcv' => $fecha,
            ],
            [
                'valor' => $valor,
                'fuente' => $fuente,
            ]
        );
    }

    /**
     * Obtiene la tasa actual del USD
     */
    public function obtenerTasaActual(): ?TasaCambio
    {
        return TasaCambio::obtenerTasaActual('USD');
    }
}
