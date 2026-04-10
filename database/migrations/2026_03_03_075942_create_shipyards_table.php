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
        Schema::create('shipyards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('owner_name');
            $table->string('owner_id_card')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('province_id')->nullable();
            $table->string('ward_id')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('license_number')->nullable();
            $table->json('files')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipyards');
    }
};
