<?php
// Archivo: app/Models/TalleProductoVenta.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TalleProductoVenta extends Model
{
    use HasFactory;

    protected $table = 'talle_producto_venta';

    protected $fillable = [
        'producto_venta_id',
        'talle',
        'cantidad_total',
        'cantidad_disponible',
        'cantidad_vendida'
    ];

    public function productoVenta()
    {
        return $this->belongsTo(ProductoVenta::class);
    }
}