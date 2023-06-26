<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('achievements_badges');
        Schema::dropIfExists('user_achievement_badges');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};