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
        Schema::create('inspection_step_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_step_id')->constrained()->onDelete('cascade');
            $table->string('content'); // e.g., "Giấy chứng nhận đăng ký"
            $table->boolean('is_required')->default(true);
            $table->string('field_type')->default('checkbox'); // checkbox, text, photo, etc.
            $table->integer('order_index')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspection_step_items');
    }
};
