<?php

namespace App\Models;

use App\Enums\TipoProducto;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombre',
        'tipo',
        'precio_venta',
        'precio_compra',
        'fecha_compra',
        'activo_para_venta',
        'observacion',
    ];

    protected $casts = [
        'tipo'              => TipoProducto::class,
        'precio_venta'      => 'integer',
        'precio_compra'     => 'integer',
        'fecha_compra'      => 'date',
        'activo_para_venta' => 'boolean',
    ];

    public function talles(): HasMany
    {
        return $this->hasMany(ProductoTalle::class);
    }

    /* ------------------------------- Scopes ------------------------------- */

    public function scopeComprados(Builder $query): Builder
    {
        return $query->where('tipo', TipoProducto::Comprado->value);
    }

    public function scopeFabricados(Builder $query): Builder
    {
        return $query->where('tipo', TipoProducto::Fabricado->value);
    }

    /** Productos activos con al menos un talle con stock disponible. */
    public function scopeVendibles(Builder $query): Builder
    {
        return $query->where('activo_para_venta', true)
            ->whereHas('talles', fn (Builder $q) => $q->where('cantidad_disponible', '>', 0));
    }

    /* ----------------------------- Accessors ------------------------------ */

    public function getCantidadDisponibleAttribute(): int
    {
        return (int) $this->talles()->sum('cantidad_disponible');
    }

    public function getCantidadTotalAttribute(): int
    {
        return (int) $this->talles()->sum('cantidad_total');
    }

    public function getCantidadVendidaAttribute(): int
    {
        return (int) $this->talles()->sum('cantidad_vendida');
    }
}
