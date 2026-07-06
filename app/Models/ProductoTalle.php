<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductoTalle extends Model
{
    protected $table = 'producto_talles';

    protected $fillable = [
        'producto_id',
        'talle',
        'cantidad_total',
        'cantidad_disponible',
        'cantidad_vendida',
    ];

    protected $casts = [
        'cantidad_total'      => 'integer',
        'cantidad_disponible' => 'integer',
        'cantidad_vendida'    => 'integer',
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function detallesVenta(): HasMany
    {
        return $this->hasMany(DetalleVenta::class);
    }
}
