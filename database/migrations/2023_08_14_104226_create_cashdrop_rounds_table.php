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
        Schema::create('cashdrop_rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cashdrop_id');
            $table->bigInteger('pooled_amount');
            $table->dateTime('dropped_at');
            $table->decimal('percentage_stake', $precision = 9, $scale = 2)->comment('cashdrop precentage stake * stake amount');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cashdrop_rounds');
    }
};
