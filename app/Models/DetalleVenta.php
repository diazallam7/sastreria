<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleVenta extends Model
{
    use HasFactory;

    protected $fillable = [
        'venta_id',
        'producto_talle_id',
        'nombre_producto', // snapshot al momento de la venta
        'talle',           // snapshot
        'cantidad',
        'precio_unitario',
        'subtotal',
    ];

    protected $casts = [
        'cantidad'        => 'integer',
        'precio_unitario' => 'integer',
        'subtotal'        => 'integer',
    ];

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class);
    }

    /** Puede ser null si el talle/producto fue eliminado después de la venta. */
    public function productoTalle(): BelongsTo
    {
        return $this->belongsTo(ProductoTalle::class);
    }
}
