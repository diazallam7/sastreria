<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockAlquiler extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'stock_alquiler';

    protected $fillable = [
        'codigo',
        'nombre',
        'precio_alquiler',
        'descripcion',
    ];

    protected $casts = [
        'precio_alquiler' => 'integer',
    ];

    public function talles(): HasMany
    {
        return $this->hasMany(TalleStock::class, 'stock_id');
    }

    public function alquileres(): BelongsToMany
    {
        return $this->belongsToMany(Alquiler::class, 'alquiler_stock', 'stock_id', 'alquiler_id')
            ->withPivot('talle_id', 'cantidad');
    }

    /* ------------------------------- Scopes ------------------------------- */

    /** Prendas con al menos un talle disponible. */
    public function scopeDisponibles(Builder $query): Builder
    {
        return $query->whereHas('talles', fn (Builder $q) => $q->where('cantidad_disponible', '>', 0));
    }

    /* ----------------------------- Accessors ------------------------------ */

    public function getCantidadTotalAttribute(): int
    {
        return (int) $this->talles()->sum('cantidad_total');
    }

    public function getCantidadDisponibleAttribute(): int
    {
        return (int) $this->talles()->sum('cantidad_disponible');
    }

    public function getCantidadAlquiladaAttribute(): int
    {
        return (int) $this->talles()->sum('cantidad_alquilada');
    }

    public function getCantidadReservadaAttribute(): int
    {
        return (int) $this->talles()->sum('cantidad_reservada');
    }
}
