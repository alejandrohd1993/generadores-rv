<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Suplly extends Model
{
    protected $fillable = [
        'tipo',
        'nombre',
        'horas',
    ];

    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class);
    }
}
