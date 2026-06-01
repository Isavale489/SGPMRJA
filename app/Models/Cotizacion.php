<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cotizacion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cotizacion';

    protected $fillable = [
        'cliente_id',
        'fecha_cotizacion',
        'fecha_validez',
        'estado',
        'total',
        'tasa_cambio_valor',
        'notas',
        'condiciones_terminos',
        'user_id',
        'prioridad',
    ];

    protected $casts = [
        'fecha_cotizacion'   => 'date',
        'fecha_validez'      => 'date',
        'total'              => 'decimal:2',
        'tasa_cambio_valor'  => 'decimal:4',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function productos()
    {
        return $this->hasMany(DetalleCotizacion::class);
    }

    /**
     * Relación con el pedido creado desde esta cotización
     */
    public function pedido()
    {
        return $this->hasOne(Pedido::class);
    }

    /**
     * Verificar si la cotización puede ser convertida a pedido.
     */
    public function puedeConvertirse(): bool
    {
        return $this->estado === 'Aprobada' && !$this->yaFueConvertida();
    }

    /**
     * Verificar si la cotización ya fue convertida a pedido.
     */
    public function yaFueConvertida(): bool
    {
        return $this->pedido()->exists();
    }

    /**
     * Actualizar automáticamente cotizaciones vencidas
     * Este método revisa todas las cotizaciones que están en estado Pendiente o Aprobada
     * y las marca como Vencida si su fecha_validez ya pasó
     */
    public static function actualizarCotizacionesVencidas()
    {
        $hoy = now()->format('Y-m-d');

        self::whereIn('estado', ['Pendiente', 'Aprobada'])
            ->where('fecha_validez', '<', $hoy)
            ->update(['estado' => 'Vencida']);
    }
}
