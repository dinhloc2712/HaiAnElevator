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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['income', 'expense'])->comment('Loại: Thu hoặc Chi');
            $table->decimal('amount', 15, 2)->comment('Số tiền');
            $table->text('description')->nullable()->comment('Lý do, mô tả');
            $table->string('reference_id')->nullable()->comment('Mã tham chiếu liên quan');
            $table->string('reference_type')->nullable()->comment('Loại tham chiếu (Ví dụ: inspection)');
            $table->unsignedBigInteger('user_id')->nullable()->comment('Người tạo phiếu');
            $table->dateTime('transaction_date')->comment('Thời gian giao dịch');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
