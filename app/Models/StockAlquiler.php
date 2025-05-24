<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockAlquiler extends Model
{
    use HasFactory;

    protected $table = 'stock_alquiler';
    
    protected $fillable = [
        'codigo',
        'nombre',
        'precio_alquiler',
        'descripcion'
    ];

    public function talles()
    {
        return $this->hasMany(TalleStock::class, 'stock_id');
    }

    // Método para verificar si hay al menos una prenda disponible
    public function getDisponibleAttribute()
    {
        return $this->talles()->where('cantidad_disponible', '>', 0)->exists();
    }

    // Método para obtener la cantidad total de prendas
    public function getCantidadTotalAttribute()
    {
        return $this->talles()->sum('cantidad_total');
    }

    // Método para obtener la cantidad disponible de prendas
    public function getCantidadDisponibleAttribute()
    {
        return $this->talles()->sum('cantidad_disponible');
    }

    // Método para obtener la cantidad alquilada de prendas
    public function getCantidadAlquiladaAttribute()
    {
        return $this->talles()->sum('cantidad_alquilada');
    }



    public function alquileres()
    {
        return $this->belongsToMany(Alquiler::class, 'alquiler_stock', 'stock_id', 'alquiler_id')
                    ->withPivot('talle_id', 'cantidad');
    }
}