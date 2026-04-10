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
            if (!Schema::hasColumn('users', 'mysign_client_id')) {
                $table->string('mysign_client_id')->nullable()->after('matbao_signature_image');
            }
            if (!Schema::hasColumn('users', 'mysign_client_secret')) {
                $table->string('mysign_client_secret')->nullable()->after('mysign_client_id');
            }
            if (!Schema::hasColumn('users', 'mysign_profile_id')) {
                $table->string('mysign_profile_id')->nullable()->after('mysign_client_secret');
            }
            if (!Schema::hasColumn('users', 'mysign_user_id')) {
                $table->string('mysign_user_id')->nullable()->after('mysign_profile_id');
            }
            if (!Schema::hasColumn('users', 'mysign_credential_id')) {
                $table->string('mysign_credential_id')->nullable()->after('mysign_user_id');
            }
            if (!Schema::hasColumn('users', 'mysign_signature_image')) {
                $table->string('mysign_signature_image')->nullable()->after('mysign_credential_id');
            }
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
                'mysign_credential_id',
                'mysign_signature_image',
            ]);
        });
    }
};
