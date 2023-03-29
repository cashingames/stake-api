<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('trivia_challenge_questions', function (Blueprint $table) {
            $table->bigIncrements('id')->primary();
            $table->string('session_token')->index();
            $table->foreignId('challenge_request_id')->constrained('challenge_requests')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('username');
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->string('category_name');
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->string('question_label');
            $table->foreignId('option_id')->nullable()->constrained('options')->cascadeOnDelete();
            $table->string('option_label')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trivia_challenge_questions');
    }
};
