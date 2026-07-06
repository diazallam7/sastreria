<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TalleStock extends Model
{
    use HasFactory;

    protected $table = 'talle_stock';

    protected $fillable = [
        'stock_id',
        'talle',
        'cantidad_total',
        'cantidad_disponible',
        'cantidad_alquilada',
        'cantidad_reservada',
    ];

    protected $casts = [
        'cantidad_total' => 'integer',
        'cantidad_disponible' => 'integer',
        'cantidad_alquilada' => 'integer',
        'cantidad_reservada' => 'integer',
    ];

    public function stock(): BelongsTo
    {
        return $this->belongsTo(StockAlquiler::class, 'stock_id');
    }

    public function unidades(): HasMany
    {
        return $this->hasMany(UnidadStock::class, 'talle_stock_id');
    }
}
