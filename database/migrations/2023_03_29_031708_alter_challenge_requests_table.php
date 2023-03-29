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
        Schema::table('challenge_requests', function (Blueprint $table) {
            $table->bigInteger('user_id')->constrained('users')->cascadeOnDelete()->change();
            $table->bigInteger('category_id')->constrained('categories')->cascadeOnDelete()->change();
            $table->decimal('amount_won', 10, 2)->default(0);
            $table->tinyInteger('score')->default(0);
            $table->string('status')->default('MATCHING');
            $table->string('session_token')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('challenge_requests', function (Blueprint $table) {
            $table->dropColumn('amount_won');
        });
        Schema::table('challenge_requests', function (Blueprint $table) {
            $table->dropColumn('score');
        });
        Schema::table('challenge_requests', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        Schema::table('challenge_requests', function (Blueprint $table) {
            $table->dropColumn('session_token');
        });
        Schema::table('challenge_requests', function (Blueprint $table) {
            $table->dropColumn('started_at');
        });
        Schema::table('challenge_requests', function (Blueprint $table) {
            $table->dropColumn('ended_at');
        });

        Schema::table('challenge_requests', function (Blueprint $table) {
            $table->foreignId('user_id')->change();
            $table->foreignId('category_id')->change();
        });
    }
};
