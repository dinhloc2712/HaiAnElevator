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
        Schema::table('proposals', function (Blueprint $table) {
            $table->decimal('pre_vat_amount', 15, 2)->nullable()->after('content');
            $table->integer('vat')->nullable()->after('pre_vat_amount')->comment('VAT percentage, e.g., 8 for 8%');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->dropColumn(['pre_vat_amount', 'vat']);
        });
    }
};
