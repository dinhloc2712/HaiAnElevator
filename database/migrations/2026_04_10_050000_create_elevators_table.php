<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('elevators', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();              // MÃ THANG MÁY (e.g., HA-91226-EM)
            $table->foreignId('building_id')->nullable()->constrained()->onDelete('set null'); // Liên kết tòa nhà
            $table->string('customer_name')->nullable();   // Tên khách hàng (độc lập hoặc từ tòa nhà)
            $table->string('customer_phone')->nullable();  // SĐT khách hàng
            $table->string('province')->nullable();        // Tỉnh
            $table->string('district')->nullable();        // Huyện
            $table->integer('cycle_days')->default(30);    // Chu kỳ bảo trì (ngày)
            $table->string('status')->default('active');   // Trạng thái (Hoạt động/Vô hiệu)
            $table->date('maintenance_deadline')->nullable(); // Hạn bảo trì
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('elevators');
    }
};
