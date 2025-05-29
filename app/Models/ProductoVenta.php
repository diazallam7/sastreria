<?php
// Archivo: app/Models/ProductoVenta.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoVenta extends Model
{
    use HasFactory;

    protected $table = 'productos_venta';

    protected $fillable = [
        'nombre_producto',
        'precio_venta',
        'observacion'
    ];

    public function talles()
    {
        return $this->hasMany(TalleProductoVenta::class);
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }

    // Método para verificar si hay stock disponible
    public function getDisponibleAttribute()
    {
        return $this->talles()->where('cantidad_disponible', '>', 0)->exists();
    }

    // Método para obtener la cantidad total
    public function getCantidadTotalAttribute()
    {
        return $this->talles()->sum('cantidad_total');
    }

    // Método para obtener la cantidad disponible
    public function getCantidadDisponibleAttribute()
    {
        return $this->talles()->sum('cantidad_disponible');
    }

    // Método para obtener la cantidad vendida
    public function getCantidadVendidaAttribute()
    {
        return $this->talles()->sum('cantidad_vendida');
    }
}