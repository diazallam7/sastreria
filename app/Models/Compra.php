<?php
// Archivo: app/Models/Compra.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre_producto',
        'fecha_compra',
        'precio_compra',
        'precio_venta',
        'observacion',
        'activo_para_venta'
    ];

    protected $casts = [
        'fecha_compra' => 'date',
        'activo_para_venta' => 'boolean'
    ];

    public function talles()
    {
        return $this->hasMany(TalleCompra::class);
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