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
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->string('device_id', 100)->nullable()->index()->after('abilities');
            $table->string('device_name', 100)->nullable()->after('device_id');
            $table->unsignedTinyInteger('device_type')->nullable()->after('device_name');
            $table->text('fcm_token')->nullable()->after('device_type');
            $table->decimal('latitude', 10, 7)->nullable()->after('fcm_token');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->string('ip_address', 45)->nullable()->after('longitude');
            $table->text('user_agent')->nullable()->after('ip_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->dropColumn([
                'device_id',
                'device_name',
                'device_type',
                'fcm_token',
                'latitude',
                'longitude',
                'ip_address',
                'user_agent'
            ]);
        });
    }
};
