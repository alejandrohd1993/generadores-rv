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
        Schema::create('maintenances', function (Blueprint $table) {
            $table->id();

            $table->string('nombre');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('generator_id')->constrained('generators')->onDelete('cascade');
            $table->enum('tipo_mantenimiento', ['aceite', 'filtro','otro']);
            $table->enum('categoria_mantenimiento', ['preventivo', 'correctivo', 'predictivo', 'otro']);
            $table->date('fecha');
            $table->foreignId('provider_id')->constrained('providers')->onDelete('cascade');
            $table->string('descripcion')->nullable();
            $table->enum('estado', ['Pendiente', 'En proceso', 'Completado', 'Cancelado']);
            $table->decimal('costo_mantenimiento', 12, 2)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenances');
    }
};
