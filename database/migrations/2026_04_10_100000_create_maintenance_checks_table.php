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
        Schema::create('maintenance_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('elevator_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Người lập phiếu
            $table->date('check_date');
            
            // JSON column to store the 40 checklist items results
            // Format: { "item_1": "Δ", "item_2": "√", ... }
            $table->json('results')->nullable();
            
            $table->text('evaluation')->nullable(); // Đánh giá, nhận xét
            $table->text('staff_names')->nullable(); // Danh sách cán bộ kỹ thuật (1-6)
            
            $table->integer('performer_count')->default(1); // Số người thực hiện
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_checks');
    }
};
