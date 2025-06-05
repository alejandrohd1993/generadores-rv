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
        Schema::create('supllies', function (Blueprint $table) {
            $table->id();

            $table->enum('tipo', ['aceite', 'filtro',]);
            $table->string('nombre');
            $table->decimal('horas', 12, 0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supllies');
    }
};
