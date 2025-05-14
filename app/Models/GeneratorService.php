<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Relations\Pivot;

class GeneratorService extends Pivot
{
    protected $table = 'generator_service';

    protected $fillable = [
        'generator_id',
        'service_id',
        'horometro_inicio',
        'horometro_fin',
        'horas_trabajadas',
    ];
}
