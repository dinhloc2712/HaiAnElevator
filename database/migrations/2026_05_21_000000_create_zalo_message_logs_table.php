<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('zalo_message_logs', function (Blueprint $table) {
            $table->id();
            $table->string('phone')->index();
            $table->string('channel')->nullable()->default('zns')->index();
            $table->string('template_id')->nullable();
            $table->string('tracking_id')->nullable()->index();
            $table->string('msg_id')->nullable()->index();
            $table->string('status')->default('pending')->index();
            $table->integer('error_code')->nullable()->index();
            $table->text('error_message')->nullable();
            $table->json('response')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('zalo_message_logs');
    }
};
