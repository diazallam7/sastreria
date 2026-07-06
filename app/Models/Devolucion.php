<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Devolucion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'devoluciones';

    protected $fillable = [
        'alquiler_id',
        'user_id',
        'fecha_devolucion',
        'retraso',
        'dias_retraso',
        'multa_calculada',   // corresponde por retraso (calculada por el sistema)
        'multa_aplicada',    // realmente cobrada (puede diferir por ajuste manual)
        'garantia_original',
        'monto_devuelto',    // garantia_original - multa_aplicada (nunca < 0)
        'motivo_ajuste',
        'observaciones',
    ];

    protected $casts = [
        'fecha_devolucion'  => 'datetime',
        'retraso'           => 'boolean',
        'dias_retraso'      => 'integer',
        'multa_calculada'   => 'integer',
        'multa_aplicada'    => 'integer',
        'garantia_original' => 'integer',
        'monto_devuelto'    => 'integer',
    ];

    public function alquiler(): BelongsTo
    {
        return $this->belongsTo(Alquiler::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
