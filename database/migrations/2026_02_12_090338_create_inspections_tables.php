<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('inspections', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // e.g., DK-2023-001
            $table->unsignedBigInteger('ship_id');
            $table->unsignedBigInteger('inspection_process_id');
            $table->unsignedBigInteger('inspector_id')->nullable(); // User ID of the inspector
            
            $table->date('inspection_date');
            $table->string('status')->default('draft'); // draft, in_progress, completed, rejected
            $table->string('result')->nullable(); // pass, fail
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->foreign('ship_id')->references('id')->on('ships')->onDelete('cascade');
            $table->foreign('inspection_process_id')->references('id')->on('inspection_processes')->onDelete('cascade');
            $table->foreign('inspector_id')->references('id')->on('users')->onDelete('set null');
        });

        Schema::create('inspection_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inspection_id');
            $table->unsignedBigInteger('inspection_step_item_id');
            
            $table->string('status')->nullable(); // pass, fail, skipped
            $table->text('note')->nullable();
            $table->json('evidence_files')->nullable(); // Path to uploaded files

            $table->timestamps();

            $table->foreign('inspection_id')->references('id')->on('inspections')->onDelete('cascade');
            $table->foreign('inspection_step_item_id')->references('id')->on('inspection_step_items')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('inspection_details');
        Schema::dropIfExists('inspections');
    }
};
