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
        Schema::table('users', function (Blueprint $table) {
            // en desarrollo local el campo es NULL
            $table->string('verifier_sub')->nullable()->unique()->after('email');
            $table->index('verifier_sub');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['verifier_sub']);
            $table->dropColumn('verifier_sub');
        });
    }
};
