<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('contests');
        Schema::dropIfExists('contest_prize_pools');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('contests', function (Blueprint $table) {
            $table->id();
            $table->dateTime('start_date')->default(Carbon::now());
            $table->dateTime('end_date')->default(Carbon::now());
            $table->string('name');
            $table->string('description');
            $table->string('display_name');
            $table->string('contest_type');
            $table->string('entry_mode');
            $table->timestamps();
        });

        Schema::create('contest_prize_pools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contest_id');
            $table->smallInteger('rank_from');
            $table->smallInteger('rank_to');
            $table->string('prize');
            $table->string('prize_type');
            $table->timestamps();
        });
    }
};
