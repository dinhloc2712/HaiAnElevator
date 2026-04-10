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
        Schema::table('ships', function (Blueprint $table) {
            $table->double('gross_tonnage')->nullable()->comment('Tổng dung tích');
            $table->double('deadweight')->nullable()->comment('Trọng tải (tấn)');
            $table->double('length_design')->nullable()->comment('Chiều dài thiết kế Ltk');
            $table->double('width_design')->nullable()->comment('Chiều rộng thiết kế Btk');
            $table->double('length_max')->nullable()->comment('Chiều dài lớn nhất Lmax');
            $table->double('width_max')->nullable()->comment('Chiều rộng lớn nhất Bmax');
            $table->double('depth_max')->nullable()->comment('Chiều cao mạn Dmax');
            $table->double('draft')->nullable()->comment('Mớn nước d');
            $table->string('hull_material')->nullable()->comment('Vật liệu vỏ');
            $table->integer('build_year')->nullable()->comment('Năm đóng');
            $table->string('build_place')->nullable()->comment('Nơi đóng');
            $table->string('engine_mark')->nullable()->comment('Ký hiệu máy');
            $table->string('engine_number')->nullable()->comment('Số máy');
            $table->json('engine_hp')->nullable()->comment('Công suất máy (HP)');
            $table->json('engine_kw')->nullable()->comment('Công suất máy (KW)');
            $table->string('technical_safety_number')->nullable()->comment('Số ATKT');
            $table->date('technical_safety_date')->nullable()->comment('Ngày cấp ATKT');
            $table->string('record_number')->nullable()->comment('Số Biên bản');
            $table->date('record_date')->nullable()->comment('Ngày cấp biên bản');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ships', function (Blueprint $table) {
            $table->dropColumn([
                'gross_tonnage', 'deadweight', 'length_design', 'width_design',
                'length_max', 'width_max', 'depth_max', 'draft', 'hull_material',
                'build_year', 'build_place', 'engine_mark', 'engine_number',
                'engine_hp', 'engine_kw', 'technical_safety_number', 'technical_safety_date',
                'record_number', 'record_date'
            ]);
        });
    }
};
