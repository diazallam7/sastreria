<?php

namespace App\Models;

use App\Enums\EstadoAlquiler;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Alquiler extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'alquileres';

    protected $fillable = [
        'cliente_id',
        'fecha_inicio',
        'fecha_fin',
        'costo_total',
        'garantia',
        'estado',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin'    => 'date',
        'costo_total'  => 'integer',
        'garantia'     => 'integer',
        'estado'       => EstadoAlquiler::class,
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function stockItems(): BelongsToMany
    {
        return $this->belongsToMany(StockAlquiler::class, 'alquiler_stock', 'alquiler_id', 'stock_id')
            ->withPivot('talle_id', 'cantidad')
            ->withTimestamps();
    }

    public function reserva(): HasOne
    {
        return $this->hasOne(Reserva::class);
    }

    public function devolucion(): HasOne
    {
        return $this->hasOne(Devolucion::class);
    }

    /* ------------------------------- Scopes ------------------------------- */

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('estado', EstadoAlquiler::Activo->value);
    }

    /* ------------------------------ Helpers ------------------------------- */

    public function estaActivo(): bool
    {
        return $this->estado === EstadoAlquiler::Activo;
    }
}
