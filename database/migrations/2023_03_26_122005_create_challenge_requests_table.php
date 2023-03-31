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
        Schema::create('challenge_requests', function (Blueprint $table) {
            $table->id();
            $table->string('challenge_request_id');
            $table->foreignId('user_id');
            $table->string('username');
            $table->decimal('amount', 10, 2);
            $table->foreignId('category_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenge_requests');
    }
};
