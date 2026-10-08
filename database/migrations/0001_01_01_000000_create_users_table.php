<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('phone', 15)->unique();
            $table->string('email')->nullable()->unique();
            $table->string('password')->nullable();
            $table->string('pincode', 10)->nullable()->index();
            $table->string('city', 100)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('profile_photo')->nullable();
            
            // Roles: 1=Admin, 2=Manager, 3=User
            $table->unsignedTinyInteger('role')->default(3)->index();
            
            // Status: 1=Active, 2=Blocked, 3=Suspended
            $table->unsignedTinyInteger('status')->default(1)->index();
            $table->text('blocked_reason')->nullable();
            
            $table->timestamp('phone_verified_at')->nullable();
            $table->timestamp('location_updated_at')->nullable();
            $table->timestamp('last_login_at')->nullable()->index();
            
            $table->timestamps();
            $table->softDeletes();
            
            // 0 = Self registered, otherwise ID of the user (admin/manager) who created this user
            $table->unsignedBigInteger('created_by')->default(0)->index();
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('sessions');
    }
};
