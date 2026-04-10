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
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('code')->after('id')->nullable()->comment('Mã phiếu');
            $table->string('customer_name')->after('code')->nullable()->comment('Người nộp hoặc nhận');
            $table->enum('payment_method', ['transfer', 'cash'])->default('transfer')->after('amount')->comment('Hình thức thanh toán');
            $table->enum('status', ['approved', 'rejected', 'pending'])->default('approved')->after('payment_method')->comment('Trạng thái');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['code', 'customer_name', 'payment_method', 'status']);
        });
    }
};
