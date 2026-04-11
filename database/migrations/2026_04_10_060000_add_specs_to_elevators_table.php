<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('elevators', function (Blueprint $table) {
            $table->string('manufacturer')->nullable()->after('district'); // Hãng sản xuất
            $table->string('model')->nullable()->after('manufacturer');    // MODEL
            $table->string('type')->nullable()->after('model');             // Loại thang máy
            $table->string('capacity')->nullable()->after('type');          // Tải trọng
        });
    }

    public function down(): void
    {
        Schema::table('elevators', function (Blueprint $table) {
            $table->dropColumn(['manufacturer', 'model', 'type', 'capacity']);
        });
    }
};
