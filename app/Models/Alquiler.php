<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alquiler extends Model
{
    use HasFactory;

     protected $table = 'alquileres';
    protected $fillable = [
        'cliente_id',
        'fecha_inicio',
        'fecha_fin',
        'costo_total',
        'garantia',
        'estado'

    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    // Relación existente con prendas (vestidos)
    public function prendas()
    {
        return $this->belongsToMany(Vestido::class, 'alquiler_prenda', 'alquiler_id', 'prenda_id');
    }

    // Nueva relación con stock de alquiler
    public function stockItems()
    {
        return $this->belongsToMany(StockAlquiler::class, 'alquiler_stock', 'alquiler_id', 'stock_id');
    }
}