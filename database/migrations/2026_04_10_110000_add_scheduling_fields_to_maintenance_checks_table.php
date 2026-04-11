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
            $table->string('status')->default('pending')->after('user_id'); // pending, in_progress, completed
            $table->string('task_type')->default('periodic')->after('status'); // periodic, repair
            $table->date('scheduled_date')->nullable()->after('task_type');
            
            // Allow check_date to be nullable since a pending task hasn't been checked yet
            $table->date('check_date')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_checks', function (Blueprint $table) {
            $table->dropColumn(['status', 'task_type', 'scheduled_date']);
            $table->date('check_date')->nullable(false)->change();
        });
    }
};
