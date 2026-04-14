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
        Schema::table('maintenance_checks', function (Blueprint $table) {
            $table->json('fault_category')->nullable()->after('task_type')->comment('Mảng chứa danh sách các lỗi: ["Cơ khí", "Hệ điều khiển"]...');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_checks', function (Blueprint $table) {
            $table->dropColumn('fault_category');
        });
    }
};
