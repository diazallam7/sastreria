<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory;

    protected $fillable = ['cliente_id', 'vestido_id', 'fecha_venta', 'precio_total'];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function vestido()
    {
        return $this->belongsTo(Vestido::class);
    }
}
