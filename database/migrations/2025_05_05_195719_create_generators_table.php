<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('generators', function (Blueprint $table) {
            $table->id();

            $table->string('codigo')->unique();
            $table->string('modelo')->nullable();
            $table->string('marca')->nullable();
            $table->string('horometro')->nullable();
            $table->enum('estado', ['Disponible', 'En uso', 'En mantenimiento', 'Fuera de servicio']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generators');
    }
};
