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

                    // `fechaActualizacion` del oficial ya es la FECHA VALOR (vigencia)
                    // que DolarAPI normaliza a medianoche: la tasa que el BCV publica
                    // una tarde ya viene fechada al día en que rige. Por eso NO se suma
                    // un día aquí (sería un doble corrimiento que dejaría la tasa de hoy
                    // fechada a mañana e invisible bajo el techo `fecha_bcv <= hoy` del
                    // getter). La regla "rige al día siguiente de publicarse" queda
                    // satisfecha por la propia API + ese techo en obtenerTasaActual().
                    $fecha = Carbon::parse($fechaStr)->toDateString();

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
