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
        Schema::create('ecosistemas_laborales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modulo_id')
                ->constrained('modulos')
                ->cascadeOnDelete();
            $table->string('nombre');
            $table->string('codigo', 20)->unique();   // Ej: "0614-TBM"
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecosistemas_laborales');
    }
};
