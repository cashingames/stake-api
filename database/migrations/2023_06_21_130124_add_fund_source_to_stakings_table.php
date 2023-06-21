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
        Schema::table('stakings', function (Blueprint $table) {
            $table->enum('fund_source',['BONUS','CREDIT','WINNINGS'])->default('CREDIT');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stakings', function (Blueprint $table) {
            $table->dropColumn(['fund_source']);
        });
    }
};
