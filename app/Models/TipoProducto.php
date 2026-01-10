<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoProducto extends Model
{
    use HasFactory;

    protected $table = 'tipo_producto';

    protected $fillable = [
        'nombre',
        'codigo_prefijo',
        'descripcion',
        'contador',
    ];

    /**
     * Relación con productos
     */
    public function productos()
    {
        return $this->hasMany(Producto::class);
    }

    /**
     * Genera abreviatura de 3 letras del modelo
     */
    public static function abreviarModelo(string $modelo): string
    {
        // Quitar acentos y limpiar
        $modelo = self::quitarAcentos($modelo);
        $modelo = preg_replace('/[^a-zA-Z\s]/', '', $modelo);
        $palabras = array_filter(explode(' ', trim($modelo)));

        if (count($palabras) >= 3) {
            // Tomar primera letra de las primeras 3 palabras
            return strtoupper(
                substr($palabras[0], 0, 1) .
                substr($palabras[1], 0, 1) .
                substr($palabras[2], 0, 1)
            );
        } elseif (count($palabras) == 2) {
            // Primera palabra 2 letras, segunda 1 letra
            return strtoupper(
                substr($palabras[0], 0, 2) .
                substr($palabras[1], 0, 1)
            );
        } else {
            // Una palabra: primeras 3 letras
            return strtoupper(substr($palabras[0] ?? 'XXX', 0, 3));
        }
    }

    /**
     * Quitar acentos de string
     */
    private static function quitarAcentos(string $texto): string
    {
        $acentos = ['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú', 'ñ', 'Ñ'];
        $sinAcento = ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U', 'n', 'N'];
        return str_replace($acentos, $sinAcento, $texto);
    }

    /**
     * Cuenta productos existentes con este tipo y abreviatura de modelo
     */
    public function contarProductosConModelo(string $abreviatura): int
    {
        return Producto::where('tipo_producto_id', $this->id)
            ->where('codigo', 'LIKE', $this->codigo_prefijo . '-' . $abreviatura . '-%')
            ->count();
    }

    /**
     * Genera el próximo código para un producto de este tipo y modelo
     * Formato: TIPO-MOD-NNN (ej: CHM-POL-001)
     */
    public function generarCodigo(string $modelo): string
    {
        $abreviatura = self::abreviarModelo($modelo);
        $contador = $this->contarProductosConModelo($abreviatura) + 1;

        return $this->codigo_prefijo . '-' . $abreviatura . '-' . str_pad($contador, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Obtiene el próximo código sin guardar (preview)
     */
    public function proximoCodigo(string $modelo = ''): string
    {
        if (empty($modelo)) {
            return $this->codigo_prefijo . '-XXX-001';
        }

        $abreviatura = self::abreviarModelo($modelo);
        $contador = $this->contarProductosConModelo($abreviatura) + 1;

        return $this->codigo_prefijo . '-' . $abreviatura . '-' . str_pad($contador, 3, '0', STR_PAD_LEFT);
    }
}
