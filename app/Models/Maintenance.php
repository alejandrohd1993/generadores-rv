<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Maintenance extends Model
{
    protected $fillable = [
        'nombre',
        'user_id',
        'generator_id',
        'tipo_mantenimiento',
        'suplly_id',
        'categoria_mantenimiento',
        'fecha',
        'provider_id',
        'descripcion',
        'estado',
        'costo_mantenimiento'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(Generator::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function usages()
    {
        return Usage::where('tipo', 'mantenimiento')->where('reference_id', $this->id);
    }

    public function suplly(): BelongsTo
    {
        return $this->belongsTo(Suplly::class);
    }
}
