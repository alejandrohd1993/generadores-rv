<?php

namespace App\Models;

use App\Services\NotificacionMantenimientoService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Usage extends Model
{
    protected $fillable = [
        'fecha',
        'generator_id',
        'tipo',
        'reference_id',
        'horometro_inicio',
        'horometro_fin',
        'horas_trabajadas',
        'combustible',
        'otros_gastos',
        'soportes',
    ];

    // protected static function booted()
    // {
    //     static::created(function ($usage) {
    //         // Verificar notificaciones de mantenimiento después de crear un nuevo uso
    //         $generator = $usage->generator;
    //         app(NotificacionMantenimientoService::class)->verificarNotificaciones($generator);
    //     });
    // }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(Generator::class);
    }

    public function getReferenceModelAttribute()
    {
        return match ($this->tipo) {
            'servicio' => \App\Models\Service::find($this->reference_id),
            'mantenimiento' => \App\Models\Maintenance::find($this->reference_id),
            default => null,
        };
    }
    
}
