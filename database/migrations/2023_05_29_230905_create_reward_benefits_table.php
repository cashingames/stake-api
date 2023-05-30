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
        Schema::create('reward_benefits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reward_id')->constrained()->onDelete('cascade');
            $table->integer('total_hours');
            $table->string('reward_type');
            $table->integer('reward_count');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reward_benefits');
    }
};
