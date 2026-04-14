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
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('elevator_id')->constrained()->onDelete('cascade');
            $table->string('reporter_name')->nullable();
            $table->string('reporter_phone')->nullable();
            $table->text('description')->nullable();
            
            // Priority: emergency (khẩn cấp), high (cao), medium (trung bình), low (thấp)
            $table->string('priority')->default('medium');
            
            // Status: new (mới báo), processing (đang xử lý), resolved (hoàn thành), canceled (đã hủy)
            $table->string('status')->default('new');
            
            $table->timestamp('reported_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
