<?php
// Archivo: app/Models/Reserva.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Reserva extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'fecha_reserva',
        'fecha_entrega_programada',
        'fecha_devolucion_programada',
        'monto_total',
        'garantia_total',
        'seña_garantia',
        'seña_alquiler',
        'estado',
        'observaciones',
        'alquiler_id',
        'seña_devuelta',       
    'motivo_devolucion'
    ];

    protected $casts = [
        'fecha_reserva' => 'date',
        'fecha_evento' => 'date',
        'fecha_entrega_programada' => 'date',
        'fecha_devolucion_programada' => 'date',
        'monto_total' => 'decimal:2',
        'garantia_total' => 'decimal:2',
        'seña_garantia' => 'decimal:2',
        'seña_alquiler' => 'decimal:2',
    'seña_devuelta' => 'decimal:2'
    ];

    // Relaciones
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function alquiler()
    {
        return $this->belongsTo(Alquiler::class);
    }

    public function stockItems()
    {
        return $this->belongsToMany(StockAlquiler::class, 'reserva_stock', 'reserva_id', 'stock_id')
                    ->withPivot('talle_id', 'cantidad')
                    ->withTimestamps();
    }

    // Accessors
    public function getMontoFormateadoAttribute()
    {
        return '₲ ' . number_format($this->monto_total, 0, ',', '.');
    }

    public function getGarantiaFormateadaAttribute()
    {
        return '₲ ' . number_format($this->garantia_total, 0, ',', '.');
    }

    public function getSeniaGarantiaFormateadaAttribute()
    {
        return '₲ ' . number_format($this->seña_garantia, 0, ',', '.');
    }

    public function getSeniaAlquilerFormateadaAttribute()
    {
        return '₲ ' . number_format($this->seña_alquiler, 0, ',', '.');
    }

    public function getSaldoAlquilerAttribute()
    {
        return $this->monto_total - $this->seña_alquiler;
    }

    public function getSaldoGarantiaAttribute()
    {
        return $this->garantia_total - $this->seña_garantia;
    }

    public function getTotalACobrarAttribute()
    {
        return $this->getSaldoAlquilerAttribute() + $this->getSaldoGarantiaAttribute();
    }

    public function getSeniaDevueltaFormateadaAttribute()
{
    return '₲ ' . number_format($this->seña_devuelta ?? 0, 0, ',', '.');
}

    // Scopes
    public function scopePendientesEntrega($query)
    {
        return $query->where('estado', 'confirmada')
                    ->where('fecha_entrega_programada', '<=', Carbon::now());
    }

    public function scopeProximasEntregas($query)
    {
        return $query->where('estado', 'confirmada')
                    ->where('fecha_entrega_programada', '>', Carbon::now())
                    ->where('fecha_entrega_programada', '<=', Carbon::now()->addDays(7));
    }
}