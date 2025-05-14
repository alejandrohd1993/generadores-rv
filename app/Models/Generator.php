<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Generator extends Model
{
    protected $fillable = [
        'codigo',
        'modelo',
        'marca',
        'horometro',
        'estado',
    ];

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class)
            ->using(GeneratorService::class)
            ->withPivot('horometro_inicio', 'horometro_fin', 'horas_trabajadas')
            ->withTimestamps();
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class);
    }
}
