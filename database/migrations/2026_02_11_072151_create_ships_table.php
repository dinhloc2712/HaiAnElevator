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
        Schema::create('ships', function (Blueprint $table) {
            $table->id();
            // Registration Info
            $table->string('registration_number')->unique()->comment('Số đăng ký');
            $table->date('registration_date')->nullable()->comment('Ngày đăng ký');
            $table->string('status')->default('active')->comment('Tình trạng tàu'); // active, expired, suspended
            
            // Technical & Operation Info
            $table->string('name')->nullable()->comment('Tên tàu');
            $table->string('hull_number')->nullable()->comment('Số hiệu');
            $table->string('usage')->nullable()->comment('Công dụng tàu cá');
            $table->string('operation_area')->nullable()->comment('Vùng hoạt động');
            $table->integer('crew_size')->nullable()->comment('Số thuyền viên');
            $table->string('main_occupation')->nullable()->comment('Nghề chính');
            $table->string('secondary_occupation')->nullable()->comment('Nghề phụ');

            // Owner Info
            $table->string('owner_name')->comment('Chủ phương tiện');
            $table->string('owner_id_card')->nullable()->index()->comment('Số CMND/CCCD');
            $table->string('owner_phone')->nullable()->comment('Điện thoại');
            
            // Address
            $table->string('province_id')->nullable()->comment('Mã Tỉnh/Thành phố');
            $table->string('ward_id')->nullable()->comment('Mã Xã/Phường');
            $table->string('address')->nullable()->comment('Địa chỉ chi tiết'); // Street address

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ships');
    }
};
