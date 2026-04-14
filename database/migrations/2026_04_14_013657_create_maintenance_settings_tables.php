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
        Schema::create('maintenance_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('maintenance_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('maintenance_categories')->onDelete('cascade');
            $table->string('name');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('maintenance_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_statuses');
        Schema::dropIfExists('maintenance_items');
        Schema::dropIfExists('maintenance_categories');
    }
};
