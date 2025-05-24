<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'telefono', 'correo', 'direccion'];

    public function alquileres()
    {
        return $this->hasMany(Alquiler::class);
    }

    public function reservas()
    {
        return $this->hasMany(Reserva::class);
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }
}
