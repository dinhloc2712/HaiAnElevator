<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_reset_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reset_by')->constrained('users')->onDelete('cascade');
            // JSON snapshot của KPI tất cả nhân viên tại thời điểm reset
            $table->json('snapshot')->nullable();
            // Mốc thời gian: chỉ tính proposals created_at > reset_at
            $table->timestamp('reset_at')->useCurrent();
            $table->text('note')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_reset_logs');
    }
};
