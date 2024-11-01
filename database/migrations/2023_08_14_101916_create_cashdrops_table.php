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
        Schema::create('cashdrops', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->bigInteger('lower_pool_limit');
            $table->bigInteger('upper_pool_limit');
            $table->decimal('percentage_stake', $precision = 9, $scale = 2)->comment('the percentage taken into the pool');
            $table->string('icon');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cashdrops');
    }
};
