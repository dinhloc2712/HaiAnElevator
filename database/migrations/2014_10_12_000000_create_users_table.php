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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone')->nullable()->unique();
            $table->string('avatar')->nullable();
            $table->foreignId('role_id')->nullable();

            // Status
            $table->boolean('is_active')->default(true);

            // Location (stored as strings)
            $table->string('province_id')->nullable();
            $table->string('ward_id')->nullable();
            $table->string('street_address')->nullable();

            // Employee / HR fields
            $table->string('code')->nullable()->unique();
            $table->string('position')->nullable();
            $table->decimal('salary_base', 15, 2)->default(0);
            $table->date('start_date')->nullable();
            $table->foreignId('department_id')->nullable();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();

            // Company Info (for B2B/Client users)
            $table->string('company_name')->nullable();
            $table->string('tax_code')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('bank_name')->nullable();

            // Viettel MySign CA
            $table->string('mysign_client_id')->nullable();
            $table->string('mysign_client_secret')->nullable();
            $table->string('mysign_profile_id')->nullable();
            $table->string('mysign_user_id')->nullable();
            $table->string('mysign_credential_id')->nullable();
            $table->string('mysign_signature_image')->nullable();

            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
