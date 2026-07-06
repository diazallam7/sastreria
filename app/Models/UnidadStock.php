<?php

namespace App\Models;

use App\Enums\EstadoUnidad;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UnidadStock extends Model
{
    use SoftDeletes;

    protected $table = 'unidad_stock';

    protected $fillable = [
        'talle_stock_id',
        'codigo',
        'estado',
    ];

    protected $casts = [
        'estado' => EstadoUnidad::class,
    ];

    public function talleStock(): BelongsTo
    {
        return $this->belongsTo(TalleStock::class, 'talle_stock_id');
    }

    public function scopeDisponibles(Builder $query): Builder
    {
        return $query->where('estado', EstadoUnidad::Disponible->value);
    }

    public function scopePorCodigo(Builder $query, string $codigo): Builder
    {
        return $query->where('codigo', $codigo);
    }
}
