<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Service extends Model
{
    protected $fillable = [
        'nombre',
        'customer_id',
        'user_id',
        'lugar',
        'date_start',
        'date_final',
        'estado',
        'presupuesto_otros_gastos',
        'presupuesto_viaticos',
        'presupuesto_total',
        'valor_servicio',
        'notas',
        'facturado',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function generators(): BelongsToMany
    {
        return $this->belongsToMany(Generator::class)
            ->using(GeneratorService::class)
            ->withPivot('horometro_inicio', 'horometro_fin', 'horas_trabajadas')
            ->withTimestamps();
    }

    public function usages()
    {
        return Usage::where('tipo', 'servicio')->where('reference_id', $this->id);
    }
}
