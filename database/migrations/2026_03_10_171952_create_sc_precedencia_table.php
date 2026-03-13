<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sc_precedencia', function (Blueprint $table) {
            // La SC que requiere un prerequisito
            $table->foreignId('sc_id')
                ->constrained('situaciones_competencia')
                ->cascadeOnDelete();
            // La SC que debe estar conquistada previamente
            $table->foreignId('sc_requisito_id')
                ->constrained('situaciones_competencia')
                ->cascadeOnDelete();

            $table->primary(['sc_id', 'sc_requisito_id']);
        });

        // Evitar que una SC sea requisito de sí misma
        // DB::statement('ALTER TABLE sc_precedencia ADD CONSTRAINT chk_sc_precedencia CHECK (sc_id != sc_requisito_id);');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sc_precedencia');
    }
};
