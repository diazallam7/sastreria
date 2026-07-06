<?php

namespace App\Models;

use App\Enums\TipoMedida;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClienteMedida extends Model
{
    protected $table = 'cliente_medidas';

    protected $fillable = [
        'cliente_id',
        'user_id',
        'tipo',
        'medidas',
        'observaciones',
        'vigente',
    ];

    protected $casts = [
        'tipo'    => TipoMedida::class,
        'medidas' => 'array',
        'vigente' => 'boolean',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    /** Usuario que registró las medidas. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
