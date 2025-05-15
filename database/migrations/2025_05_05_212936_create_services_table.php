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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            
            $table->string('nombre');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('lugar');
            $table->date('date_start');
            $table->date('date_final');
            $table->enum('estado', ['Pendiente', 'En proceso', 'Completado', 'Cancelado']);
            $table->decimal('presupuesto_combustible', 12, 2)->nullable();
            $table->decimal('presupuesto_viaticos', 12, 2)->nullable();
            $table->decimal('presupuesto_total', 12, 2)->nullable();
            $table->string('notas')->nullable();
            $table->enum('facturado', ['Si', 'No']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
