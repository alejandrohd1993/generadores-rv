<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'nit',
        'nombre',
        'email',
        'telefono',
        'direccion',
        'tipo_persona',
    ];

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }
}
