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
        Schema::create('usages', function (Blueprint $table) {
            $table->id();

            $table->date('fecha');
            $table->foreignId('generator_id')->constrained('generators')->onDelete('cascade');
            $table->enum('tipo', ['servicio', 'mantenimiento']);
            $table->unsignedBigInteger('reference_id');
            $table->time('horometro_inicio')->nullable();
            $table->time('horometro_fin')->nullable();
            $table->time('horas_trabajadas')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usages');
    }
};
