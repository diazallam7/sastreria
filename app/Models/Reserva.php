<?php

namespace App\Models;

use App\Enums\EstadoReserva;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Reserva extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'cliente_id',
        'user_id',
        'fecha_reserva',
        'fecha_entrega_programada',
        'fecha_devolucion_programada',
        'monto_total',
        'garantia_total',
        'senia_garantia',
        'senia_alquiler',
        'senia_devuelta',
        'motivo_devolucion',
        'estado',
        'observaciones',
        'alquiler_id',
    ];

    protected $casts = [
        'fecha_reserva'               => 'date',
        'fecha_entrega_programada'    => 'date',
        'fecha_devolucion_programada' => 'date',
        'monto_total'                 => 'integer',
        'garantia_total'              => 'integer',
        'senia_garantia'              => 'integer',
        'senia_alquiler'              => 'integer',
        'senia_devuelta'              => 'integer',
        'estado'                      => EstadoReserva::class,
    ];

    /* ----------------------------- Relaciones ----------------------------- */

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function alquiler(): BelongsTo
    {
        return $this->belongsTo(Alquiler::class);
    }

    public function stockItems(): BelongsToMany
    {
        return $this->belongsToMany(StockAlquiler::class, 'reserva_stock', 'reserva_id', 'stock_id')
            ->withPivot('talle_id', 'cantidad')
            ->withTimestamps();
    }

    /* ------------------------------- Scopes ------------------------------- */

    public function scopePendientesEntrega(Builder $query): Builder
    {
        return $query->where('estado', EstadoReserva::Confirmada->value)
            ->where('fecha_entrega_programada', '<=', Carbon::now());
    }

    public function scopeProximasEntregas(Builder $query): Builder
    {
        return $query->where('estado', EstadoReserva::Confirmada->value)
            ->whereBetween('fecha_entrega_programada', [Carbon::now(), Carbon::now()->addDays(7)]);
    }

    /* ----------------------------- Accessors ------------------------------ */

    public function getSaldoAlquilerAttribute(): int
    {
        return (int) $this->monto_total - (int) $this->senia_alquiler;
    }

    public function getSaldoGarantiaAttribute(): int
    {
        return (int) $this->garantia_total - (int) $this->senia_garantia;
    }

    public function getTotalACobrarAttribute(): int
    {
        return $this->saldo_alquiler + $this->saldo_garantia;
    }

    /** Total efectivamente recibido del cliente (seña de alquiler + garantía). */
    public function getTotalRecibidoAttribute(): int
    {
        return (int) $this->senia_alquiler + (int) $this->senia_garantia;
    }
}
