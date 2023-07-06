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
        Schema::table('challenge_requests', function (Blueprint $table) {
            $table->string('challenge_mode')->default('CHALLENGE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('challenge_requests', function (Blueprint $table) {
            $table->dropColumn(['challenge_mode']);
        });
    }
};
