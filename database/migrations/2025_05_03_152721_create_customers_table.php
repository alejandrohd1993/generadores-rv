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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            
            $table->string('nit')->unique();
            $table->string('nombre');
            $table->string('email')->unique();
            $table->string('telefono');
            $table->string('direccion');
            $table->enum('tipo_persona', ['Jurídica', 'Natural']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
