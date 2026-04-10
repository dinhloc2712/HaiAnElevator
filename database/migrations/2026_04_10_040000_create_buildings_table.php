<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buildings', function (Blueprint $table) {
            $table->id();
            $table->string('name');                        // Tòa nhà
            $table->string('customer_name')->nullable();   // Khách hàng
            $table->string('address')->nullable();         // Địa chỉ
            $table->string('contact_name')->nullable();    // Người liên hệ
            $table->string('contact_phone')->nullable();   // SĐT liên hệ
            $table->integer('elevator_count')->default(0); // Số lượng thang
            $table->text('notes')->nullable();             // Ghi chú
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buildings');
    }
};
