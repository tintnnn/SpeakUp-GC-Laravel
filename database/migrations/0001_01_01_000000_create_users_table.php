<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->char('firebase_uid', 28)->nullable()->unique();
            $table->string('email')->unique();
            $table->string('display_name')->nullable();
            $table->string('alias', 100)->nullable()->unique();
            $table->string('photo_url', 2048)->nullable();
            $table->string('password');
            $table->enum('provider', ['email', 'google'])->default('email');
            $table->boolean('is_admin')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_deleted')->default(false);
            $table->boolean('is_suspended')->default(false);
            $table->boolean('is_registered')->default(true);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('role', 40)->nullable();
            $table->string('notification_preference', 16)->nullable();
            $table->boolean('terms_accepted')->default(false);
            $table->boolean('privacy_accepted')->default(false);
            $table->boolean('profile_setup_complete')->default(false);
            $table->timestamp('profile_setup_completed_at')->nullable();
            $table->timestamp('registered_at')->nullable();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('deleted_at')->nullable();
            $table->json('extra')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->index('display_name');
            $table->index('created_at');
            $table->index('is_admin');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
