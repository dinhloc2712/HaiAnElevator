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
        Schema::table('users', function (Blueprint $table) {
            $table->string('province_id')->nullable()->change();
            $table->string('ward_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revert back to unsigned big integers if needed
            $table->unsignedBigInteger('province_id')->nullable()->change();
            $table->unsignedBigInteger('ward_id')->nullable()->change();
        });
    }
};
