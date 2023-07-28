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
        Schema::dropIfExists('user_bonuses');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('user_bonuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('bonus_id');
            $table->boolean('is_on')->default(false);
            $table->decimal('amount_credited', $precision = 9, $scale = 2)->default(0);
            $table->decimal('amount_remaining_after_staking', $precision = 9, $scale = 2)->default(0);
            $table->decimal('total_amount_won', $precision = 9, $scale = 2)->default(0);
            $table->decimal('amount_remaining_after_withdrawal', $precision = 9, $scale = 2)->default(0);
            $table->timestamps();
        });
    }
};
