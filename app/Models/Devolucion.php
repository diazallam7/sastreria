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
        'multa_calculada',
        'multa_aplicada_real',
        'garantia_original',
        'multa_aplicada',
        'monto_devuelto',
        'monto_devuelto_real',
        'dias_retraso',
        'observaciones',
        'motivo_ajuste'
    ];

    protected $casts = [
        'fecha_devolucion' => 'date',
        'garantia_original' => 'decimal:2',
        'multa_aplicada' => 'decimal:2',
        'monto_devuelto' => 'decimal:2',
        'multa_calculada' => 'decimal:2',
        'multa_aplicada_real' => 'decimal:2',
        'monto_devuelto_real' => 'decimal:2'
    ];

    public function alquiler()
    {
        return $this->belongsTo(Alquiler::class);
    }

    // Accessors para formatear montos
    public function getMultaCalculadaFormateadaAttribute()
    {
        return '₲ ' . number_format($this->multa_calculada ?? 0, 0, ',', '.');
    }

    public function getMultaAplicadaRealFormateadaAttribute()
    {
        return '₲ ' . number_format($this->multa_aplicada_real ?? 0, 0, ',', '.');
    }

    public function getMontoDevueltoRealFormateadoAttribute()
    {
        return '₲ ' . number_format($this->monto_devuelto_real ?? 0, 0, ',', '.');
    }
}