<?php
// Archivo: app/Models/Alquiler.php - Actualización

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

    public function stockItems()
{
    return $this->belongsToMany(StockAlquiler::class, 'alquiler_stock', 'alquiler_id', 'stock_id')
           ->withPivot('talle_id', 'cantidad')
           ->withTimestamps(); // opcional, si quieres incluir created_at y updated_at
}

    // Nueva relación con reserva
    public function reserva()
    {
        return $this->hasOne(Reserva::class);
    }


}