<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'telefono', 
        'correo',
        'direccion',
        'estado',
        // Medidas básicas
        'medida_saco_basica',
        'medida_pantalon_basica',
        // Medidas saco
        'saco_talle', 'saco_largo', 'saco_espalda', 'saco_manga', 'saco_pecho', 'saco_cintura', 'saco_cadera',
        'saco_alto_hombro', 'saco_plomo_trasero', 'saco_plomo_delantero', 'saco_sisa', 'saco_puno',
        // Medidas pantalón
        'pantalon_largo', 'pantalon_cintura', 'pantalon_cadera', 'pantalon_entre_pierna', 'pantalon_muslo', 'pantalon_rodilla', 'pantalon_bajo',
        // Medidas chaleco
        'chaleco_talle', 'chaleco_pecho', 'chaleco_cintura', 'chaleco_escote', 'chaleco_largo', 'chaleco_largo_trasero', 'chaleco_cuello',
        // Observaciones
        'observaciones_medidas'
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    // Relaciones
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

    // Métodos auxiliares para medidas
    public function tieneMedidasBasicas()
    {
        return !empty($this->medida_saco_basica) || !empty($this->medida_pantalon_basica);
    }

    public function tieneMedidasCompletas()
    {
        return !empty($this->saco_talle) || !empty($this->pantalon_largo) || !empty($this->chaleco_talle);
    }

    public function getMedidasSacoCompletas()
    {
        return [
            'talle' => $this->saco_talle,
            'largo' => $this->saco_largo,
            'espalda' => $this->saco_espalda,
            'manga' => $this->saco_manga,
            'pecho' => $this->saco_pecho,
            'cintura' => $this->saco_cintura,
            'cadera' => $this->saco_cadera,
            'alto_hombro' => $this->saco_alto_hombro,
            'plomo_trasero' => $this->saco_plomo_trasero,
            'plomo_delantero' => $this->saco_plomo_delantero,
            'sisa' => $this->saco_sisa,
            'puno' => $this->saco_puno,
        ];
    }

    public function getMedidasPantalonCompletas()
    {
        return [
            'largo' => $this->pantalon_largo,
            'cintura' => $this->pantalon_cintura,
            'cadera' => $this->pantalon_cadera,
            'entre_pierna' => $this->pantalon_entre_pierna,
            'muslo' => $this->pantalon_muslo,
            'rodilla' => $this->pantalon_rodilla,
            'bajo' => $this->pantalon_bajo,
        ];
    }

    public function getMedidasChalecoCompletas()
    {
        return [
            'talle' => $this->chaleco_talle,
            'pecho' => $this->chaleco_pecho,
            'cintura' => $this->chaleco_cintura,
            'escote' => $this->chaleco_escote,
            'largo' => $this->chaleco_largo,
            'largo_trasero' => $this->chaleco_largo_trasero,
            'cuello' => $this->chaleco_cuello,
        ];
    }
}
