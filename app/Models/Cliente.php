<?php

namespace App\Models;

use App\Enums\TipoMedida;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombre',
        'documento',
        'telefono',
        'correo',
        'direccion',
        'estado',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    /* ----------------------------- Relaciones ----------------------------- */

    public function alquileres(): HasMany
    {
        return $this->hasMany(Alquiler::class);
    }

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class);
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class);
    }

    public function medidas(): HasMany
    {
        return $this->hasMany(ClienteMedida::class);
    }

    /** Solo la versión vigente de cada tipo de medida. */
    public function medidasVigentes(): HasMany
    {
        return $this->medidas()->where('vigente', true);
    }

    /** Versión vigente de un tipo concreto (saco/pantalon/chaleco). */
    public function medidaVigente(TipoMedida $tipo): HasOne
    {
        return $this->hasOne(ClienteMedida::class)
            ->where('tipo', $tipo->value)
            ->where('vigente', true);
    }

    /* ------------------------------- Scopes ------------------------------- */

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('estado', true);
    }

    public function scopeBuscar(Builder $query, ?string $termino): Builder
    {
        return $query->when($termino, function (Builder $q, string $termino) {
            $q->where(function (Builder $sub) use ($termino) {
                $sub->where('nombre', 'like', "%{$termino}%")
                    ->orWhere('documento', 'like', "%{$termino}%")
                    ->orWhere('telefono', 'like', "%{$termino}%")
                    ->orWhere('correo', 'like', "%{$termino}%");
            });
        });
    }
}
