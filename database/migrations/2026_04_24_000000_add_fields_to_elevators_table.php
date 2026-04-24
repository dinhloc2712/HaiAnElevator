<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('elevators', function (Blueprint $table) {
            $table->integer('floors')->nullable()->after('capacity')->comment('Số tầng');
            $table->text('note')->nullable()->after('status')->comment('Ghi chú');
            $table->text('map')->nullable()->after('district')->comment('Link bản đồ hoặc tọa độ');
            $table->date('maintenance_end_date')->nullable()->after('maintenance_deadline')->comment('Ngày kết thúc thời hạn bảo trì');
        });
    }

    public function down(): void
    {
        Schema::table('elevators', function (Blueprint $table) {
            $table->dropColumn(['floors', 'note', 'map', 'maintenance_end_date']);
        });
    }
};
