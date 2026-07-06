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
use Illuminate\Support\Collection;

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
        'fecha_fin' => 'date',
        'costo_total' => 'integer',
        'garantia' => 'integer',
        'estado' => EstadoAlquiler::class,
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function unidades(): BelongsToMany
    {
        return $this->belongsToMany(UnidadStock::class, 'alquiler_unidad', 'alquiler_id', 'unidad_id')
            ->withPivot('precio')
            ->withTimestamps();
    }

    /**
     * Agrupa las unidades por talle (compatibilidad con la UI, que muestra
     * "prenda × talle × cantidad" en vez de listar cada unidad física una por una).
     * Requiere `unidades.talleStock.stock` cargado para evitar N+1.
     *
     * @return Collection<int, object{stock_id:int,nombre:string,codigo:string,talle_id:int,talle:string,cantidad:int,precio:int}>
     */
    public function prendasAgrupadas(): Collection
    {
        return $this->unidades
            ->groupBy('talle_stock_id')
            ->map(function (Collection $grupo) {
                $talle = $grupo->first()->talleStock;

                return (object) [
                    'stock_id' => $talle->stock_id,
                    'nombre' => $talle->stock->nombre,
                    'codigo' => $talle->stock->codigo,
                    'talle_id' => $talle->id,
                    'talle' => $talle->talle,
                    'cantidad' => $grupo->count(),
                    'precio' => (int) $grupo->first()->pivot->precio,
                ];
            })
            ->values();
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
