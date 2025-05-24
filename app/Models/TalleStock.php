<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TalleStock extends Model
{
    use HasFactory;

    protected $table = 'talle_stock';
    
    protected $fillable = [
        'stock_id',
        'talle',
        'cantidad_total',
        'cantidad_disponible',
        'cantidad_alquilada',
    ];

    public function stock()
    {
        return $this->belongsTo(StockAlquiler::class, 'stock_id');
    }
}