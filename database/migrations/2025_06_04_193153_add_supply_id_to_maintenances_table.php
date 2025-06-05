<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('maintenances', function (Blueprint $table) {
            // Verifica primero si la columna no existe (para evitar errores)
            if (!Schema::hasColumn('maintenances', 'suplly_id')) {
                $table->foreignId('suplly_id')
                      ->nullable() // O ->constrained() si es obligatorio
                      ->constrained('supllies')
                      ->onDelete('cascade');
            }
        });
    }

    public function down()
    {
        Schema::table('maintenances', function (Blueprint $table) {
            // Eliminar la clave foránea primero
            $table->dropForeign(['suplly_id']);
            
            // Luego eliminar la columna
            $table->dropColumn('suplly_id');
        });
    }
};