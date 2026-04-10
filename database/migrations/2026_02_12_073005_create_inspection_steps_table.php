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
        Schema::create('inspection_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_process_id')->constrained()->onDelete('cascade');
            $table->string('title'); // e.g., "1. Hồ sơ Pháp lý"
            $table->integer('order_index')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspection_steps');
    }
};
