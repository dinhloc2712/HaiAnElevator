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
        Schema::table('users', function (Blueprint $table) {
            $table->string('mysign_client_id')->nullable();
            $table->string('mysign_client_secret')->nullable();
            $table->string('mysign_profile_id')->nullable();
            $table->string('mysign_user_id')->nullable();
            $table->string('mysign_credential_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'mysign_client_id',
                'mysign_client_secret',
                'mysign_profile_id',
                'mysign_user_id',
                'mysign_credential_id'
            ]);
        });
    }
};
