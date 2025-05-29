<?php
// Archivo: app/Models/TalleCompra.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TalleCompra extends Model
{
    use HasFactory;

    protected $table = 'talle_compra';

    protected $fillable = [
        'compra_id',
        'talle',
        'cantidad_total',
        'cantidad_disponible',
        'cantidad_vendida'
    ];

    public function compra()
    {
        return $this->belongsTo(Compra::class);
    }
}