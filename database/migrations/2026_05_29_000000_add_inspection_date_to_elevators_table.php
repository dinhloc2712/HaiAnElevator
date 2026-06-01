<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('elevators', function (Blueprint $table) {
            $table->date('inspection_date')->nullable()->after('maintenance_end_date'); // Hạn ngày kiểm định
        });
    }

    public function down(): void
    {
        Schema::table('elevators', function (Blueprint $table) {
            $table->dropColumn('inspection_date');
        });
    }
};
