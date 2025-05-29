<?php
// Archivo: app/Models/GastoVario.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GastoVario extends Model
{
    use HasFactory;

    protected $table = 'gastos_varios';

    protected $fillable = [
        'nombre_gasto',
        'fecha',
        'monto',
        'observacion'
    ];

    protected $casts = [
        'fecha' => 'date',
        'monto' => 'decimal:2'
    ];

    // Scope para filtrar por fecha
    public function scopeFechaEntre($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
    }

    // Scope para buscar por nombre
    public function scopeBuscarPorNombre($query, $nombre)
    {
        return $query->where('nombre_gasto', 'like', '%' . $nombre . '%');
    }

    // Accessor para formatear el monto
    public function getMontoFormateadoAttribute()
    {
        return '₲ ' . number_format($this->monto, 0, ',', '.');
    }
}