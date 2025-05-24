<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Devolucion extends Model
{
    use HasFactory;
    protected $table = 'devoluciones';

    protected $fillable = [
        'alquiler_id',
        'fecha_devolucion',
        'retraso',
        'multa',
    ];

    /**
     * Relación con el modelo Alquiler.
     * Una devolución pertenece a un alquiler.
     */
    public function alquiler()
    {
        return $this->belongsTo(Alquiler::class);
    }
}
