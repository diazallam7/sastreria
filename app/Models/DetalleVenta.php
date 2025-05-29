<?php
// Archivo: app/Models/DetalleVenta.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Compra;
use App\Models\ProductoVenta;
use App\Models\TalleCompra;
use App\Models\TalleProductoVenta;

class DetalleVenta extends Model
{
    use HasFactory;

    protected $fillable = [
        'venta_id',
        'tipo_producto',
        'producto_id',
        'talle_id',
        'cantidad',
        'precio_unitario',
        'subtotal',
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function getProductoAttribute()
    {
        if ($this->tipo_producto === 'compra') {
            return Compra::find($this->producto_id);
        } else {
            return ProductoVenta::find($this->producto_id);
        }
    }

    public function getTalleAttribute()
    {
        if ($this->tipo_producto === 'compra') {
            return TalleCompra::find($this->talle_id);
        } else {
            return TalleProductoVenta::find($this->talle_id);
        }
    }
}