<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vestido extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre', 'descripcion', 'talla', 'color', 'categoria', 
        'precio_alquiler', 'precio_venta', 'estado'
    ];

    public function alquileres()
    {
        return $this->belongsToMany(Alquiler::class, 'alquiler_prenda', 'prenda_id', 'alquiler_id');
    }


}

