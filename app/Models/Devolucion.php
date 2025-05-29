<?php
// Archivo: app/Models/Devolucion.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Devolucion extends Model
{
    use HasFactory;
    
    protected $table = 'devoluciones';

    protected $fillable = [
        'alquiler_id',
        'fecha_devolucion',
        'retraso',
        'multa',
        'garantia_original',
        'multa_aplicada',
        'monto_devuelto',
        'dias_retraso',
        'observaciones'
    ];

    protected $casts = [
        'fecha_devolucion' => 'date',
        'garantia_original' => 'decimal:2',
        'multa_aplicada' => 'decimal:2',
        'monto_devuelto' => 'decimal:2'
    ];

    /**
     * Relación con el modelo Alquiler.
     */
    public function alquiler()
    {
        return $this->belongsTo(Alquiler::class);
    }

    /**
     * Accessor para formatear la garantía original
     */
    public function getGarantiaFormateadaAttribute()
    {
        return '₲ ' . number_format($this->garantia_original, 0, ',', '.');
    }

    /**
     * Accessor para formatear la multa aplicada
     */
    public function getMultaFormateadaAttribute()
    {
        return '₲ ' . number_format($this->multa_aplicada, 0, ',', '.');
    }

    /**
     * Accessor para formatear el monto devuelto
     */
    public function getMontoDevueltoFormateadoAttribute()
    {
        return '₲ ' . number_format($this->monto_devuelto, 0, ',', '.');
    }

    /**
     * Scope para filtrar por fecha
     */
    public function scopeFechaEntre($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha_devolucion', [$fechaInicio, $fechaFin]);
    }
}